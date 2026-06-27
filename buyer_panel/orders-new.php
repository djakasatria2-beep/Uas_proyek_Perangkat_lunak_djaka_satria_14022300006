<?php
// ============================================================
//  buyer_panel/orders-new.php
//  Form ajukan pesanan baru — multi-item dengan session cart.
// ============================================================

define('REQUIRED_ROLE', 'buyer');
require_once __DIR__ . '/../assets/verifyRoleRedirect.php';
require_once __DIR__ . '/partials/config.php';

$idBuyer   = (int) ($currentBuyer['id_buyer'] ?? 0);
$pageTitle = 'Buat Pesanan Baru';

// Mulai session untuk cart item sementara
if (session_status() === PHP_SESSION_NONE) session_start();
$cartKey = 'order_cart_' . $idBuyer;
if (!isset($_SESSION[$cartKey])) $_SESSION[$cartKey] = [];

$errors      = [];
$fieldErrors = [];

// ------------------------------------------------------------
// Auto-generate no_order: ORD-YYYY-XXXX
// ------------------------------------------------------------
function generateNoOrder(mysqli $conn): string {
    $year = date('Y');
    $res  = $conn->query("
        SELECT no_order FROM orders
        WHERE no_order LIKE 'ORD-{$year}-%'
        ORDER BY id_order DESC LIMIT 1
    ");
    $last = $res && $res->num_rows ? $res->fetch_assoc()['no_order'] : null;
    $seq  = $last ? ((int) substr($last, -4)) + 1 : 1;
    return sprintf('ORD-%s-%04d', $year, $seq);
}

// ------------------------------------------------------------
// Pilihan jenis & ukuran benang dari tabel products
// ------------------------------------------------------------
$jenisList  = [];
$ukuranList = [];

$resP = $conn->query("SELECT DISTINCT material_type FROM products ORDER BY material_type");
while ($r = $resP->fetch_assoc()) $jenisList[] = $r['material_type'];

$resU = $conn->query("SELECT DISTINCT denier FROM products WHERE denier IS NOT NULL ORDER BY denier");
while ($r = $resU->fetch_assoc()) $ukuranList[] = $r['denier'];

// ------------------------------------------------------------
// Handle actions
// ------------------------------------------------------------
$action = $_POST['action'] ?? '';

// ---- Hapus item dari cart ----
if ($action === 'remove_item' && isset($_POST['item_idx'])) {
    $idx = (int) $_POST['item_idx'];
    if (isset($_SESSION[$cartKey][$idx])) {
        array_splice($_SESSION[$cartKey], $idx, 1);
    }
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
}

// ---- Tambah item ke cart ----
if ($action === 'add_item') {
    $jenisBenang  = trim($_POST['jenis_benang'] ?? '');
    $ukuranBenang = trim($_POST['ukuran_benang'] ?? '');
    $kodeWarna    = trim($_POST['kode_warna'] ?? '');
    $namaWarna    = trim($_POST['nama_warna'] ?? '');
    $qty          = (int) ($_POST['qty'] ?? 0);
    $hargaBenang  = (float) str_replace(['.', ','], ['', '.'], $_POST['harga_benang'] ?? '0');
    $catatan      = trim($_POST['catatan'] ?? '');

    if ($jenisBenang === '') $fieldErrors['jenis_benang'] = 'Jenis benang wajib diisi.';
    if ($qty <= 0)           $fieldErrors['qty']          = 'Qty harus lebih dari 0.';
    if ($hargaBenang <= 0)   $fieldErrors['harga_benang'] = 'Harga benang wajib diisi.';

    if (empty($fieldErrors)) {
        $_SESSION[$cartKey][] = [
            'jenis_benang'  => $jenisBenang,
            'ukuran_benang' => $ukuranBenang,
            'kode_warna'    => $kodeWarna,
            'nama_warna'    => $namaWarna,
            'qty'           => $qty,
            'harga_benang'  => $hargaBenang,
            'catatan'       => $catatan,
        ];
        // Reset form setelah tambah
        header('Location: ' . $_SERVER['PHP_SELF'] . '?added=1');
        exit;
    }

    // Repopulate form jika error
    $old = compact('jenisBenang','ukuranBenang','kodeWarna','namaWarna','qty','hargaBenang','catatan');
}

// ---- Ajukan semua item dalam cart ke DB ----
if ($action === 'submit_order') {
    if (empty($_SESSION[$cartKey])) {
        $errors[] = 'Tambahkan minimal satu item sebelum mengajukan pesanan.';
    } else {
        $tanggal = date('Y-m-d');
        $success = true;
        $firstId = null;

        $conn->begin_transaction();
        try {
            foreach ($_SESSION[$cartKey] as $item) {
                $noOrder = generateNoOrder($conn);
                $stmt = $conn->prepare("
                    INSERT INTO orders
                        (id_buyer, no_order, jenis_benang, ukuran_benang, kode_warna, nama_warna,
                         qty, harga_benang, tanggal, catatan, status)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending')
                ");
                $stmt->bind_param(
                    'isssssidss',
                    $idBuyer, $noOrder,
                    $item['jenis_benang'], $item['ukuran_benang'],
                    $item['kode_warna'],   $item['nama_warna'],
                    $item['qty'],          $item['harga_benang'],
                    $tanggal,              $item['catatan']
                );
                $stmt->execute();
                if (!$firstId) $firstId = $conn->insert_id;
                $stmt->close();
            }
            $conn->commit();
            unset($_SESSION[$cartKey]); // Bersihkan cart
            header('Location: ' . BUYER_URL . '/orders.php?submitted=1');
            exit;
        } catch (Exception $e) {
            $conn->rollback();
            $errors[] = 'Gagal menyimpan pesanan. Silakan coba lagi.';
        }
    }
}

// Default old values
if (!isset($old)) {
    $old = [
        'jenisBenang'  => '',
        'ukuranBenang' => '',
        'kodeWarna'    => '',
        'namaWarna'    => '',
        'qty'          => '',
        'hargaBenang'  => '',
        'catatan'      => '',
    ];
}

$cart       = $_SESSION[$cartKey];
$cartTotal  = array_sum(array_map(fn($i) => $i['qty'] * $i['harga_benang'], $cart));
$cartCount  = count($cart);
$justAdded  = isset($_GET['added']);
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
    <main class="bp-new">

        <!-- Heading -->
        <div class="bp-new__heading">
            <a href="<?= BUYER_URL ?>/orders.php" class="bp-back">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="16" height="16">
                    <polyline points="15 18 9 12 15 6"></polyline>
                </svg>
                Kembali ke Daftar Pesanan
            </a>
            <div class="bp-new__title-wrap">
                <div>
                    <h1>Buat Pesanan Baru</h1>
                    <p>Tambahkan item benang lalu ajukan pesanan sekaligus.</p>
                </div>
            </div>
        </div>

        <?php if (!empty($errors)): ?>
            <div class="bp-alert bp-alert--error">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="18" height="18">
                    <circle cx="12" cy="12" r="10"></circle>
                    <line x1="12" y1="8" x2="12" y2="12"></line>
                    <line x1="12" y1="16" x2="12.01" y2="16"></line>
                </svg>
                <span><?= htmlspecialchars($errors[0]) ?></span>
            </div>
        <?php endif; ?>

        <?php if ($justAdded): ?>
            <div class="bp-alert bp-alert--success">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="18" height="18">
                    <polyline points="20 6 9 17 4 12"></polyline>
                </svg>
                <span>Item berhasil ditambahkan. Tambah item lain atau ajukan pesanan.</span>
            </div>
        <?php endif; ?>

        <!-- ============ DAFTAR ITEM (cart) ============ -->
        <?php if (!empty($cart)): ?>
        <section class="bp-card bp-cart">
            <div class="bp-cart__header">
                <div class="bp-cart__title">
                    <h2>Item Pesanan</h2>
                    <span class="bp-cart__count"><?= $cartCount ?> item</span>
                </div>
                <div class="bp-cart__total-wrap">
                    <span class="bp-cart__total-label">Total Estimasi</span>
                    <span class="bp-cart__total-value">Rp <?= number_format($cartTotal, 0, ',', '.') ?></span>
                </div>
            </div>

            <div class="bp-table-wrap">
                <table class="bp-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Jenis Benang</th>
                            <th>Ukuran</th>
                            <th>Kode / Nama Warna</th>
                            <th>Qty (kg)</th>
                            <th>Harga / kg</th>
                            <th>Subtotal</th>
                            <th>Catatan</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($cart as $idx => $item): ?>
                            <tr>
                                <td data-label="#"><?= $idx + 1 ?></td>
                                <td data-label="Jenis Benang"><?= htmlspecialchars($item['jenis_benang']) ?></td>
                                <td data-label="Ukuran"><?= htmlspecialchars($item['ukuran_benang'] ?: '-') ?></td>
                                <td data-label="Kode / Nama Warna">
                                    <?php if ($item['kode_warna'] || $item['nama_warna']): ?>
                                        <?php if ($item['kode_warna']): ?>
                                            <span class="bp-color-code"><?= htmlspecialchars($item['kode_warna']) ?></span>
                                        <?php endif; ?>
                                        <?php if ($item['nama_warna']): ?>
                                            <span class="bp-color-name"><?= htmlspecialchars($item['nama_warna']) ?></span>
                                        <?php endif; ?>
                                    <?php else: ?>-<?php endif; ?>
                                </td>
                                <td data-label="Qty (kg)"><?= number_format($item['qty'], 0, ',', '.') ?></td>
                                <td data-label="Harga / kg">Rp <?= number_format($item['harga_benang'], 0, ',', '.') ?></td>
                                <td data-label="Subtotal"><strong>Rp <?= number_format($item['qty'] * $item['harga_benang'], 0, ',', '.') ?></strong></td>
                                <td data-label="Catatan">
                                    <?php if ($item['catatan']): ?>
                                        <span class="bp-catatan-snippet" title="<?= htmlspecialchars($item['catatan']) ?>">
                                            <?= htmlspecialchars(mb_strimwidth($item['catatan'], 0, 30, '…')) ?>
                                        </span>
                                    <?php else: ?>-<?php endif; ?>
                                </td>
                                <td class="bp-table__action">
                                    <form method="POST" action="" onsubmit="return confirm('Hapus item ini?')">
                                        <input type="hidden" name="action" value="remove_item">
                                        <input type="hidden" name="item_idx" value="<?= $idx ?>">
                                        <button type="submit" class="bp-btn-remove" aria-label="Hapus item">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="15" height="15">
                                                <polyline points="3 6 5 6 21 6"></polyline>
                                                <path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"></path>
                                                <path d="M10 11v6M14 11v6"></path>
                                                <path d="M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"></path>
                                            </svg>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Tombol Ajukan Pesanan -->
            <div class="bp-cart__footer">
                <form method="POST" action="">
                    <input type="hidden" name="action" value="submit_order">
                    <button type="submit" class="bp-btn-submit">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="16" height="16">
                            <line x1="22" y1="2" x2="11" y2="13"></line>
                            <polygon points="22 2 15 22 11 13 2 9 22 2"></polygon>
                        </svg>
                        Ajukan <?= $cartCount ?> Pesanan
                    </button>
                </form>
            </div>
        </section>
        <?php endif; ?>

        <!-- ============ FORM TAMBAH ITEM ============ -->
        <form method="POST" action="" novalidate id="formTambah">
            <input type="hidden" name="action" value="add_item">

            <div class="bp-form-grid">

                <!-- Kolom kiri -->
                <div class="bp-form-col">

                    <!-- Detail Produk -->
                    <div class="bp-card">
                        <div class="bp-card__section-header">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="16" height="16">
                                <path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"></path>
                                <line x1="3" y1="6" x2="21" y2="6"></line>
                                <path d="M16 10a4 4 0 0 1-8 0"></path>
                            </svg>
                            <h2>Detail Produk</h2>
                        </div>

                        <div class="bp-field <?= isset($fieldErrors['jenis_benang']) ? 'is-error' : '' ?>">
                            <label for="jenis_benang">Jenis Benang <span class="bp-required">*</span></label>
                            <?php if (!empty($jenisList)): ?>
                                <select name="jenis_benang" id="jenis_benang">
                                    <option value="">— Pilih jenis benang —</option>
                                    <?php foreach ($jenisList as $j): ?>
                                        <option value="<?= htmlspecialchars($j) ?>"
                                            <?= ($old['jenisBenang'] ?? '') === $j ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($j) ?>
                                        </option>
                                    <?php endforeach; ?>
                                    <option value="__other__" <?= (!in_array($old['jenisBenang'] ?? '', $jenisList) && ($old['jenisBenang'] ?? '') !== '') ? 'selected' : '' ?>>
                                        Lainnya…
                                    </option>
                                </select>
                                <input type="text" name="jenis_benang_custom" id="jenis_benang_custom"
                                       placeholder="Tulis jenis benang"
                                       value="<?= (!in_array($old['jenisBenang'] ?? '', $jenisList) && ($old['jenisBenang'] ?? '') !== '') ? htmlspecialchars($old['jenisBenang']) : '' ?>"
                                       style="display:none; margin-top:8px;">
                            <?php else: ?>
                                <input type="text" name="jenis_benang" id="jenis_benang"
                                       placeholder="cth. POLYAMIDE NYLON"
                                       value="<?= htmlspecialchars($old['jenisBenang'] ?? '') ?>">
                            <?php endif; ?>
                            <?php if (isset($fieldErrors['jenis_benang'])): ?>
                                <span class="bp-field__error"><?= htmlspecialchars($fieldErrors['jenis_benang']) ?></span>
                            <?php endif; ?>
                        </div>

                        <div class="bp-field">
                            <label for="ukuran_benang">Ukuran / Denier</label>
                            <?php if (!empty($ukuranList)): ?>
                                <select name="ukuran_benang" id="ukuran_benang">
                                    <option value="">— Pilih ukuran —</option>
                                    <?php foreach ($ukuranList as $u): ?>
                                        <option value="<?= htmlspecialchars($u) ?>"
                                            <?= ($old['ukuranBenang'] ?? '') === $u ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($u) ?>
                                        </option>
                                    <?php endforeach; ?>
                                    <option value="__other__" <?= (!in_array($old['ukuranBenang'] ?? '', $ukuranList) && ($old['ukuranBenang'] ?? '') !== '') ? 'selected' : '' ?>>
                                        Lainnya…
                                    </option>
                                </select>
                                <input type="text" name="ukuran_benang_custom" id="ukuran_benang_custom"
                                       placeholder="cth. 70D/24FX2"
                                       value="<?= (!in_array($old['ukuranBenang'] ?? '', $ukuranList) && ($old['ukuranBenang'] ?? '') !== '') ? htmlspecialchars($old['ukuranBenang']) : '' ?>"
                                       style="display:none; margin-top:8px;">
                            <?php else: ?>
                                <input type="text" name="ukuran_benang" id="ukuran_benang"
                                       placeholder="cth. 70D/24FX2"
                                       value="<?= htmlspecialchars($old['ukuranBenang'] ?? '') ?>">
                            <?php endif; ?>
                        </div>

                        <div class="bp-row-2">
                            <div class="bp-field">
                                <label for="kode_warna">Kode Warna</label>
                                <input type="text" name="kode_warna" id="kode_warna"
                                       placeholder="cth. 59651"
                                       value="<?= htmlspecialchars($old['kodeWarna'] ?? '') ?>">
                            </div>
                            <div class="bp-field">
                                <label for="nama_warna">Nama Warna</label>
                                <input type="text" name="nama_warna" id="nama_warna"
                                       placeholder="cth. TURBULENCE"
                                       value="<?= htmlspecialchars($old['namaWarna'] ?? '') ?>">
                            </div>
                        </div>
                    </div>

                    <!-- Catatan -->
                    <div class="bp-card">
                        <div class="bp-card__section-header">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="16" height="16">
                                <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                                <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                            </svg>
                            <h2>Catatan Item Ini</h2>
                        </div>
                        <div class="bp-field">
                            <label for="catatan">Catatan Tambahan</label>
                            <textarea name="catatan" id="catatan" rows="3"
                                      placeholder="Instruksi khusus untuk item ini…"><?= htmlspecialchars($old['catatan'] ?? '') ?></textarea>
                        </div>
                    </div>

                </div>

                <!-- Kolom kanan -->
                <div class="bp-form-col">

                    <!-- Jumlah & Harga -->
                    <div class="bp-card">
                        <div class="bp-card__section-header">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="16" height="16">
                                <line x1="12" y1="1" x2="12" y2="23"></line>
                                <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path>
                            </svg>
                            <h2>Jumlah & Harga</h2>
                        </div>

                        <div class="bp-field <?= isset($fieldErrors['qty']) ? 'is-error' : '' ?>">
                            <label for="qty">Jumlah <span class="bp-required">*</span></label>
                            <div class="bp-input-addon">
                                <input type="number" name="qty" id="qty" min="1"
                                       placeholder="0"
                                       value="<?= htmlspecialchars((string)($old['qty'] ?? '')) ?>"
                                       oninput="hitungTotal()">
                                <span class="bp-addon">KG</span>
                            </div>
                            <?php if (isset($fieldErrors['qty'])): ?>
                                <span class="bp-field__error"><?= htmlspecialchars($fieldErrors['qty']) ?></span>
                            <?php endif; ?>
                        </div>

                        <div class="bp-field <?= isset($fieldErrors['harga_benang']) ? 'is-error' : '' ?>">
                            <label for="harga_benang">Harga per kg <span class="bp-required">*</span></label>
                            <div class="bp-input-addon">
                                <span class="bp-addon bp-addon--left">Rp</span>
                                <input type="text" name="harga_benang" id="harga_benang"
                                       placeholder="0"
                                       value="<?= isset($old['hargaBenang']) && $old['hargaBenang'] ? number_format((float)$old['hargaBenang'], 0, ',', '.') : '' ?>"
                                       oninput="formatHarga(this); hitungTotal()">
                            </div>
                            <?php if (isset($fieldErrors['harga_benang'])): ?>
                                <span class="bp-field__error"><?= htmlspecialchars($fieldErrors['harga_benang']) ?></span>
                            <?php endif; ?>
                        </div>

                        <!-- Preview subtotal item ini -->
                        <div class="bp-total-preview">
                            <div class="bp-total-preview__label">Subtotal Item Ini</div>
                            <div class="bp-total-preview__value" id="totalPreview">Rp 0</div>
                        </div>
                    </div>

                    <!-- Info -->
                    <div class="bp-card bp-info-card">
                        <div class="bp-info-row">
                            <span class="bp-info-row__label">Tanggal Pesanan</span>
                            <span class="bp-info-row__value"><?= date('d M Y') ?></span>
                        </div>
                        <div class="bp-info-row">
                            <span class="bp-info-row__label">Status Awal</span>
                            <span class="bp-badge badge-pending">Menunggu</span>
                        </div>
                        <div class="bp-info-row">
                            <span class="bp-info-row__label">Item di cart</span>
                            <span class="bp-info-row__value"><?= $cartCount ?> item</span>
                        </div>
                    </div>

                    <!-- Aksi -->
                    <div class="bp-form-actions">
                        <a href="<?= BUYER_URL ?>/orders.php" class="bp-btn-secondary">Batal</a>
                        <button type="submit" class="bp-btn-primary">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="16" height="16">
                                <line x1="12" y1="5" x2="12" y2="19"></line>
                                <line x1="5" y1="12" x2="19" y2="12"></line>
                            </svg>
                            Simpan Item
                        </button>
                    </div>

                </div>
            </div>
        </form>

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

    .bp-new {
        padding: 24px;
        max-width: 960px;
        margin: 0 auto;
        display: flex;
        flex-direction: column;
        gap: 18px;
    }

    /* Heading */
    .bp-new__heading { display: flex; flex-direction: column; gap: 10px; }
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
    .bp-new__title-wrap { display: flex; align-items: flex-start; justify-content: space-between; }
    .bp-new__title-wrap h1 { margin: 0 0 4px; font-size: 22px; font-weight: 700; }
    .bp-new__title-wrap p  { margin: 0; font-size: 14px; color: #6b7280; }

    /* Alert */
    .bp-alert {
        display: flex;
        align-items: flex-start;
        gap: 10px;
        padding: 12px 16px;
        border-radius: 10px;
        font-size: 13.5px;
        line-height: 1.4;
    }
    .bp-alert--error   { background: #fef2f2; color: #b91c1c; }
    .bp-alert--success { background: #f0fdf4; color: #166534; }
    .bp-alert svg { flex-shrink: 0; margin-top: 1px; }

    /* Card */
    .bp-card {
        background: #ffffff;
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
        font-size: 14px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        color: #374151;
    }

    /* ===== Cart ===== */
    .bp-cart { padding: 0; overflow: hidden; }
    .bp-cart__header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 12px;
        padding: 18px 20px 14px;
        border-bottom: 1px solid #e5e7eb;
    }
    .bp-cart__title { display: flex; align-items: center; gap: 10px; }
    .bp-cart__title h2 { margin: 0; font-size: 16px; font-weight: 700; }
    .bp-cart__count {
        background: #eff6ff;
        color: #1d4ed8;
        font-size: 12px;
        font-weight: 700;
        padding: 3px 9px;
        border-radius: 999px;
    }
    .bp-cart__total-wrap { display: flex; align-items: center; gap: 10px; }
    .bp-cart__total-label { font-size: 13px; color: #6b7280; font-weight: 500; }
    .bp-cart__total-value { font-size: 18px; font-weight: 700; color: #111827; }

    .bp-cart__footer {
        padding: 14px 20px;
        border-top: 1px solid #e5e7eb;
        display: flex;
        justify-content: flex-end;
    }
    .bp-btn-submit {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 11px 22px;
        background: #16a34a;
        color: #ffffff;
        border: none;
        border-radius: 10px;
        font-size: 13.5px;
        font-weight: 600;
        cursor: pointer;
        transition: background 0.15s;
    }
    .bp-btn-submit:hover { background: #15803d; }

    /* Warna */
    .bp-color-code {
        font-family: 'SF Mono', 'Fira Code', 'Consolas', monospace;
        font-size: 11.5px;
        font-weight: 600;
        color: #6b7280;
        background: #f3f4f6;
        padding: 2px 6px;
        border-radius: 4px;
        margin-right: 4px;
    }
    .bp-color-name { font-size: 13px; color: #374151; }
    .bp-catatan-snippet { font-size: 12.5px; color: #6b7280; }

    /* Tombol hapus item */
    .bp-btn-remove {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 30px;
        height: 30px;
        border: 1px solid #e5e7eb;
        border-radius: 7px;
        background: #fff;
        color: #9ca3af;
        cursor: pointer;
        transition: all 0.15s;
    }
    .bp-btn-remove:hover { background: #fef2f2; border-color: #fca5a5; color: #dc2626; }

    /* Form grid */
    .bp-form-grid {
        display: grid;
        grid-template-columns: 1fr 340px;
        gap: 18px;
        align-items: start;
    }
    .bp-form-col { display: flex; flex-direction: column; gap: 18px; }

    /* Fields */
    .bp-field { display: flex; flex-direction: column; gap: 6px; margin-bottom: 14px; }
    .bp-field:last-child { margin-bottom: 0; }
    .bp-field label { font-size: 13px; font-weight: 600; color: #374151; }
    .bp-required { color: #ef4444; }

    .bp-field input[type="text"],
    .bp-field input[type="number"],
    .bp-field select,
    .bp-field textarea {
        width: 100%;
        padding: 9px 12px;
        border: 1px solid #d1d5db;
        border-radius: 8px;
        font-size: 13.5px;
        color: #111827;
        background: #ffffff;
        outline: none;
        box-sizing: border-box;
        transition: border-color 0.15s;
        font-family: inherit;
    }
    .bp-field input:focus,
    .bp-field select:focus,
    .bp-field textarea:focus { border-color: #2563eb; box-shadow: 0 0 0 3px rgba(37,99,235,.1); }
    .bp-field textarea { resize: vertical; }
    .bp-field.is-error input,
    .bp-field.is-error select { border-color: #ef4444; }
    .bp-field__error { font-size: 12px; color: #ef4444; }

    .bp-row-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }

    /* Addon inputs */
    .bp-input-addon {
        display: flex;
        align-items: stretch;
        border: 1px solid #d1d5db;
        border-radius: 8px;
        overflow: hidden;
        transition: border-color 0.15s;
    }
    .bp-input-addon:focus-within { border-color: #2563eb; box-shadow: 0 0 0 3px rgba(37,99,235,.1); }
    .bp-input-addon input {
        flex: 1;
        border: none !important;
        border-radius: 0 !important;
        box-shadow: none !important;
        padding: 9px 12px;
    }
    .bp-addon {
        display: flex;
        align-items: center;
        padding: 0 12px;
        background: #f3f4f6;
        font-size: 13px;
        font-weight: 600;
        color: #6b7280;
        white-space: nowrap;
        border-left: 1px solid #d1d5db;
    }
    .bp-addon--left { border-left: none; border-right: 1px solid #d1d5db; order: -1; }

    /* Total preview */
    .bp-total-preview {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 14px 16px;
        background: #eff6ff;
        border-radius: 10px;
        margin-top: 4px;
    }
    .bp-total-preview__label { font-size: 13px; font-weight: 600; color: #1d4ed8; }
    .bp-total-preview__value { font-size: 18px; font-weight: 700; color: #1d4ed8; }

    /* Info card */
    .bp-info-card { padding: 16px 20px; }
    .bp-info-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 9px 0;
        border-bottom: 1px solid #f3f4f6;
        font-size: 13px;
    }
    .bp-info-row:last-child { border-bottom: none; }
    .bp-info-row__label { color: #6b7280; font-weight: 500; }
    .bp-info-row__value { font-weight: 600; color: #111827; }

    /* Badge */
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

    /* Actions */
    .bp-form-actions { display: flex; gap: 10px; }
    .bp-btn-primary {
        flex: 1;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        padding: 11px 16px;
        background: #2563eb;
        color: #ffffff;
        border: none;
        border-radius: 10px;
        font-size: 13.5px;
        font-weight: 600;
        cursor: pointer;
        transition: background 0.15s;
    }
    .bp-btn-primary:hover { background: #1d4ed8; }
    .bp-btn-secondary {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 11px 16px;
        background: #ffffff;
        color: #374151;
        border: 1px solid #d1d5db;
        border-radius: 10px;
        font-size: 13.5px;
        font-weight: 600;
        cursor: pointer;
        text-decoration: none;
        transition: background 0.15s;
    }
    .bp-btn-secondary:hover { background: #f3f4f6; }

    /* Tabel cart */
    .bp-table-wrap { overflow-x: auto; }
    .bp-table { width: 100%; border-collapse: collapse; font-size: 13.5px; }
    .bp-table th {
        text-align: left;
        padding: 10px 12px;
        color: #6b7280;
        font-weight: 600;
        font-size: 12px;
        text-transform: uppercase;
        letter-spacing: 0.02em;
        border-bottom: 1px solid #e5e7eb;
        white-space: nowrap;
    }
    .bp-table td {
        padding: 11px 12px;
        border-bottom: 1px solid #f3f4f6;
        white-space: nowrap;
    }
    .bp-table tbody tr:last-child td { border-bottom: none; }
    .bp-table__action { text-align: right; }

    /* ====== Responsive ====== */
    @media (max-width: 768px) {
        .bp-new { padding: 16px; gap: 14px; }
        .bp-form-grid { grid-template-columns: 1fr; }
        .bp-row-2 { grid-template-columns: 1fr; }
        .bp-cart__header { flex-direction: column; align-items: flex-start; }

        .bp-table thead { display: none; }
        .bp-table, .bp-table tbody, .bp-table tr, .bp-table td { display: block; width: 100%; }
        .bp-table tr { border: 1px solid #e5e7eb; border-radius: 10px; margin: 0 0 8px; padding: 4px; }
        .bp-table td {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 10px;
            border-bottom: none;
            padding: 6px 10px;
        }
        .bp-table td[data-label]:not([data-label=""])::before {
            content: attr(data-label);
            font-weight: 600;
            color: #6b7280;
            font-size: 12px;
            flex-shrink: 0;
        }
        .bp-table__action { justify-content: flex-end; }
    }
</style>

<script>
function formatHarga(el) {
    let raw = el.value.replace(/\D/g, '');
    el.value = raw ? parseInt(raw, 10).toLocaleString('id-ID') : '';
}

function hitungTotal() {
    const qty   = parseInt(document.getElementById('qty').value || '0', 10);
    const harga = parseInt((document.getElementById('harga_benang').value || '0').replace(/\D/g, ''), 10);
    document.getElementById('totalPreview').textContent =
        'Rp ' + (qty * harga).toLocaleString('id-ID');
}

// Dropdown "Lainnya…" — jenis benang
(function () {
    const sel = document.getElementById('jenis_benang');
    const inp = document.getElementById('jenis_benang_custom');
    if (!sel || !inp) return;
    function toggle() {
        const show = sel.value === '__other__';
        inp.style.display = show ? 'block' : 'none';
        if (show) inp.focus();
    }
    sel.addEventListener('change', toggle);
    toggle();
    sel.closest('form').addEventListener('submit', function () {
        if (sel.value === '__other__') { sel.name = ''; inp.name = 'jenis_benang'; }
    });
})();

// Dropdown "Lainnya…" — ukuran benang
(function () {
    const sel = document.getElementById('ukuran_benang');
    const inp = document.getElementById('ukuran_benang_custom');
    if (!sel || !inp) return;
    function toggle() {
        const show = sel.value === '__other__';
        inp.style.display = show ? 'block' : 'none';
        if (show) inp.focus();
    }
    sel.addEventListener('change', toggle);
    toggle();
    sel.closest('form').addEventListener('submit', function () {
        if (sel.value === '__other__') { sel.name = ''; inp.name = 'ukuran_benang'; }
    });
})();

hitungTotal();
</script>

</body>
</html>