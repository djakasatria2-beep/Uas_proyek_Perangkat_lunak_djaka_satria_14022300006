<?php
// ============================================================
//  buyer_panel/returns-new.php
//  Form pengajuan return pesanan.
// ============================================================

define('REQUIRED_ROLE', 'buyer');
require_once __DIR__ . '/../assets/verifyRoleRedirect.php';
require_once __DIR__ . '/partials/config.php';

$idBuyer   = (int) ($currentBuyer['id_buyer'] ?? 0);
$pageTitle = 'Ajukan Return';

// ------------------------------------------------------------
// Ambil daftar pesanan yang bisa di-return (shipped / done)
// milik buyer ini, belum punya return aktif (opsional bisa dinonaktifkan)
// ------------------------------------------------------------
$eligibleOrders = [];
$resO = $conn->prepare("
    SELECT o.id_order, o.no_order, o.jenis_benang, o.ukuran_benang,
           o.kode_warna, o.nama_warna, o.qty, o.tanggal, o.status
    FROM orders o
    WHERE o.id_buyer = ?
      AND o.status IN ('shipped','done')
    ORDER BY o.tanggal DESC
");
$resO->bind_param('i', $idBuyer);
$resO->execute();
$resOResult = $resO->get_result();
while ($r = $resOResult->fetch_assoc()) $eligibleOrders[] = $r;
$resO->close();

// Jika masuk dari orders-detail.php?id_order=X, pre-select pesanan tersebut
$preSelectOrder = (int) ($_GET['id_order'] ?? 0);

// Validasi preselect milik buyer ini
if ($preSelectOrder) {
    $valid = false;
    foreach ($eligibleOrders as $eo) {
        if ($eo['id_order'] === $preSelectOrder) { $valid = true; break; }
    }
    if (!$valid) $preSelectOrder = 0;
}

// ------------------------------------------------------------
// Auto-generate no_return: RET-YYYY-XXXX
// ------------------------------------------------------------
function generateNoReturn(mysqli $conn): string {
    $year = date('Y');
    $res  = $conn->query("
        SELECT no_return FROM order_returns
        WHERE no_return LIKE 'RET-{$year}-%'
        ORDER BY id_return DESC LIMIT 1
    ");
    $last = $res && $res->num_rows ? $res->fetch_assoc()['no_return'] : null;
    $seq  = $last ? ((int) substr($last, -4)) + 1 : 1;
    return sprintf('RET-%s-%04d', $year, $seq);
}

// ------------------------------------------------------------
// Handle upload foto (max 5, jpg/png/webp, max 2MB each)
// ------------------------------------------------------------
function handleFotoUpload(): array {
    $uploaded = [];
    $errors   = [];

    if (empty($_FILES['foto']['name'][0])) return ['paths' => [], 'errors' => []];

    $uploadDir = __DIR__ . '/uploads/returns/';
    if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

    $allowed = ['image/jpeg', 'image/png', 'image/webp'];
    $maxSize = 2 * 1024 * 1024; // 2 MB
    $count   = min(count($_FILES['foto']['name']), 5);

    for ($i = 0; $i < $count; $i++) {
        if ($_FILES['foto']['error'][$i] !== UPLOAD_ERR_OK) continue;
        if ($_FILES['foto']['size'][$i] > $maxSize) {
            $errors[] = 'File "' . htmlspecialchars($_FILES['foto']['name'][$i]) . '" melebihi 2 MB.';
            continue;
        }
        $mime = mime_content_type($_FILES['foto']['tmp_name'][$i]);
        if (!in_array($mime, $allowed)) {
            $errors[] = 'Format file tidak didukung: ' . htmlspecialchars($_FILES['foto']['name'][$i]);
            continue;
        }
        $ext      = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'][$mime];
        $filename = 'ret_' . time() . '_' . $i . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
        if (move_uploaded_file($_FILES['foto']['tmp_name'][$i], $uploadDir . $filename)) {
            $uploaded[] = $filename;
        }
    }

    return ['paths' => $uploaded, 'errors' => $errors];
}

// ------------------------------------------------------------
// Handle POST
// ------------------------------------------------------------
$errors      = [];
$fieldErrors = [];
$old         = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $idOrder       = (int) ($_POST['id_order'] ?? 0);
    $kategori      = trim($_POST['alasan_kategori'] ?? '');
    $alasan        = trim($_POST['alasan'] ?? '');

    $allowedKat = ['deviasi_warna','kualitas','barang_rusak','spesifikasi_salah','lainnya'];

    // Validasi
    if (!$idOrder)                          $fieldErrors['id_order']         = 'Pilih pesanan yang akan di-return.';
    if (!in_array($kategori, $allowedKat))  $fieldErrors['alasan_kategori']  = 'Pilih kategori return yang valid.';
    if ($alasan === '')                     $fieldErrors['alasan']           = 'Alasan return wajib diisi.';
    elseif (mb_strlen($alasan) < 20)       $fieldErrors['alasan']           = 'Alasan minimal 20 karakter.';

    // Validasi pesanan milik buyer & eligible
    if ($idOrder && empty($fieldErrors['id_order'])) {
        $chk = $conn->prepare("
            SELECT id_order FROM orders
            WHERE id_order = ? AND id_buyer = ? AND status IN ('shipped','done')
            LIMIT 1
        ");
        $chk->bind_param('ii', $idOrder, $idBuyer);
        $chk->execute();
        if (!$chk->get_result()->fetch_assoc()) {
            $fieldErrors['id_order'] = 'Pesanan tidak valid atau tidak memenuhi syarat return.';
        }
        $chk->close();
    }

    // Upload foto
    $fotoResult = handleFotoUpload();
    if (!empty($fotoResult['errors'])) {
        $errors = array_merge($errors, $fotoResult['errors']);
    }
    $fotoJson = !empty($fotoResult['paths']) ? json_encode($fotoResult['paths']) : null;

    // Simpan jika tidak ada error
    if (empty($fieldErrors) && empty($errors)) {
        $noReturn = generateNoReturn($conn);
        $stmt = $conn->prepare("
            INSERT INTO order_returns
                (id_order, no_return, alasan_kategori, alasan, foto, status)
            VALUES (?, ?, ?, ?, ?, 'submitted')
        ");
        $stmt->bind_param('issss', $idOrder, $noReturn, $kategori, $alasan, $fotoJson);
        if ($stmt->execute()) {
            header('Location: ' . BUYER_URL . '/returns.php?submitted=1');
            exit;
        } else {
            $errors[] = 'Gagal menyimpan pengajuan. Silakan coba lagi.';
        }
        $stmt->close();
    }

    // Repopulate
    $old = [
        'id_order'        => $idOrder,
        'alasan_kategori' => $kategori,
        'alasan'          => $alasan,
    ];
    $preSelectOrder = $idOrder;
}

// Kalau belum ada old, isi dari preselect
if (empty($old)) {
    $old = [
        'id_order'        => $preSelectOrder,
        'alasan_kategori' => '',
        'alasan'          => '',
    ];
}

// Data pesanan yang dipilih (untuk preview)
$selectedOrder = null;
if ($old['id_order']) {
    foreach ($eligibleOrders as $eo) {
        if ($eo['id_order'] === (int) $old['id_order']) {
            $selectedOrder = $eo;
            break;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle) ?> — Buyer Panel</title>
</head>
<body>

<?php include __DIR__ . '/partials/_header.php'; ?>
<?php include __DIR__ . '/partials/_sidebar.php'; ?>
<?php include __DIR__ . '/partials/overdue-banner.php'; ?>

<div class="bp-content">
    <main class="bp-retnew">

        <!-- ===== Heading ===== -->
        <div class="bp-retnew__heading">
            <a href="<?= BUYER_URL ?>/returns.php" class="bp-back">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                     stroke-linecap="round" stroke-linejoin="round" width="16" height="16">
                    <polyline points="15 18 9 12 15 6"></polyline>
                </svg>
                Kembali ke Daftar Return
            </a>
            <div class="bp-retnew__title-row">
                <div>
                    <h1>Ajukan Return Baru</h1>
                    <p>Isi form berikut untuk mengajukan pengembalian pesanan.</p>
                </div>
            </div>
        </div>

        <!-- Global errors -->
        <?php if (!empty($errors)): ?>
            <div class="bp-alert bp-alert--error">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                     stroke-linecap="round" stroke-linejoin="round" width="18" height="18">
                    <circle cx="12" cy="12" r="10"></circle>
                    <line x1="12" y1="8" x2="12" y2="12"></line>
                    <line x1="12" y1="16" x2="12.01" y2="16"></line>
                </svg>
                <div>
                    <?php foreach ($errors as $e): ?>
                        <p style="margin:0 0 4px;"><?= htmlspecialchars($e) ?></p>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>

        <!-- No eligible orders -->
        <?php if (empty($eligibleOrders)): ?>
            <div class="bp-empty-card">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"
                     stroke-linecap="round" stroke-linejoin="round" width="48" height="48">
                    <polyline points="1 4 1 10 7 10"></polyline>
                    <path d="M3.51 15a9 9 0 1 0 .49-3.51"></path>
                </svg>
                <p class="bp-empty-card__title">Tidak ada pesanan yang bisa di-return</p>
                <p class="bp-empty-card__sub">Hanya pesanan berstatus <strong>Dikirim</strong> atau <strong>Selesai</strong> yang dapat diajukan return.</p>
                <a href="<?= BUYER_URL ?>/orders.php" class="bp-btn-secondary">Lihat Pesanan Saya</a>
            </div>

        <?php else: ?>

        <form method="POST" action="" enctype="multipart/form-data" novalidate id="formReturn">

            <div class="bp-form-grid">

                <!-- ===== Kolom Kiri ===== -->
                <div class="bp-form-col">

                    <!-- Pilih Pesanan -->
                    <div class="bp-card">
                        <div class="bp-card__section-header">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                 stroke-linecap="round" stroke-linejoin="round" width="16" height="16">
                                <path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"></path>
                                <line x1="3" y1="6" x2="21" y2="6"></line>
                                <path d="M16 10a4 4 0 0 1-8 0"></path>
                            </svg>
                            <h2>Pilih Pesanan</h2>
                        </div>

                        <div class="bp-field <?= isset($fieldErrors['id_order']) ? 'is-error' : '' ?>">
                            <label for="id_order">Pesanan <span class="bp-required">*</span></label>
                            <select name="id_order" id="id_order" onchange="updateOrderPreview(this)">
                                <option value="">— Pilih pesanan —</option>
                                <?php foreach ($eligibleOrders as $eo): ?>
                                    <option value="<?= $eo['id_order'] ?>"
                                            data-no="<?= htmlspecialchars($eo['no_order']) ?>"
                                            data-jenis="<?= htmlspecialchars($eo['jenis_benang']) ?>"
                                            data-ukuran="<?= htmlspecialchars($eo['ukuran_benang'] ?? '') ?>"
                                            data-kode="<?= htmlspecialchars($eo['kode_warna'] ?? '') ?>"
                                            data-warna="<?= htmlspecialchars($eo['nama_warna'] ?? '') ?>"
                                            data-qty="<?= number_format($eo['qty'], 0, ',', '.') ?>"
                                            data-tgl="<?= date('d M Y', strtotime($eo['tanggal'])) ?>"
                                            data-status="<?= htmlspecialchars($eo['status']) ?>"
                                            <?= (int)($old['id_order'] ?? 0) === $eo['id_order'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($eo['no_order']) ?> —
                                        <?= htmlspecialchars($eo['jenis_benang']) ?>
                                        <?= $eo['ukuran_benang'] ? '(' . htmlspecialchars($eo['ukuran_benang']) . ')' : '' ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <?php if (isset($fieldErrors['id_order'])): ?>
                                <span class="bp-field__error"><?= htmlspecialchars($fieldErrors['id_order']) ?></span>
                            <?php endif; ?>
                        </div>

                        <!-- Preview pesanan yang dipilih -->
                        <div class="bp-order-preview" id="orderPreview"
                             style="<?= $selectedOrder ? '' : 'display:none' ?>">
                            <div class="bp-order-preview__row">
                                <span>No. Order</span>
                                <span id="prev-no" class="mono"><?= htmlspecialchars($selectedOrder['no_order'] ?? '') ?></span>
                            </div>
                            <div class="bp-order-preview__row">
                                <span>Produk</span>
                                <span id="prev-jenis"><?= htmlspecialchars($selectedOrder['jenis_benang'] ?? '') ?></span>
                            </div>
                            <div class="bp-order-preview__row" id="prev-row-ukuran"
                                 style="<?= ($selectedOrder && $selectedOrder['ukuran_benang']) ? '' : 'display:none' ?>">
                                <span>Ukuran</span>
                                <span id="prev-ukuran"><?= htmlspecialchars($selectedOrder['ukuran_benang'] ?? '') ?></span>
                            </div>
                            <div class="bp-order-preview__row" id="prev-row-warna"
                                 style="<?= ($selectedOrder && ($selectedOrder['kode_warna'] || $selectedOrder['nama_warna'])) ? '' : 'display:none' ?>">
                                <span>Warna</span>
                                <span id="prev-warna">
                                    <?php if ($selectedOrder && $selectedOrder['kode_warna']): ?>
                                        <span class="bp-color-code"><?= htmlspecialchars($selectedOrder['kode_warna']) ?></span>
                                    <?php endif; ?>
                                    <?= htmlspecialchars($selectedOrder['nama_warna'] ?? '') ?>
                                </span>
                            </div>
                            <div class="bp-order-preview__row">
                                <span>Qty</span>
                                <span id="prev-qty"><?= $selectedOrder ? number_format($selectedOrder['qty'], 0, ',', '.') . ' KG' : '' ?></span>
                            </div>
                            <div class="bp-order-preview__row">
                                <span>Tgl Pesanan</span>
                                <span id="prev-tgl"><?= $selectedOrder ? date('d M Y', strtotime($selectedOrder['tanggal'])) : '' ?></span>
                            </div>
                        </div>
                    </div>

                    <!-- Kategori & Alasan -->
                    <div class="bp-card">
                        <div class="bp-card__section-header">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                 stroke-linecap="round" stroke-linejoin="round" width="16" height="16">
                                <circle cx="12" cy="12" r="10"></circle>
                                <line x1="12" y1="8" x2="12" y2="12"></line>
                                <line x1="12" y1="16" x2="12.01" y2="16"></line>
                            </svg>
                            <h2>Alasan Return</h2>
                        </div>

                        <div class="bp-field <?= isset($fieldErrors['alasan_kategori']) ? 'is-error' : '' ?>">
                            <label for="alasan_kategori">Kategori <span class="bp-required">*</span></label>
                            <div class="bp-kat-options">
                                <?php
                                $katOptions = [
                                    'deviasi_warna'     => ['Deviasi Warna',     'Warna yang diterima berbeda dari sampel yang disetujui.'],
                                    'kualitas'          => ['Kualitas',           'Produk tidak memenuhi standar kualitas yang dijanjikan.'],
                                    'barang_rusak'      => ['Barang Rusak',       'Produk mengalami kerusakan fisik saat tiba.'],
                                    'spesifikasi_salah' => ['Spesifikasi Salah',  'Produk yang dikirim tidak sesuai spesifikasi PO.'],
                                    'lainnya'           => ['Lainnya',            'Alasan lain di luar kategori di atas.'],
                                ];
                                foreach ($katOptions as $val => [$label, $desc]):
                                    $checked = ($old['alasan_kategori'] ?? '') === $val;
                                ?>
                                    <label class="bp-kat-option <?= $checked ? 'is-checked' : '' ?>">
                                        <input type="radio" name="alasan_kategori" value="<?= $val ?>"
                                               <?= $checked ? 'checked' : '' ?>
                                               onchange="toggleKatOption(this)">
                                        <div class="bp-kat-option__content">
                                            <span class="bp-kat-option__label"><?= $label ?></span>
                                            <span class="bp-kat-option__desc"><?= $desc ?></span>
                                        </div>
                                        <div class="bp-kat-option__check">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                 stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"
                                                 width="13" height="13">
                                                <polyline points="20 6 9 17 4 12"></polyline>
                                            </svg>
                                        </div>
                                    </label>
                                <?php endforeach; ?>
                            </div>
                            <?php if (isset($fieldErrors['alasan_kategori'])): ?>
                                <span class="bp-field__error"><?= htmlspecialchars($fieldErrors['alasan_kategori']) ?></span>
                            <?php endif; ?>
                        </div>

                        <div class="bp-field <?= isset($fieldErrors['alasan']) ? 'is-error' : '' ?>" style="margin-top:4px;">
                            <label for="alasan">
                                Uraian Alasan <span class="bp-required">*</span>
                                <span class="bp-field__hint">Min. 20 karakter</span>
                            </label>
                            <textarea name="alasan" id="alasan" rows="5"
                                      placeholder="Jelaskan secara detail alasan pengajuan return ini, termasuk kondisi barang saat diterima…"
                                      oninput="updateCharCount(this)"><?= htmlspecialchars($old['alasan'] ?? '') ?></textarea>
                            <div class="bp-char-count">
                                <span id="charCount"><?= mb_strlen($old['alasan'] ?? '') ?></span> karakter
                                <span id="charMin" style="color:#ef4444;margin-left:6px;<?= mb_strlen($old['alasan'] ?? '') >= 20 ? 'display:none' : '' ?>">
                                    (min. 20)
                                </span>
                            </div>
                            <?php if (isset($fieldErrors['alasan'])): ?>
                                <span class="bp-field__error"><?= htmlspecialchars($fieldErrors['alasan']) ?></span>
                            <?php endif; ?>
                        </div>
                    </div>

                </div><!-- /col-left -->

                <!-- ===== Kolom Kanan ===== -->
                <div class="bp-form-col">

                    <!-- Upload Foto -->
                    <div class="bp-card">
                        <div class="bp-card__section-header">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                 stroke-linecap="round" stroke-linejoin="round" width="16" height="16">
                                <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
                                <circle cx="8.5" cy="8.5" r="1.5"></circle>
                                <polyline points="21 15 16 10 5 21"></polyline>
                            </svg>
                            <h2>Foto Bukti</h2>
                        </div>

                        <div class="bp-field">
                            <label>Upload Foto <span class="bp-field__hint">Maks. 5 foto · JPG/PNG/WebP · 2 MB/file</span></label>
                            <div class="bp-upload-area" id="uploadArea"
                                 onclick="document.getElementById('foto').click()"
                                 ondragover="event.preventDefault(); this.classList.add('is-drag')"
                                 ondragleave="this.classList.remove('is-drag')"
                                 ondrop="handleDrop(event)">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"
                                     stroke-linecap="round" stroke-linejoin="round" width="36" height="36">
                                    <polyline points="16 16 12 12 8 16"></polyline>
                                    <line x1="12" y1="12" x2="12" y2="21"></line>
                                    <path d="M20.39 18.39A5 5 0 0 0 18 9h-1.26A8 8 0 1 0 3 16.3"></path>
                                </svg>
                                <p class="bp-upload-area__text">Klik atau seret foto ke sini</p>
                                <p class="bp-upload-area__sub">JPG, PNG, WebP maks. 2 MB per file</p>
                                <input type="file" name="foto[]" id="foto"
                                       accept="image/jpeg,image/png,image/webp"
                                       multiple style="display:none"
                                       onchange="handleFileSelect(this)">
                            </div>
                            <div class="bp-preview-grid" id="previewGrid"></div>
                        </div>
                    </div>

                    <!-- Info -->
                    <div class="bp-card bp-info-card">
                        <div class="bp-card__section-header">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                 stroke-linecap="round" stroke-linejoin="round" width="16" height="16">
                                <circle cx="12" cy="12" r="10"></circle>
                                <line x1="12" y1="8" x2="12" y2="12"></line>
                                <line x1="12" y1="16" x2="12.01" y2="16"></line>
                            </svg>
                            <h2>Informasi</h2>
                        </div>
                        <div class="bp-info-rows">
                            <div class="bp-info-row">
                                <span class="bp-info-row__label">Tanggal Pengajuan</span>
                                <span class="bp-info-row__value"><?= date('d M Y') ?></span>
                            </div>
                            <div class="bp-info-row">
                                <span class="bp-info-row__label">Status Awal</span>
                                <span class="bp-badge badge-pending">Diajukan</span>
                            </div>
                            <div class="bp-info-row">
                                <span class="bp-info-row__label">Pesanan Eligible</span>
                                <span class="bp-info-row__value"><?= count($eligibleOrders) ?> pesanan</span>
                            </div>
                        </div>

                        <div class="bp-info-notice">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                 stroke-linecap="round" stroke-linejoin="round" width="14" height="14">
                                <circle cx="12" cy="12" r="10"></circle>
                                <line x1="12" y1="8" x2="12" y2="12"></line>
                                <line x1="12" y1="16" x2="12.01" y2="16"></line>
                            </svg>
                            <p>Pengajuan akan ditinjau oleh tim kami. Anda akan mendapat notifikasi setelah status diperbarui.</p>
                        </div>
                    </div>

                    <!-- Aksi -->
                    <div class="bp-form-actions">
                        <a href="<?= BUYER_URL ?>/returns.php" class="bp-btn-secondary">Batal</a>
                        <button type="submit" class="bp-btn-primary">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                 stroke-linecap="round" stroke-linejoin="round" width="15" height="15">
                                <line x1="22" y1="2" x2="11" y2="13"></line>
                                <polygon points="22 2 15 22 11 13 2 9 22 2"></polygon>
                            </svg>
                            Ajukan Return
                        </button>
                    </div>

                </div><!-- /col-right -->

            </div>
        </form>

        <?php endif; ?>

    </main>
</div>

<?php include __DIR__ . '/partials/_footer.php'; ?>

<style>
    body {
        margin: 0;
        font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
        background: #f9fafb;
        color: #111827;
    }

    .bp-retnew {
        padding: 24px;
        max-width: 960px;
        margin: 0 auto;
        display: flex;
        flex-direction: column;
        gap: 18px;
    }

    /* Heading */
    .bp-retnew__heading { display: flex; flex-direction: column; gap: 10px; }
    .bp-back {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        font-size: 13px;
        font-weight: 600;
        color: #6b7280;
        text-decoration: none;
    }
    .bp-back:hover { color: #1d4ed8; }
    .bp-retnew__title-row { display: flex; align-items: flex-start; justify-content: space-between; }
    .bp-retnew__title-row h1 { margin: 0 0 4px; font-size: 22px; font-weight: 700; }
    .bp-retnew__title-row p  { margin: 0; font-size: 14px; color: #6b7280; }

    /* Alert */
    .bp-alert {
        display: flex;
        align-items: flex-start;
        gap: 10px;
        padding: 12px 16px;
        border-radius: 10px;
        font-size: 13.5px;
        line-height: 1.5;
    }
    .bp-alert--error { background: #fef2f2; color: #b91c1c; }
    .bp-alert svg { flex-shrink: 0; margin-top: 2px; }

    /* Form grid */
    .bp-form-grid {
        display: grid;
        grid-template-columns: 1fr 320px;
        gap: 18px;
        align-items: start;
    }
    .bp-form-col { display: flex; flex-direction: column; gap: 18px; }

    /* Card */
    .bp-card {
        background: #fff;
        border: 1px solid #e5e7eb;
        border-radius: 14px;
        padding: 20px;
    }
    .bp-card__section-header {
        display: flex;
        align-items: center;
        gap: 8px;
        margin-bottom: 18px;
        color: #374151;
    }
    .bp-card__section-header h2 {
        margin: 0;
        font-size: 13px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        color: #374151;
    }

    /* Fields */
    .bp-field { display: flex; flex-direction: column; gap: 6px; margin-bottom: 16px; }
    .bp-field:last-child { margin-bottom: 0; }
    .bp-field label {
        display: flex;
        align-items: center;
        gap: 6px;
        font-size: 13px;
        font-weight: 600;
        color: #374151;
    }
    .bp-required { color: #ef4444; }
    .bp-field__hint { font-size: 11.5px; color: #9ca3af; font-weight: 400; margin-left: auto; }
    .bp-field__error { font-size: 12px; color: #ef4444; }

    .bp-field select,
    .bp-field textarea {
        width: 100%;
        padding: 9px 12px;
        border: 1px solid #d1d5db;
        border-radius: 8px;
        font-size: 13.5px;
        color: #111827;
        background: #fff;
        outline: none;
        box-sizing: border-box;
        font-family: inherit;
        transition: border-color 0.15s;
    }
    .bp-field select:focus,
    .bp-field textarea:focus { border-color: #2563eb; box-shadow: 0 0 0 3px rgba(37,99,235,.1); }
    .bp-field.is-error select,
    .bp-field.is-error textarea { border-color: #ef4444; }
    .bp-field textarea { resize: vertical; }

    /* Char count */
    .bp-char-count { font-size: 12px; color: #9ca3af; text-align: right; }

    /* Order preview */
    .bp-order-preview {
        margin-top: 12px;
        background: #f9fafb;
        border: 1px solid #e5e7eb;
        border-radius: 10px;
        overflow: hidden;
    }
    .bp-order-preview__row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 9px 14px;
        border-bottom: 1px solid #f3f4f6;
        font-size: 13px;
        gap: 10px;
    }
    .bp-order-preview__row:last-child { border-bottom: none; }
    .bp-order-preview__row > span:first-child { color: #6b7280; font-weight: 500; flex-shrink: 0; }
    .bp-order-preview__row > span:last-child  { font-weight: 600; color: #111827; text-align: right; }
    .mono { font-family: 'SF Mono','Fira Code',monospace; font-size: 12px; }
    .bp-color-code {
        font-family: 'SF Mono','Fira Code',monospace;
        font-size: 11.5px;
        font-weight: 600;
        background: #e5e7eb;
        color: #6b7280;
        padding: 2px 6px;
        border-radius: 4px;
        margin-right: 4px;
    }

    /* Kategori radio options */
    .bp-kat-options { display: flex; flex-direction: column; gap: 8px; }
    .bp-kat-option {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 12px 14px;
        border: 1.5px solid #e5e7eb;
        border-radius: 10px;
        cursor: pointer;
        transition: all 0.15s;
        background: #fff;
    }
    .bp-kat-option:hover { border-color: #93c5fd; background: #f8faff; }
    .bp-kat-option.is-checked { border-color: #2563eb; background: #eff6ff; }
    .bp-kat-option input[type="radio"] { display: none; }
    .bp-kat-option__content { flex: 1; }
    .bp-kat-option__label {
        display: block;
        font-size: 13.5px;
        font-weight: 600;
        color: #111827;
        margin-bottom: 2px;
    }
    .bp-kat-option__desc { font-size: 12px; color: #6b7280; line-height: 1.4; }
    .bp-kat-option__check {
        width: 22px;
        height: 22px;
        border-radius: 50%;
        border: 2px solid #d1d5db;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        color: transparent;
        transition: all 0.15s;
    }
    .bp-kat-option.is-checked .bp-kat-option__check {
        background: #2563eb;
        border-color: #2563eb;
        color: #fff;
    }

    /* Upload area */
    .bp-upload-area {
        border: 2px dashed #d1d5db;
        border-radius: 12px;
        padding: 28px 20px;
        text-align: center;
        cursor: pointer;
        transition: all 0.15s;
        color: #9ca3af;
        background: #fafafa;
    }
    .bp-upload-area:hover,
    .bp-upload-area.is-drag {
        border-color: #2563eb;
        background: #eff6ff;
        color: #2563eb;
    }
    .bp-upload-area__text { margin: 8px 0 4px; font-size: 14px; font-weight: 600; }
    .bp-upload-area__sub  { margin: 0; font-size: 12px; }

    /* Preview grid */
    .bp-preview-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(80px, 1fr));
        gap: 8px;
        margin-top: 10px;
    }
    .bp-preview-thumb {
        position: relative;
        aspect-ratio: 1;
        border-radius: 8px;
        overflow: hidden;
        border: 1px solid #e5e7eb;
        background: #f3f4f6;
    }
    .bp-preview-thumb img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        display: block;
    }
    .bp-preview-thumb__remove {
        position: absolute;
        top: 3px;
        right: 3px;
        width: 20px;
        height: 20px;
        background: rgba(0,0,0,.55);
        border: none;
        border-radius: 50%;
        color: #fff;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 14px;
        line-height: 1;
        padding: 0;
    }
    .bp-preview-thumb__remove:hover { background: #dc2626; }

    /* Info card */
    .bp-info-card { padding: 16px 20px; }
    .bp-info-rows { display: flex; flex-direction: column; }
    .bp-info-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 8px 0;
        border-bottom: 1px solid #f3f4f6;
        font-size: 13px;
        gap: 10px;
    }
    .bp-info-row:last-child { border-bottom: none; }
    .bp-info-row__label { color: #6b7280; font-weight: 500; }
    .bp-info-row__value { font-weight: 600; color: #111827; }

    .bp-info-notice {
        display: flex;
        gap: 8px;
        align-items: flex-start;
        margin-top: 14px;
        padding: 10px 12px;
        background: #fffbeb;
        border-radius: 8px;
        color: #92400e;
        font-size: 12.5px;
        line-height: 1.5;
    }
    .bp-info-notice svg { flex-shrink: 0; margin-top: 1px; }
    .bp-info-notice p { margin: 0; }

    /* Badges */
    .bp-badge {
        display: inline-flex;
        align-items: center;
        font-size: 12px;
        font-weight: 600;
        padding: 4px 10px;
        border-radius: 999px;
        white-space: nowrap;
    }
    .badge-pending { background: #fffbeb; color: #b45309; }

    /* Buttons */
    .bp-btn-primary {
        flex: 1;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        padding: 11px 18px;
        background: #2563eb;
        color: #fff;
        border: none;
        border-radius: 10px;
        font-size: 13.5px;
        font-weight: 600;
        cursor: pointer;
        font-family: inherit;
        transition: background 0.15s;
    }
    .bp-btn-primary:hover { background: #1d4ed8; }

    .bp-btn-secondary {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 11px 18px;
        background: #fff;
        color: #374151;
        border: 1px solid #d1d5db;
        border-radius: 10px;
        font-size: 13.5px;
        font-weight: 600;
        text-decoration: none;
        cursor: pointer;
        transition: background 0.15s;
    }
    .bp-btn-secondary:hover { background: #f3f4f6; }

    .bp-form-actions { display: flex; gap: 10px; }

    /* Empty state */
    .bp-empty-card {
        background: #fff;
        border: 1px solid #e5e7eb;
        border-radius: 14px;
        padding: 48px 24px;
        text-align: center;
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 10px;
        color: #d1d5db;
    }
    .bp-empty-card__title { margin: 0; font-size: 16px; font-weight: 700; color: #374151; }
    .bp-empty-card__sub   { margin: 0; font-size: 13.5px; color: #9ca3af; max-width: 380px; }

    /* Responsive */
    @media (max-width: 768px) {
        .bp-retnew { padding: 16px; gap: 14px; }
        .bp-form-grid { grid-template-columns: 1fr; }
        .bp-retnew__title-row { flex-direction: column; }
    }
</style>

<script>
// ---- Order preview ----
function updateOrderPreview(sel) {
    const opt     = sel.options[sel.selectedIndex];
    const preview = document.getElementById('orderPreview');

    if (!opt.value) {
        preview.style.display = 'none';
        return;
    }

    document.getElementById('prev-no').textContent    = opt.dataset.no    || '';
    document.getElementById('prev-jenis').textContent = opt.dataset.jenis  || '';
    document.getElementById('prev-qty').textContent   = (opt.dataset.qty   || '') + ' KG';
    document.getElementById('prev-tgl').textContent   = opt.dataset.tgl    || '';

    const ukuranRow = document.getElementById('prev-row-ukuran');
    if (opt.dataset.ukuran) {
        document.getElementById('prev-ukuran').textContent = opt.dataset.ukuran;
        ukuranRow.style.display = '';
    } else {
        ukuranRow.style.display = 'none';
    }

    const warnaRow = document.getElementById('prev-row-warna');
    const kode     = opt.dataset.kode || '';
    const warna    = opt.dataset.warna || '';
    if (kode || warna) {
        const warnaEl = document.getElementById('prev-warna');
        warnaEl.innerHTML = kode
            ? '<span class="bp-color-code">' + escHtml(kode) + '</span>' + escHtml(warna)
            : escHtml(warna);
        warnaRow.style.display = '';
    } else {
        warnaRow.style.display = 'none';
    }

    preview.style.display = '';
}

function escHtml(s) {
    return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
}

// ---- Kategori radio styling ----
function toggleKatOption(radio) {
    document.querySelectorAll('.bp-kat-option').forEach(el => el.classList.remove('is-checked'));
    radio.closest('.bp-kat-option').classList.add('is-checked');
}

// ---- Char counter ----
function updateCharCount(el) {
    const len = el.value.length;
    document.getElementById('charCount').textContent = len;
    document.getElementById('charMin').style.display = len >= 20 ? 'none' : 'inline';
}

// ---- File preview ----
let selectedFiles = [];

function renderPreviews() {
    const grid = document.getElementById('previewGrid');
    grid.innerHTML = '';
    selectedFiles.forEach((file, i) => {
        const url   = URL.createObjectURL(file);
        const thumb = document.createElement('div');
        thumb.className = 'bp-preview-thumb';
        thumb.innerHTML = `
            <img src="${url}" alt="${escHtml(file.name)}">
            <button type="button" class="bp-preview-thumb__remove"
                    onclick="removeFile(${i})" aria-label="Hapus foto">×</button>
        `;
        grid.appendChild(thumb);
    });
    syncFileInput();
}

function removeFile(idx) {
    selectedFiles.splice(idx, 1);
    renderPreviews();
}

function syncFileInput() {
    const dt    = new DataTransfer();
    selectedFiles.forEach(f => dt.items.add(f));
    document.getElementById('foto').files = dt.files;
}

function handleFileSelect(input) {
    addFiles(Array.from(input.files));
}

function handleDrop(e) {
    e.preventDefault();
    document.getElementById('uploadArea').classList.remove('is-drag');
    addFiles(Array.from(e.dataTransfer.files).filter(f =>
        ['image/jpeg','image/png','image/webp'].includes(f.type)
    ));
}

function addFiles(files) {
    const remaining = 5 - selectedFiles.length;
    files.slice(0, remaining).forEach(f => {
        if (f.size <= 2 * 1024 * 1024) selectedFiles.push(f);
    });
    renderPreviews();
}

// Init preview on page load (repopulate on validation error — not needed for files)
// Init order preview on load
(function () {
    const sel = document.getElementById('id_order');
    if (sel && sel.value) updateOrderPreview(sel);
})();
</script>

</body>
</html>