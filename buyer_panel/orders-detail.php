<?php
// ============================================================
//  buyer_panel/orders-detail.php
//  Detail pesanan tunggal — info, tracking, return, dokumen.
// ============================================================

define('REQUIRED_ROLE', 'buyer');
require_once __DIR__ . '/../assets/verifyRoleRedirect.php';
require_once __DIR__ . '/partials/config.php';

$idBuyer = (int) ($currentBuyer['id_buyer'] ?? 0);
$pageTitle = 'Detail Pesanan';

// ------------------------------------------------------------
// Ambil ID order dari query string
// ------------------------------------------------------------
$idOrder = (int) ($_GET['id'] ?? 0);
if (!$idOrder) {
    header('Location: ' . BUYER_URL . '/orders.php');
    exit;
}

// ------------------------------------------------------------
// Query order — pastikan milik buyer yang sedang login
// ------------------------------------------------------------
$stmt = $conn->prepare("
    SELECT o.*,
           bp.nama_perusahaan, bp.nama_pic, bp.kode_pelanggan
    FROM orders o
    JOIN buyer_profile bp ON bp.id_buyer = o.id_buyer
    WHERE o.id_order = ? AND o.id_buyer = ?
    LIMIT 1
");
$stmt->bind_param('ii', $idOrder, $idBuyer);
$stmt->execute();
$order = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$order) {
    header('Location: ' . BUYER_URL . '/orders.php?not_found=1');
    exit;
}

