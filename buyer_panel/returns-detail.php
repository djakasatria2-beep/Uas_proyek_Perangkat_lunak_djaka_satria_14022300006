<?php
// ============================================================
//  buyer_panel/returns-detail.php
//  Detail satu pengajuan return milik buyer yang login.
// ============================================================

define('REQUIRED_ROLE', 'buyer');
require_once __DIR__ . '/../assets/verifyRoleRedirect.php';
require_once __DIR__ . '/partials/config.php';

$idBuyer   = (int) ($currentBuyer['id_buyer'] ?? 0);
$pageTitle = 'Detail Return';

// ------------------------------------------------------------
// Ambil ID return dari query string
// ------------------------------------------------------------
$idReturn = (int) ($_GET['id'] ?? 0);
if (!$idReturn) {
    header('Location: ' . BUYER_URL . '/returns.php');
    exit;
}

// ------------------------------------------------------------
// Query return — pastikan milik buyer yang sedang login
// ------------------------------------------------------------
$stmt = $conn->prepare("
    SELECT r.*,
           o.no_order, o.jenis_benang, o.ukuran_benang,
           o.kode_warna, o.nama_warna, o.qty, o.harga_benang,
           o.tanggal AS tanggal_order, o.status AS order_status,
           o.catatan AS catatan_order, o.id_order
    FROM order_returns r
    JOIN orders o ON o.id_order = r.id_order
    JOIN buyer_profile bp ON bp.id_buyer = o.id_buyer
    WHERE r.id_return = ? AND o.id_buyer = ?
    LIMIT 1
");
$stmt->bind_param('ii', $idReturn, $idBuyer);
$stmt->execute();
$ret = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$ret) {
    header('Location: ' . BUYER_URL . '/returns.php?not_found=1');
    exit;
}

// ------------------------------------------------------------
// Foto (JSON array path)
// ------------------------------------------------------------
$fotoList = [];
if ($ret['foto']) {
    $decoded = json_decode($ret['foto'], true);
    if (is_array($decoded)) $fotoList = $decoded;
}

// ------------------------------------------------------------
// Helpers
// ------------------------------------------------------------
function retStatusBadge(string $s): string {
    $map = [
        'submitted'    => ['Diajukan',  'badge-pending'],
        'under_review' => ['Ditinjau',  'badge-processing'],
        'approved'     => ['Disetujui', 'badge-shipped'],
        'resolved'     => ['Selesai',   'badge-done'],
        'rejected'     => ['Ditolak',   'badge-cancelled'],
    ];
    $d = $map[$s] ?? [ucfirst($s), 'badge-pending'];
    return '<span class="bp-badge ' . $d[1] . '">' . $d[0] . '</span>';
}

function orderStatusBadge(string $s): string {
    $map = [
        'pending'    => ['Menunggu',   'badge-pending'],
        'processing' => ['Diproses',   'badge-processing'],
        'shipped'    => ['Dikirim',    'badge-shipped'],
        'done'       => ['Selesai',    'badge-done'],
        'cancelled'  => ['Dibatalkan', 'badge-cancelled'],
    ];
    $d = $map[$s] ?? [ucfirst($s), 'badge-pending'];
    return '<span class="bp-badge ' . $d[1] . '">' . $d[0] . '</span>';
}

function retKatLabel(string $k): string {
    $map = [
        'deviasi_warna'     => 'Deviasi Warna',
        'kualitas'          => 'Kualitas',
        'barang_rusak'      => 'Barang Rusak',
        'spesifikasi_salah' => 'Spesifikasi Salah',
        'lainnya'           => 'Lainnya',
    ];
    return $map[$k] ?? ucfirst($k);
}

// Status progress steps
$steps = [
    'submitted'    => 1,
    'under_review' => 2,
    'approved'     => 3,
    'resolved'     => 4,
];
$currentStep = $steps[$ret['status']] ?? ($ret['status'] === 'rejected' ? -1 : 1);
$isRejected  = $ret['status'] === 'rejected';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($ret['no_return']) ?> — Detail Return</title>
</head>
<body>