// ------------------------------------------------------------
// Tracking history untuk order ini
// ------------------------------------------------------------
$trackingList = [];
$stmtT = $conn->prepare("
    SELECT t.*, u.email AS updated_by_email
    FROM tracking t
    LEFT JOIN users u ON u.id_user = t.updated_by
    WHERE t.id_order = ?
    ORDER BY t.tanggal ASC
");
$stmtT->bind_param('i', $idOrder);
$stmtT->execute();
$resT = $stmtT->get_result();
while ($row = $resT->fetch_assoc()) $trackingList[] = $row;
$stmtT->close();

// ------------------------------------------------------------
// Return history untuk order ini (jika ada)
// ------------------------------------------------------------
$returnList = [];
$stmtR = $conn->prepare("
    SELECT * FROM order_returns
    WHERE id_order = ?
    ORDER BY id_return ASC
");
$stmtR->bind_param('i', $idOrder);
$stmtR->execute();
$resR = $stmtR->get_result();
while ($row = $resR->fetch_assoc()) $returnList[] = $row;
$stmtR->close();

// ------------------------------------------------------------
// Helper: label & warna badge status order
// ------------------------------------------------------------
function orderStatusBadge(string $status): string {
    $map = [
        'pending'    => ['label' => 'Menunggu',      'class' => 'badge-pending'],
        'processing' => ['label' => 'Diproses',       'class' => 'badge-processing'],
        'shipped'    => ['label' => 'Dikirim',        'class' => 'badge-shipped'],
        'done'       => ['label' => 'Selesai',        'class' => 'badge-done'],
        'cancelled'  => ['label' => 'Dibatalkan',     'class' => 'badge-cancelled'],
    ];
    $d = $map[$status] ?? ['label' => ucfirst($status), 'class' => 'badge-pending'];
    return '<span class="bp-badge ' . $d['class'] . '">' . $d['label'] . '</span>';
}

// Helper: label kategori return
function returnKategoriLabel(string $k): string {
    $map = [
        'deviasi_warna'      => 'Deviasi Warna',
        'kualitas'           => 'Kualitas',
        'barang_rusak'       => 'Barang Rusak',
        'spesifikasi_salah'  => 'Spesifikasi Salah',
        'lainnya'            => 'Lainnya',
    ];
    return $map[$k] ?? ucfirst($k);
}

// Helper: badge status return
function returnStatusBadge(string $s): string {
    $map = [
        'submitted'    => ['label' => 'Diajukan',       'class' => 'badge-pending'],
        'under_review' => ['label' => 'Ditinjau',       'class' => 'badge-processing'],
        'approved'     => ['label' => 'Disetujui',      'class' => 'badge-shipped'],
        'resolved'     => ['label' => 'Selesai',        'class' => 'badge-done'],
        'rejected'     => ['label' => 'Ditolak',        'class' => 'badge-cancelled'],
    ];
    $d = $map[$s] ?? ['label' => ucfirst($s), 'class' => 'badge-pending'];
    return '<span class="bp-badge ' . $d['class'] . '">' . $d['label'] . '</span>';
}

$subtotal = $order['qty'] * $order['harga_benang'];
$canReturn = in_array($order['status'], ['shipped', 'done']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($order['no_order']) ?> — Detail Pesanan</title>
</head>
<body>

<?php include __DIR__ . '/partials/_header.php'; ?>
<?php include __DIR__ . '/partials/_sidebar.php'; ?>
<?php include __DIR__ . '/partials/overdue-banner.php'; ?>

<div class="bp-content">
    <main class="bp-detail">

        <!-- ===== Heading ===== -->
        <div class="bp-detail__heading">
            <a href="<?= BUYER_URL ?>/orders.php" class="bp-back">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                     stroke-linecap="round" stroke-linejoin="round" width="16" height="16">
                    <polyline points="15 18 9 12 15 6"></polyline>
                </svg>
                Kembali ke Daftar Pesanan
            </a>
            <div class="bp-detail__title-row">
                <div>
                    <h1><?= htmlspecialchars($order['no_order']) ?></h1>
                    <p>Dibuat pada <?= date('d M Y', strtotime($order['tanggal'])) ?></p>
                </div>
                <div class="bp-detail__title-actions">
                    <?= orderStatusBadge($order['status']) ?>
                    <?php if ($canReturn): ?>
                        <a href="<?= BUYER_URL ?>/returns-new.php?id_order=<?= $idOrder ?>"
                           class="bp-btn-return">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                 stroke-linecap="round" stroke-linejoin="round" width="15" height="15">
                                <polyline points="1 4 1 10 7 10"></polyline>
                                <path d="M3.51 15a9 9 0 1 0 .49-3.51"></path>
                            </svg>
                            Ajukan Return
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="bp-detail__grid">

            <!-- ===== Kolom kiri ===== -->
            <div class="bp-detail__col-main">

                <!-- Produk -->
                <div class="bp-card">
                    <div class="bp-card__section-header">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                             stroke-linecap="round" stroke-linejoin="round" width="16" height="16">
                            <path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"></path>
                            <line x1="3" y1="6" x2="21" y2="6"></line>
                            <path d="M16 10a4 4 0 0 1-8 0"></path>
                        </svg>
                        <h2>Detail Produk</h2>
                    </div>

                    <div class="bp-detail-rows">
                        <div class="bp-detail-row">
                            <span class="bp-detail-row__label">Jenis Benang</span>
                            <span class="bp-detail-row__value bp-strong"><?= htmlspecialchars($order['jenis_benang']) ?></span>
                        </div>
                        <div class="bp-detail-row">
                            <span class="bp-detail-row__label">Ukuran / Denier</span>
                            <span class="bp-detail-row__value"><?= htmlspecialchars($order['ukuran_benang'] ?: '—') ?></span>
                        </div>
                        <div class="bp-detail-row">
                            <span class="bp-detail-row__label">Kode Warna</span>
                            <span class="bp-detail-row__value">
                                <?php if ($order['kode_warna']): ?>
                                    <span class="bp-color-code"><?= htmlspecialchars($order['kode_warna']) ?></span>
                                <?php else: ?>—<?php endif; ?>
                            </span>
                        </div>
                        <div class="bp-detail-row">
                            <span class="bp-detail-row__label">Nama Warna</span>
                            <span class="bp-detail-row__value"><?= htmlspecialchars($order['nama_warna'] ?: '—') ?></span>
                        </div>
                    </div>
                </div>

                <!-- Jumlah & Harga -->
                <div class="bp-card">
                    <div class="bp-card__section-header">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                             stroke-linecap="round" stroke-linejoin="round" width="16" height="16">
                            <line x1="12" y1="1" x2="12" y2="23"></line>
                            <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path>
                        </svg>
                        <h2>Jumlah & Harga</h2>
                    </div>

                    <div class="bp-detail-rows">
                        <div class="bp-detail-row">
                            <span class="bp-detail-row__label">Jumlah</span>
                            <span class="bp-detail-row__value"><?= number_format($order['qty'], 0, ',', '.') ?> KG</span>
                        </div>
                        <div class="bp-detail-row">
                            <span class="bp-detail-row__label">Harga per kg</span>
                            <span class="bp-detail-row__value">Rp <?= number_format($order['harga_benang'], 0, ',', '.') ?></span>
                        </div>
                    </div>

                    <!-- Subtotal highlight -->
                    <div class="bp-subtotal">
                        <span class="bp-subtotal__label">Total Estimasi Pesanan</span>
                        <span class="bp-subtotal__value">Rp <?= number_format($subtotal, 0, ',', '.') ?></span>
                    </div>
                </div>

                <!-- Catatan -->
                <?php if ($order['catatan']): ?>
                <div class="bp-card">
                    <div class="bp-card__section-header">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                             stroke-linecap="round" stroke-linejoin="round" width="16" height="16">
                            <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                            <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                        </svg>
                        <h2>Catatan Pesanan</h2>
                    </div>
                    <p class="bp-note-text"><?= nl2br(htmlspecialchars($order['catatan'])) ?></p>
                </div>
                <?php endif; ?>

                <!-- ===== Tracking Timeline ===== -->
                <div class="bp-card">
                    <div class="bp-card__section-header">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                             stroke-linecap="round" stroke-linejoin="round" width="16" height="16">
                            <circle cx="12" cy="12" r="10"></circle>
                            <polyline points="12 6 12 12 16 14"></polyline>
                        </svg>
                        <h2>Riwayat Tracking</h2>
                    </div>

                    <?php if (empty($trackingList)): ?>
                        <p class="bp-empty-state">Belum ada pembaruan status untuk pesanan ini.</p>
                    <?php else: ?>
                        <div class="bp-timeline">
                            <?php foreach ($trackingList as $i => $trk): ?>
                                <div class="bp-timeline__item <?= $i === count($trackingList) - 1 ? 'is-latest' : '' ?>">
                                    <div class="bp-timeline__dot"></div>
                                    <div class="bp-timeline__content">
                                        <div class="bp-timeline__status"><?= htmlspecialchars($trk['status']) ?></div>
                                        <?php if ($trk['keterangan']): ?>
                                            <div class="bp-timeline__desc"><?= htmlspecialchars($trk['keterangan']) ?></div>
                                        <?php endif; ?>
                                        <div class="bp-timeline__meta">
                                            <?= date('d M Y, H:i', strtotime($trk['tanggal'])) ?>
                                            <?php if ($trk['updated_by_email']): ?>
                                                · <?= htmlspecialchars($trk['updated_by_email']) ?>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- ===== Return History ===== -->
                <?php if (!empty($returnList)): ?>
                <div class="bp-card">
                    <div class="bp-card__section-header">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                             stroke-linecap="round" stroke-linejoin="round" width="16" height="16">
                            <polyline points="1 4 1 10 7 10"></polyline>
                            <path d="M3.51 15a9 9 0 1 0 .49-3.51"></path>
                        </svg>
                        <h2>Riwayat Return</h2>
                    </div>

                    <div class="bp-return-list">
                        <?php foreach ($returnList as $ret): ?>
                            <div class="bp-return-item">
                                <div class="bp-return-item__header">
                                    <div>
                                        <span class="bp-return-item__no"><?= htmlspecialchars($ret['no_return']) ?></span>
                                        <span class="bp-return-item__kat"><?= returnKategoriLabel($ret['alasan_kategori']) ?></span>
                                    </div>
                                    <?= returnStatusBadge($ret['status']) ?>
                                </div>
                                <p class="bp-return-item__alasan"><?= htmlspecialchars($ret['alasan']) ?></p>
                                <?php if ($ret['respons_admin']): ?>
                                    <div class="bp-return-item__respons">
                                        <span class="bp-return-item__respons-label">Respons Admin</span>
                                        <p><?= htmlspecialchars($ret['respons_admin']) ?></p>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>

            </div><!-- /col-main -->

            <!-- ===== Kolom kanan (sidebar info) ===== -->
            <div class="bp-detail__col-side">

                <!-- Ringkasan -->
                <div class="bp-card bp-info-card">
                    <div class="bp-card__section-header">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                             stroke-linecap="round" stroke-linejoin="round" width="16" height="16">
                            <circle cx="12" cy="12" r="10"></circle>
                            <line x1="12" y1="8" x2="12" y2="12"></line>
                            <line x1="12" y1="16" x2="12.01" y2="16"></line>
                        </svg>
                        <h2>Ringkasan</h2>
                    </div>
                    <div class="bp-info-rows">
                        <div class="bp-info-row">
                            <span class="bp-info-row__label">No. Order</span>
                            <span class="bp-info-row__value mono"><?= htmlspecialchars($order['no_order']) ?></span>
                        </div>
                        <div class="bp-info-row">
                            <span class="bp-info-row__label">Tanggal</span>
                            <span class="bp-info-row__value"><?= date('d M Y', strtotime($order['tanggal'])) ?></span>
                        </div>
                        <div class="bp-info-row">
                            <span class="bp-info-row__label">Status</span>
                            <span><?= orderStatusBadge($order['status']) ?></span>
                        </div>
                        <div class="bp-info-row">
                            <span class="bp-info-row__label">Tracking</span>
                            <span class="bp-info-row__value"><?= count($trackingList) ?> update</span>
                        </div>
                        <div class="bp-info-row">
                            <span class="bp-info-row__label">Return</span>
                            <span class="bp-info-row__value"><?= count($returnList) ?> pengajuan</span>
                        </div>
                    </div>
                </div>

                <!-- Profil Buyer -->
                <div class="bp-card bp-info-card">
                    <div class="bp-card__section-header">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                             stroke-linecap="round" stroke-linejoin="round" width="16" height="16">
                            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                            <circle cx="12" cy="7" r="4"></circle>
                        </svg>
                        <h2>Informasi Pembeli</h2>
                    </div>
                    <div class="bp-info-rows">
                        <div class="bp-info-row">
                            <span class="bp-info-row__label">Perusahaan</span>
                            <span class="bp-info-row__value"><?= htmlspecialchars($order['nama_perusahaan']) ?></span>
                        </div>
                        <div class="bp-info-row">
                            <span class="bp-info-row__label">PIC</span>
                            <span class="bp-info-row__value"><?= htmlspecialchars($order['nama_pic']) ?></span>
                        </div>
                        <?php if ($order['kode_pelanggan']): ?>
                        <div class="bp-info-row">
                            <span class="bp-info-row__label">Kode Pelanggan</span>
                            <span class="bp-info-row__value mono"><?= htmlspecialchars($order['kode_pelanggan']) ?></span>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Aksi -->
                <div class="bp-side-actions">
                    <a href="<?= BUYER_URL ?>/orders.php" class="bp-btn-secondary bp-btn-full">
                        Kembali ke Daftar
                    </a>
                    <?php if ($canReturn): ?>
                        <a href="<?= BUYER_URL ?>/returns-new.php?id_order=<?= $idOrder ?>"
                           class="bp-btn-return bp-btn-full">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                 stroke-linecap="round" stroke-linejoin="round" width="15" height="15">
                                <polyline points="1 4 1 10 7 10"></polyline>
                                <path d="M3.51 15a9 9 0 1 0 .49-3.51"></path>
                            </svg>
                            Ajukan Return
                        </a>
                    <?php endif; ?>
                    <?php if ($order['status'] === 'pending'): ?>
                        <form method="POST" action="<?= BUYER_URL ?>/orders-cancel.php"
                              onsubmit="return confirm('Yakin ingin membatalkan pesanan ini?')">
                            <input type="hidden" name="id_order" value="<?= $idOrder ?>">
                            <button type="submit" class="bp-btn-cancel bp-btn-full">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                     stroke-linecap="round" stroke-linejoin="round" width="15" height="15">
                                    <circle cx="12" cy="12" r="10"></circle>
                                    <line x1="15" y1="9" x2="9" y2="15"></line>
                                    <line x1="9" y1="9" x2="15" y2="15"></line>
                                </svg>
                                Batalkan Pesanan
                            </button>
                        </form>
                    <?php endif; ?>
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
    .bp-detail {
        padding: 24px;
        max-width: 1040px;
        margin: 0 auto;
        display: flex;
        flex-direction: column;
        gap: 18px;
    }

    /* Heading */
    .bp-detail__heading { display: flex; flex-direction: column; gap: 10px; }
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

    .bp-detail__title-row {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 12px;
    }
    .bp-detail__title-row h1 { margin: 0 0 4px; font-size: 22px; font-weight: 700; }
    .bp-detail__title-row p  { margin: 0; font-size: 13.5px; color: #6b7280; }

    .bp-detail__title-actions {
        display: flex;
        align-items: center;
        gap: 10px;
        flex-wrap: wrap;
    }

    /* Grid */
    .bp-detail__grid {
        display: grid;
        grid-template-columns: 1fr 300px;
        gap: 18px;
        align-items: start;
    }
    .bp-detail__col-main,
    .bp-detail__col-side {
        display: flex;
        flex-direction: column;
        gap: 18px;
    }

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
    .bp-detail-rows { display: flex; flex-direction: column; gap: 0; }
    .bp-detail-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 10px 0;
        border-bottom: 1px solid #f3f4f6;
        gap: 12px;
    }
    .bp-detail-row:last-child { border-bottom: none; padding-bottom: 0; }
    .bp-detail-row__label { font-size: 13px; color: #6b7280; font-weight: 500; flex-shrink: 0; }
    .bp-detail-row__value { font-size: 13.5px; color: #111827; text-align: right; }
    .bp-strong { font-weight: 700; }

    .bp-color-code {
        font-family: 'SF Mono', 'Fira Code', 'Consolas', monospace;
        font-size: 12px;
        font-weight: 600;
        color: #6b7280;
        background: #f3f4f6;
        padding: 2px 8px;
        border-radius: 5px;
    }

    /* Subtotal box */
    .bp-subtotal {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-top: 14px;
        padding: 14px 16px;
        background: #eff6ff;
        border-radius: 10px;
    }
    .bp-subtotal__label { font-size: 13px; font-weight: 600; color: #1d4ed8; }
    .bp-subtotal__value { font-size: 20px; font-weight: 700; color: #1d4ed8; }

    /* Catatan */
    .bp-note-text {
        margin: 0;
        font-size: 13.5px;
        color: #374151;
        line-height: 1.6;
        background: #f9fafb;
        border-radius: 8px;
        padding: 12px 14px;
        border: 1px solid #e5e7eb;
    }

    /* ===== Timeline ===== */
    .bp-timeline {
        position: relative;
        padding-left: 24px;
    }
    .bp-timeline::before {
        content: '';
        position: absolute;
        left: 7px;
        top: 10px;
        bottom: 10px;
        width: 2px;
        background: #e5e7eb;
    }
    .bp-timeline__item {
        position: relative;
        padding-bottom: 20px;
    }
    .bp-timeline__item:last-child { padding-bottom: 0; }
    .bp-timeline__dot {
        position: absolute;
        left: -21px;
        top: 4px;
        width: 14px;
        height: 14px;
        border-radius: 50%;
        background: #d1d5db;
        border: 2px solid #fff;
        box-shadow: 0 0 0 1px #d1d5db;
    }
    .bp-timeline__item.is-latest .bp-timeline__dot {
        background: #2563eb;
        box-shadow: 0 0 0 1px #2563eb, 0 0 0 4px rgba(37, 99, 235, .15);
    }
    .bp-timeline__status {
        font-size: 14px;
        font-weight: 700;
        color: #111827;
        margin-bottom: 3px;
    }
    .bp-timeline__item.is-latest .bp-timeline__status { color: #1d4ed8; }
    .bp-timeline__desc {
        font-size: 13px;
        color: #374151;
        margin-bottom: 4px;
        line-height: 1.5;
    }
    .bp-timeline__meta {
        font-size: 11.5px;
        color: #9ca3af;
    }

    .bp-empty-state {
        margin: 0;
        font-size: 13.5px;
        color: #9ca3af;
        text-align: center;
        padding: 12px 0 4px;
    }

    /* ===== Return list ===== */
    .bp-return-list { display: flex; flex-direction: column; gap: 12px; }
    .bp-return-item {
        border: 1px solid #e5e7eb;
        border-radius: 10px;
        padding: 14px 16px;
        background: #fafafa;
    }
    .bp-return-item__header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        margin-bottom: 8px;
    }
    .bp-return-item__no {
        font-size: 13px;
        font-weight: 700;
        color: #111827;
        font-family: 'SF Mono', 'Fira Code', monospace;
        margin-right: 8px;
    }
    .bp-return-item__kat {
        font-size: 11.5px;
        background: #f3f4f6;
        color: #6b7280;
        padding: 2px 8px;
        border-radius: 99px;
        font-weight: 600;
    }
    .bp-return-item__alasan {
        margin: 0 0 8px;
        font-size: 13px;
        color: #374151;
        line-height: 1.5;
    }
    .bp-return-item__respons {
        background: #eff6ff;
        border-radius: 8px;
        padding: 10px 12px;
    }
    .bp-return-item__respons-label {
        display: block;
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        color: #1d4ed8;
        margin-bottom: 4px;
    }
    .bp-return-item__respons p {
        margin: 0;
        font-size: 13px;
        color: #1e40af;
        line-height: 1.5;
    }

    /* ===== Info card sidebar ===== */
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
    .mono { font-family: 'SF Mono', 'Fira Code', monospace; font-size: 12px; }

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

    /* ===== Buttons ===== */
    .bp-btn-return {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        padding: 9px 16px;
        background: #fffbeb;
        color: #b45309;
        border: 1px solid #fcd34d;
        border-radius: 10px;
        font-size: 13px;
        font-weight: 600;
        cursor: pointer;
        text-decoration: none;
        transition: background 0.15s;
    }
    .bp-btn-return:hover { background: #fef3c7; }

    .bp-btn-secondary {
        display: inline-flex;
        align-items: center;
        justify-content: center;
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
    }
    .bp-btn-secondary:hover { background: #f3f4f6; }

    .bp-btn-cancel {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 7px;
        padding: 10px 16px;
        background: #fff;
        color: #dc2626;
        border: 1px solid #fca5a5;
        border-radius: 10px;
        font-size: 13px;
        font-weight: 600;
        cursor: pointer;
        transition: background 0.15s;
        width: 100%;
    }
    .bp-btn-cancel:hover { background: #fef2f2; }

    .bp-btn-full { width: 100%; box-sizing: border-box; justify-content: center; }

    /* Side actions */
    .bp-side-actions { display: flex; flex-direction: column; gap: 10px; }

    /* ===== Responsive ===== */
    @media (max-width: 768px) {
        .bp-detail { padding: 16px; gap: 14px; }
        .bp-detail__grid { grid-template-columns: 1fr; }
        .bp-detail__col-side { order: -1; }
        .bp-detail__title-row { flex-direction: column; align-items: flex-start; gap: 8px; }
        .bp-subtotal { flex-direction: column; align-items: flex-start; gap: 4px; }
        .bp-subtotal__value { font-size: 18px; }
    }
</style>

</body>
</html>