<?php include __DIR__ . '/partials/_header.php'; ?>
<?php include __DIR__ . '/partials/_sidebar.php'; ?>
<?php include __DIR__ . '/partials/overdue-banner.php'; ?>

<div class="bp-content">
    <main class="bp-retdetail">

        <!-- ===== Heading ===== -->
        <div class="bp-retdetail__heading">
            <a href="<?= BUYER_URL ?>/returns.php" class="bp-back">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                     stroke-linecap="round" stroke-linejoin="round" width="16" height="16">
                    <polyline points="15 18 9 12 15 6"></polyline>
                </svg>
                Kembali ke Daftar Return
            </a>
            <div class="bp-retdetail__title-row">
                <div>
                    <h1><?= htmlspecialchars($ret['no_return']) ?></h1>
                    <p>Terkait pesanan
                        <a href="<?= BUYER_URL ?>/orders-detail.php?id=<?= $ret['id_order'] ?>"
                           class="bp-inline-link"><?= htmlspecialchars($ret['no_order']) ?></a>
                    </p>
                </div>
                <div class="bp-retdetail__badges">
                    <?= retStatusBadge($ret['status']) ?>
                    <span class="bp-kat-chip"><?= retKatLabel($ret['alasan_kategori']) ?></span>
                </div>
            </div>
        </div>

        <!-- ===== Progress Bar ===== -->
        <?php if (!$isRejected): ?>
        <div class="bp-card bp-progress-card">
            <div class="bp-progress">
                <?php
                $progressSteps = [
                    ['key' => 'submitted',    'label' => 'Diajukan'],
                    ['key' => 'under_review', 'label' => 'Ditinjau'],
                    ['key' => 'approved',     'label' => 'Disetujui'],
                    ['key' => 'resolved',     'label' => 'Selesai'],
                ];
                foreach ($progressSteps as $i => $ps):
                    $stepNum  = $i + 1;
                    $isDone   = $currentStep > $stepNum;
                    $isActive = $currentStep === $stepNum;
                ?>
                    <div class="bp-progress__step <?= $isDone ? 'is-done' : ($isActive ? 'is-active' : '') ?>">
                        <div class="bp-progress__circle">
                            <?php if ($isDone): ?>
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                     stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"
                                     width="14" height="14">
                                    <polyline points="20 6 9 17 4 12"></polyline>
                                </svg>
                            <?php else: ?>
                                <?= $stepNum ?>
                            <?php endif; ?>
                        </div>
                        <span class="bp-progress__label"><?= $ps['label'] ?></span>
                    </div>
                    <?php if ($i < count($progressSteps) - 1): ?>
                        <div class="bp-progress__line <?= $isDone ? 'is-done' : '' ?>"></div>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>
        </div>
        <?php else: ?>
        <div class="bp-alert bp-alert--error">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                 stroke-linecap="round" stroke-linejoin="round" width="18" height="18">
                <circle cx="12" cy="12" r="10"></circle>
                <line x1="15" y1="9" x2="9" y2="15"></line>
                <line x1="9" y1="9" x2="15" y2="15"></line>
            </svg>
            <div>
                <strong>Pengajuan Return Ditolak</strong>
                <?php if ($ret['respons_admin']): ?>
                    <p style="margin:4px 0 0;"><?= htmlspecialchars($ret['respons_admin']) ?></p>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>

        <div class="bp-retdetail__grid">

            <!-- ===== Kolom Kiri ===== -->
            <div class="bp-retdetail__col-main">

                <!-- Alasan & Detail Return -->
                <div class="bp-card">
                    <div class="bp-card__section-header">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                             stroke-linecap="round" stroke-linejoin="round" width="16" height="16">
                            <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                            <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                        </svg>
                        <h2>Alasan Return</h2>
                    </div>

                    <div class="bp-detail-rows">
                        <div class="bp-detail-row">
                            <span class="bp-detail-row__label">Kategori</span>
                            <span class="bp-kat-chip"><?= retKatLabel($ret['alasan_kategori']) ?></span>
                        </div>
                    </div>

                    <div class="bp-alasan-box">
                        <p><?= nl2br(htmlspecialchars($ret['alasan'])) ?></p>
                    </div>
                </div>

                <!-- Foto Bukti -->
                <?php if (!empty($fotoList)): ?>
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
                    <div class="bp-foto-grid">
                        <?php foreach ($fotoList as $foto): ?>
                            <a href="<?= BUYER_URL ?>/uploads/returns/<?= htmlspecialchars($foto) ?>"
                               target="_blank" class="bp-foto-thumb">
                                <img src="<?= BUYER_URL ?>/uploads/returns/<?= htmlspecialchars($foto) ?>"
                                     alt="Bukti return"
                                     onerror="this.closest('.bp-foto-thumb').classList.add('is-error'); this.style.display='none'">
                                <span class="bp-foto-thumb__fallback">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"
                                         width="22" height="22"><rect x="3" y="3" width="18" height="18" rx="2"/>
                                        <circle cx="8.5" cy="8.5" r="1.5"/>
                                        <polyline points="21 15 16 10 5 21"/>
                                    </svg>
                                    <?= htmlspecialchars($foto) ?>
                                </span>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Respons Admin -->
                <div class="bp-card">
                    <div class="bp-card__section-header">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                             stroke-linecap="round" stroke-linejoin="round" width="16" height="16">
                            <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
                        </svg>
                        <h2>Respons Admin</h2>
                    </div>

                    <?php if ($ret['respons_admin']): ?>
                        <div class="bp-respons-box">
                            <p><?= nl2br(htmlspecialchars($ret['respons_admin'])) ?></p>
                        </div>
                    <?php else: ?>
                        <div class="bp-respons-waiting">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"
                                 stroke-linecap="round" stroke-linejoin="round" width="32" height="32">
                                <circle cx="12" cy="12" r="10"></circle>
                                <polyline points="12 6 12 12 16 14"></polyline>
                            </svg>
                            <p>Menunggu respons dari admin.<br>Tim kami akan segera meninjau pengajuan Anda.</p>
                        </div>
                    <?php endif; ?>
                </div>

            </div><!-- /col-main -->

            <!-- ===== Kolom Kanan ===== -->
            <div class="bp-retdetail__col-side">

                <!-- Info Return -->
                <div class="bp-card bp-info-card">
                    <div class="bp-card__section-header">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                             stroke-linecap="round" stroke-linejoin="round" width="16" height="16">
                            <circle cx="12" cy="12" r="10"></circle>
                            <line x1="12" y1="8" x2="12" y2="12"></line>
                            <line x1="12" y1="16" x2="12.01" y2="16"></line>
                        </svg>
                        <h2>Info Return</h2>
                    </div>
                    <div class="bp-info-rows">
                        <div class="bp-info-row">
                            <span class="bp-info-row__label">No. Return</span>
                            <span class="bp-info-row__value mono"><?= htmlspecialchars($ret['no_return']) ?></span>
                        </div>
                        <div class="bp-info-row">
                            <span class="bp-info-row__label">Status</span>
                            <?= retStatusBadge($ret['status']) ?>
                        </div>
                        <div class="bp-info-row">
                            <span class="bp-info-row__label">Kategori</span>
                            <span class="bp-info-row__value"><?= retKatLabel($ret['alasan_kategori']) ?></span>
                        </div>
                        <div class="bp-info-row">
                            <span class="bp-info-row__label">Foto Bukti</span>
                            <span class="bp-info-row__value"><?= count($fotoList) ?> file</span>
                        </div>
                    </div>
                </div>

                <!-- Info Pesanan Terkait -->
                <div class="bp-card bp-info-card">
                    <div class="bp-card__section-header">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                             stroke-linecap="round" stroke-linejoin="round" width="16" height="16">
                            <path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"></path>
                            <line x1="3" y1="6" x2="21" y2="6"></line>
                            <path d="M16 10a4 4 0 0 1-8 0"></path>
                        </svg>
                        <h2>Pesanan Terkait</h2>
                    </div>
                    <div class="bp-info-rows">
                        <div class="bp-info-row">
                            <span class="bp-info-row__label">No. Order</span>
                            <a href="<?= BUYER_URL ?>/orders-detail.php?id=<?= $ret['id_order'] ?>"
                               class="bp-info-row__value mono bp-link-small">
                                <?= htmlspecialchars($ret['no_order']) ?>
                            </a>
                        </div>
                        <div class="bp-info-row">
                            <span class="bp-info-row__label">Produk</span>
                            <span class="bp-info-row__value"><?= htmlspecialchars($ret['jenis_benang']) ?></span>
                        </div>
                        <?php if ($ret['ukuran_benang']): ?>
                        <div class="bp-info-row">
                            <span class="bp-info-row__label">Ukuran</span>
                            <span class="bp-info-row__value"><?= htmlspecialchars($ret['ukuran_benang']) ?></span>
                        </div>
                        <?php endif; ?>
                        <?php if ($ret['kode_warna']): ?>
                        <div class="bp-info-row">
                            <span class="bp-info-row__label">Kode Warna</span>
                            <span class="bp-info-row__value">
                                <span class="bp-color-code"><?= htmlspecialchars($ret['kode_warna']) ?></span>
                                <?= htmlspecialchars($ret['nama_warna'] ?? '') ?>
                            </span>
                        </div>
                        <?php endif; ?>
                        <div class="bp-info-row">
                            <span class="bp-info-row__label">Qty</span>
                            <span class="bp-info-row__value"><?= number_format($ret['qty'], 0, ',', '.') ?> KG</span>
                        </div>
                        <div class="bp-info-row">
                            <span class="bp-info-row__label">Tgl Pesanan</span>
                            <span class="bp-info-row__value"><?= date('d M Y', strtotime($ret['tanggal_order'])) ?></span>
                        </div>
                        <div class="bp-info-row">
                            <span class="bp-info-row__label">Status Order</span>
                            <?= orderStatusBadge($ret['order_status']) ?>
                        </div>
                    </div>
                </div>

                <!-- Aksi -->
                <div class="bp-side-actions">
                    <a href="<?= BUYER_URL ?>/orders-detail.php?id=<?= $ret['id_order'] ?>"
                       class="bp-btn-secondary bp-btn-full">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                             stroke-linecap="round" stroke-linejoin="round" width="14" height="14">
                            <path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"></path>
                            <line x1="3" y1="6" x2="21" y2="6"></line>
                        </svg>
                        Lihat Detail Pesanan
                    </a>
                    <a href="<?= BUYER_URL ?>/returns.php" class="bp-btn-ghost bp-btn-full">
                        Kembali ke Daftar Return
                    </a>
                </div>

            </div><!-- /col-side -->

        </div><!-- /grid -->

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

    /* ===== Layout ===== */
    .bp-retdetail {
        padding: 24px;
        max-width: 1040px;
        margin: 0 auto;
        display: flex;
        flex-direction: column;
        gap: 18px;
    }

    /* Back */
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

    /* Heading */
    .bp-retdetail__heading { display: flex; flex-direction: column; gap: 10px; }
    .bp-retdetail__title-row {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 12px;
    }
    .bp-retdetail__title-row h1 { margin: 0 0 4px; font-size: 22px; font-weight: 700; }
    .bp-retdetail__title-row p  { margin: 0; font-size: 13.5px; color: #6b7280; }
    .bp-retdetail__badges { display: flex; align-items: center; gap: 8px; flex-wrap: wrap; }

    .bp-inline-link { color: #2563eb; text-decoration: none; font-weight: 600; font-family: 'SF Mono','Fira Code',monospace; font-size: 13px; }
    .bp-inline-link:hover { text-decoration: underline; }

    /* Alert */
    .bp-alert {
        display: flex;
        align-items: flex-start;
        gap: 12px;
        padding: 14px 18px;
        border-radius: 12px;
        font-size: 13.5px;
        line-height: 1.5;
    }
    .bp-alert--error { background: #fef2f2; color: #b91c1c; }
    .bp-alert svg { flex-shrink: 0; margin-top: 2px; }
    .bp-alert p { margin: 0; }

    /* ===== Progress ===== */
    .bp-progress-card { padding: 20px 24px; }
    .bp-progress {
        display: flex;
        align-items: center;
        gap: 0;
    }
    .bp-progress__step {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 6px;
        flex-shrink: 0;
    }
    .bp-progress__circle {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        border: 2px solid #d1d5db;
        background: #fff;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 13px;
        font-weight: 700;
        color: #9ca3af;
        transition: all 0.2s;
    }
    .bp-progress__step.is-done .bp-progress__circle {
        background: #2563eb;
        border-color: #2563eb;
        color: #fff;
    }
    .bp-progress__step.is-active .bp-progress__circle {
        background: #fff;
        border-color: #2563eb;
        color: #2563eb;
        box-shadow: 0 0 0 4px rgba(37,99,235,.15);
    }
    .bp-progress__label {
        font-size: 11.5px;
        font-weight: 600;
        color: #9ca3af;
        white-space: nowrap;
    }
    .bp-progress__step.is-done .bp-progress__label,
    .bp-progress__step.is-active .bp-progress__label { color: #2563eb; }

    .bp-progress__line {
        flex: 1;
        height: 2px;
        background: #e5e7eb;
        margin: 0 4px;
        margin-bottom: 22px; /* align with circle center */
        transition: background 0.2s;
    }
    .bp-progress__line.is-done { background: #2563eb; }

    /* ===== Grid ===== */
    .bp-retdetail__grid {
        display: grid;
        grid-template-columns: 1fr 300px;
        gap: 18px;
        align-items: start;
    }
    .bp-retdetail__col-main,
    .bp-retdetail__col-side { display: flex; flex-direction: column; gap: 18px; }

    /* ===== Card ===== */
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
        margin-bottom: 16px;
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

    /* Detail rows */
    .bp-detail-rows { margin-bottom: 14px; }
    .bp-detail-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 9px 0;
        border-bottom: 1px solid #f3f4f6;
        gap: 12px;
        font-size: 13.5px;
    }
    .bp-detail-row:last-child { border-bottom: none; }
    .bp-detail-row__label { color: #6b7280; font-weight: 500; flex-shrink: 0; }

    /* Alasan box */
    .bp-alasan-box {
        background: #f9fafb;
        border: 1px solid #e5e7eb;
        border-radius: 10px;
        padding: 14px 16px;
    }
    .bp-alasan-box p { margin: 0; font-size: 14px; color: #374151; line-height: 1.7; }

    /* Foto grid */
    .bp-foto-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(120px, 1fr));
        gap: 10px;
    }
    .bp-foto-thumb {
        display: block;
        border: 1px solid #e5e7eb;
        border-radius: 10px;
        overflow: hidden;
        aspect-ratio: 1;
        background: #f9fafb;
        position: relative;
        text-decoration: none;
        transition: border-color 0.15s;
    }
    .bp-foto-thumb:hover { border-color: #2563eb; }
    .bp-foto-thumb img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        display: block;
    }
    .bp-foto-thumb__fallback {
        display: none;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 6px;
        width: 100%;
        height: 100%;
        font-size: 10px;
        color: #9ca3af;
        text-align: center;
        padding: 8px;
        box-sizing: border-box;
        word-break: break-all;
    }
    .bp-foto-thumb.is-error img { display: none; }
    .bp-foto-thumb.is-error .bp-foto-thumb__fallback { display: flex; }

    /* Respons box */
    .bp-respons-box {
        background: #eff6ff;
        border: 1px solid #bfdbfe;
        border-radius: 10px;
        padding: 14px 16px;
    }
    .bp-respons-box p { margin: 0; font-size: 14px; color: #1e40af; line-height: 1.7; }

    .bp-respons-waiting {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 10px;
        padding: 16px 0 4px;
        color: #9ca3af;
        text-align: center;
    }
    .bp-respons-waiting p { margin: 0; font-size: 13.5px; line-height: 1.6; }

    /* ===== Info card ===== */
    .bp-info-card { padding: 16px 20px; }
    .bp-info-rows { display: flex; flex-direction: column; }
    .bp-info-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 8px 0;
        border-bottom: 1px solid #f3f4f6;
        gap: 10px;
        font-size: 13px;
    }
    .bp-info-row:last-child { border-bottom: none; }
    .bp-info-row__label { color: #6b7280; font-weight: 500; flex-shrink: 0; }
    .bp-info-row__value { font-weight: 600; color: #111827; text-align: right; }
    .mono { font-family: 'SF Mono','Fira Code',monospace; font-size: 12px; }

    .bp-color-code {
        font-family: 'SF Mono','Fira Code',monospace;
        font-size: 11.5px;
        font-weight: 600;
        background: #f3f4f6;
        color: #6b7280;
        padding: 2px 6px;
        border-radius: 4px;
        margin-right: 4px;
    }

    /* ===== Badges ===== */
    .bp-badge {
        display: inline-flex;
        align-items: center;
        font-size: 12px;
        font-weight: 600;
        padding: 4px 10px;
        border-radius: 999px;
        white-space: nowrap;
    }
    .badge-pending    { background: #fffbeb; color: #b45309; }
    .badge-processing { background: #eff6ff; color: #1d4ed8; }
    .badge-shipped    { background: #f0f9ff; color: #0369a1; }
    .badge-done       { background: #f0fdf4; color: #15803d; }
    .badge-cancelled  { background: #fef2f2; color: #b91c1c; }

    .bp-kat-chip {
        display: inline-block;
        padding: 4px 12px;
        border-radius: 99px;
        font-size: 12px;
        font-weight: 600;
        background: #f3f4f6;
        color: #374151;
        white-space: nowrap;
    }

    /* ===== Buttons ===== */
    .bp-btn-secondary {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 7px;
        padding: 10px 16px;
        background: #fff;
        color: #374151;
        border: 1px solid #d1d5db;
        border-radius: 10px;
        font-size: 13px;
        font-weight: 600;
        text-decoration: none;
        cursor: pointer;
        transition: background 0.15s;
        box-sizing: border-box;
    }
    .bp-btn-secondary:hover { background: #f3f4f6; }

    .bp-btn-ghost {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 10px 16px;
        background: transparent;
        color: #6b7280;
        border: none;
        border-radius: 10px;
        font-size: 13px;
        font-weight: 600;
        text-decoration: none;
        cursor: pointer;
        transition: background 0.15s;
        box-sizing: border-box;
    }
    .bp-btn-ghost:hover { background: #f3f4f6; color: #374151; }

    .bp-link-small {
        font-size: 12px;
        color: #2563eb;
        text-decoration: none;
        font-family: 'SF Mono','Fira Code',monospace;
        font-weight: 700;
    }
    .bp-link-small:hover { text-decoration: underline; }

    .bp-btn-full { width: 100%; }

    .bp-side-actions { display: flex; flex-direction: column; gap: 8px; }

    /* ===== Responsive ===== */
    @media (max-width: 768px) {
        .bp-retdetail { padding: 16px; gap: 14px; }
        .bp-retdetail__grid { grid-template-columns: 1fr; }
        .bp-retdetail__col-side { order: -1; }
        .bp-retdetail__title-row { flex-direction: column; align-items: flex-start; }

        .bp-progress { overflow-x: auto; padding-bottom: 4px; }
        .bp-progress__label { font-size: 10.5px; }

        .bp-foto-grid { grid-template-columns: repeat(3, 1fr); }
    }

    @media (max-width: 480px) {
        .bp-foto-grid { grid-template-columns: repeat(2, 1fr); }
    }
</style>

</body>
</html>