<?php
// ============================================================
//  buyer_panel/dashboard.php
//  Dashboard utama untuk panel Buyer.
// ============================================================

require_once __DIR__ . '/../assets/verifyRoleRedirect.php';
require_once __DIR__ . '/partials/config.php';

$idBuyer = (int) ($currentBuyer['id_buyer'] ?? 0);

// ------------------------------------------------------------
// 1. Ringkasan status order
// ------------------------------------------------------------
$orderSummary = [
    'pending'    => 0,
    'processing' => 0,
    'shipped'    => 0,
    'done'       => 0,
    'cancelled'  => 0,
];
$totalOrder = 0;

$stmt = $conn->prepare("
    SELECT status, COUNT(*) AS jumlah
    FROM orders
    WHERE id_buyer = ?
    GROUP BY status
");
$stmt->bind_param('i', $idBuyer);
$stmt->execute();
$res = $stmt->get_result();
while ($row = $res->fetch_assoc()) {
    $orderSummary[$row['status']] = (int) $row['jumlah'];
    $totalOrder += (int) $row['jumlah'];
}
$stmt->close();

// ------------------------------------------------------------
// 2. Ringkasan sample request
// ------------------------------------------------------------
$sampleSummary = [
    'pending'        => 0,
    'waiting_result' => 0,
    'result_ready'   => 0,
    'approved'       => 0,
    'rejected'       => 0,
    'revision'       => 0,
];
$totalSample = 0;

$stmt = $conn->prepare("
    SELECT status, COUNT(*) AS jumlah
    FROM sample_requests
    WHERE id_buyer = ?
    GROUP BY status
");
$stmt->bind_param('i', $idBuyer);
$stmt->execute();
$res = $stmt->get_result();
while ($row = $res->fetch_assoc()) {
    $sampleSummary[$row['status']] = (int) $row['jumlah'];
    $totalSample += (int) $row['jumlah'];
}
$stmt->close();

// ------------------------------------------------------------
// 3. Statistik return / komplain
// ------------------------------------------------------------
$returnSummary = [
    'submitted'    => 0,
    'under_review' => 0,
    'approved'     => 0,
    'resolved'     => 0,
    'rejected'     => 0,
];
$totalReturn = 0;

$stmt = $conn->prepare("
    SELECT r.status, COUNT(*) AS jumlah
    FROM order_returns r
    INNER JOIN orders o ON o.id_order = r.id_order
    WHERE o.id_buyer = ?
    GROUP BY r.status
");
$stmt->bind_param('i', $idBuyer);
$stmt->execute();
$res = $stmt->get_result();
while ($row = $res->fetch_assoc()) {
    $returnSummary[$row['status']] = (int) $row['jumlah'];
    $totalReturn += (int) $row['jumlah'];
}
$stmt->close();

// ------------------------------------------------------------
// 4. Daftar order terbaru (5 terakhir)
// ------------------------------------------------------------
$recentOrders = [];
$stmt = $conn->prepare("
    SELECT id_order, no_order, jenis_benang, nama_warna, qty, harga_benang, tanggal, status
    FROM orders
    WHERE id_buyer = ?
    ORDER BY tanggal DESC, id_order DESC
    LIMIT 5
");
$stmt->bind_param('i', $idBuyer);
$stmt->execute();
$res = $stmt->get_result();
while ($row = $res->fetch_assoc()) {
    $recentOrders[] = $row;
}
$stmt->close();

// ------------------------------------------------------------
// Label & warna badge status order
// ------------------------------------------------------------
$orderStatusLabel = [
    'pending'    => ['label' => 'Menunggu', 'class' => 'badge-pending'],
    'processing' => ['label' => 'Diproses', 'class' => 'badge-info'],
    'shipped'    => ['label' => 'Dikirim', 'class' => 'badge-info'],
    'done'       => ['label' => 'Selesai', 'class' => 'badge-verified'],
    'cancelled'  => ['label' => 'Dibatalkan', 'class' => 'badge-rejected'],
];

$pageTitle = 'Dashboard';
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
    <main class="bp-dash">

        <!-- ============ Page heading ============ -->
        <div class="bp-dash__heading">
            <h1>Dashboard</h1>
            <p>Selamat datang kembali, <strong><?= htmlspecialchars($currentBuyer['nama_pic'] ?? '') ?></strong>.</p>
        </div>

        <!-- ============ Kartu profil & status verifikasi ============ -->
        <section class="bp-card bp-profile-card">
            <div class="bp-profile-card__info">
                <div class="bp-profile-card__icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="24" height="24">
                        <path d="M3 21h18"></path>
                        <path d="M5 21V7l8-4v18"></path>
                        <path d="M19 21V11l-6-4"></path>
                        <line x1="9" y1="9" x2="9" y2="9.01"></line>
                        <line x1="9" y1="13" x2="9" y2="13.01"></line>
                        <line x1="9" y1="17" x2="9" y2="17.01"></line>
                    </svg>
                </div>
                <div>
                    <div class="bp-profile-card__name"><?= htmlspecialchars($currentBuyer['nama_perusahaan'] ?? '-') ?></div>
                    <div class="bp-profile-card__meta">
                        PIC: <?= htmlspecialchars($currentBuyer['nama_pic'] ?? '-') ?>
                        &middot; Tenor: <?= htmlspecialchars((string) ($currentBuyer['tenor_hari'] ?? 30)) ?> hari
                    </div>
                </div>
            </div>

            <?php
            $vStatus = $currentBuyer['status_verifikasi'] ?? 'pending';
            $vBadge = [
                'approved' => ['label' => 'Terverifikasi', 'class' => 'badge-verified'],
                'pending'  => ['label' => 'Menunggu Verifikasi', 'class' => 'badge-pending'],
                'rejected' => ['label' => 'Ditolak', 'class' => 'badge-rejected'],
                'blocked'  => ['label' => 'Diblokir', 'class' => 'badge-rejected'],
            ][$vStatus] ?? ['label' => 'Menunggu Verifikasi', 'class' => 'badge-pending'];
            ?>
            <span class="bp-badge <?= $vBadge['class'] ?>"><?= htmlspecialchars($vBadge['label']) ?></span>
        </section>

        <?php if ($vStatus === 'pending'): ?>
            <div class="bp-alert bp-alert--info">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="18" height="18">
                    <circle cx="12" cy="12" r="10"></circle>
                    <line x1="12" y1="16" x2="12" y2="12"></line>
                    <line x1="12" y1="8" x2="12.01" y2="8"></line>
                </svg>
                <span>Akun Anda sedang menunggu proses verifikasi oleh admin. Beberapa fitur mungkin masih terbatas.</span>
            </div>
        <?php elseif ($vStatus === 'rejected'): ?>
            <div class="bp-alert bp-alert--danger">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="18" height="18">
                    <circle cx="12" cy="12" r="10"></circle>
                    <line x1="15" y1="9" x2="9" y2="15"></line>
                    <line x1="9" y1="9" x2="15" y2="15"></line>
                </svg>
                <span>Verifikasi akun Anda ditolak. Silakan hubungi admin untuk informasi lebih lanjut.</span>
            </div>
        <?php elseif ($vStatus === 'blocked'): ?>
            <div class="bp-alert bp-alert--danger">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="18" height="18">
                    <circle cx="12" cy="12" r="10"></circle>
                    <line x1="4.93" y1="4.93" x2="19.07" y2="19.07"></line>
                </svg>
                <span>Akun Anda saat ini diblokir. Silakan hubungi admin untuk informasi lebih lanjut.</span>
            </div>
        <?php endif; ?>

        <!-- ============ Ringkasan kartu statistik ============ -->
        <section class="bp-stats-grid">
            <!-- Total Order -->
            <div class="bp-card bp-stat">
                <div class="bp-stat__icon bp-stat__icon--blue">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="20" height="20">
                        <path d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.3 2.3c-.6.6-.2 1.7.7 1.7H17"></path>
                        <circle cx="9" cy="20" r="1.5"></circle>
                        <circle cx="17" cy="20" r="1.5"></circle>
                    </svg>
                </div>
                <div class="bp-stat__value"><?= $totalOrder ?></div>
                <div class="bp-stat__label">Total Order</div>
                <div class="bp-stat__breakdown">
                    <span><?= $orderSummary['pending'] ?> menunggu</span>
                    <span><?= $orderSummary['processing'] ?> diproses</span>
                    <span><?= $orderSummary['shipped'] ?> dikirim</span>
                    <span><?= $orderSummary['done'] ?> selesai</span>
                </div>
            </div>

            <!-- Total Sample Request -->
            <div class="bp-card bp-stat">
                <div class="bp-stat__icon bp-stat__icon--purple">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="20" height="20">
                        <path d="M9 2v6L4 19a1 1 0 0 0 1 1h14a1 1 0 0 0 1-1L15 8V2"></path>
                        <line x1="9" y1="2" x2="15" y2="2"></line>
                        <line x1="7" y1="14" x2="17" y2="14"></line>
                    </svg>
                </div>
                <div class="bp-stat__value"><?= $totalSample ?></div>
                <div class="bp-stat__label">Permintaan Sample</div>
                <div class="bp-stat__breakdown">
                    <span><?= $sampleSummary['pending'] ?> menunggu</span>
                    <span><?= $sampleSummary['waiting_result'] ?> proses hasil</span>
                    <span><?= $sampleSummary['result_ready'] ?> hasil siap</span>
                </div>
            </div>

            <!-- Total Return -->
            <div class="bp-card bp-stat">
                <div class="bp-stat__icon bp-stat__icon--orange">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="20" height="20">
                        <polyline points="1 4 1 10 7 10"></polyline>
                        <path d="M3.51 15a9 9 0 1 0 2.13-9.36L1 10"></path>
                    </svg>
                </div>
                <div class="bp-stat__value"><?= $totalReturn ?></div>
                <div class="bp-stat__label">Return / Komplain</div>
                <div class="bp-stat__breakdown">
                    <span><?= $returnSummary['submitted'] ?> baru</span>
                    <span><?= $returnSummary['under_review'] ?> ditinjau</span>
                    <span><?= $returnSummary['resolved'] ?> selesai</span>
                </div>
            </div>

            <!-- Invoice (placeholder, belum aktif) -->
            <div class="bp-card bp-stat bp-stat--disabled">
                <div class="bp-stat__icon bp-stat__icon--gray">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="20" height="20">
                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                        <polyline points="14 2 14 8 20 8"></polyline>
                        <line x1="8" y1="13" x2="16" y2="13"></line>
                        <line x1="8" y1="17" x2="16" y2="17"></line>
                    </svg>
                </div>
                <div class="bp-stat__value">&mdash;</div>
                <div class="bp-stat__label">Invoice / Tagihan</div>
                <div class="bp-stat__breakdown">
                    <span>Belum tersedia untuk akun ini</span>
                </div>
            </div>
        </section>

        <!-- ============ Tabel order terbaru ============ -->
        <section class="bp-card bp-recent">
            <div class="bp-recent__header">
                <h2>Order Terbaru</h2>
                <a href="<?= BUYER_URL ?>/orders.php" class="bp-link">
                    Lihat semua
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="14" height="14">
                        <line x1="5" y1="12" x2="19" y2="12"></line>
                        <polyline points="12 5 19 12 12 19"></polyline>
                    </svg>
                </a>
            </div>

            <?php if (empty($recentOrders)): ?>
                <div class="bp-empty">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="40" height="40">
                        <path d="M3 3h2l.4 2M7 13h10l4-8H5.4"></path>
                        <circle cx="9" cy="20" r="1.5"></circle>
                        <circle cx="17" cy="20" r="1.5"></circle>
                    </svg>
                    <p>Belum ada order yang dibuat.</p>
                </div>
            <?php else: ?>
                <div class="bp-table-wrap">
                    <table class="bp-table">
                        <thead>
                            <tr>
                                <th>No. Order</th>
                                <th>Jenis Benang</th>
                                <th>Warna</th>
                                <th>Qty</th>
                                <th>Tanggal</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recentOrders as $o): ?>
                                <?php $st = $orderStatusLabel[$o['status']] ?? ['label' => $o['status'], 'class' => 'badge-pending']; ?>
                                <tr>
                                    <td data-label="No. Order"><?= htmlspecialchars($o['no_order']) ?></td>
                                    <td data-label="Jenis Benang"><?= htmlspecialchars($o['jenis_benang']) ?></td>
                                    <td data-label="Warna"><?= htmlspecialchars($o['nama_warna'] ?? '-') ?></td>
                                    <td data-label="Qty"><?= number_format((float) $o['qty'], 0, ',', '.') ?></td>
                                    <td data-label="Tanggal"><?= date('d M Y', strtotime($o['tanggal'])) ?></td>
                                    <td data-label="Status">
                                        <span class="bp-badge <?= $st['class'] ?>"><?= htmlspecialchars($st['label']) ?></span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </section>

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

    .bp-dash {
        padding: 24px;
        max-width: 1200px;
        margin: 0 auto;
        display: flex;
        flex-direction: column;
        gap: 18px;
    }

    .bp-dash__heading h1 {
        margin: 0 0 4px;
        font-size: 22px;
        font-weight: 700;
    }
    .bp-dash__heading p {
        margin: 0;
        font-size: 14px;
        color: #6b7280;
    }

    .bp-card {
        background: #ffffff;
        border: 1px solid #e5e7eb;
        border-radius: 14px;
        padding: 18px 20px;
    }

    /* Badge (dipakai bersama dengan _header.php) */
    .bp-badge {
        display: inline-flex;
        align-items: center;
        font-size: 12px;
        font-weight: 600;
        padding: 5px 10px;
        border-radius: 999px;
        white-space: nowrap;
    }
    .badge-verified { background: #ecfdf5; color: #047857; }
    .badge-pending  { background: #fffbeb; color: #b45309; }
    .badge-rejected { background: #fef2f2; color: #b91c1c; }
    .badge-info     { background: #eff6ff; color: #1d4ed8; }

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
    .bp-alert--info {
        background: #eff6ff;
        color: #1d4ed8;
    }
    .bp-alert--danger {
        background: #fef2f2;
        color: #b91c1c;
    }
    .bp-alert svg { flex-shrink: 0; margin-top: 1px; }

    /* Kartu profil */
    .bp-profile-card {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 14px;
        flex-wrap: wrap;
    }
    .bp-profile-card__info {
        display: flex;
        align-items: center;
        gap: 14px;
        min-width: 0;
    }
    .bp-profile-card__icon {
        flex-shrink: 0;
        width: 46px;
        height: 46px;
        border-radius: 12px;
        background: #eff6ff;
        color: #1d4ed8;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .bp-profile-card__name {
        font-size: 16px;
        font-weight: 700;
    }
    .bp-profile-card__meta {
        font-size: 13px;
        color: #6b7280;
        margin-top: 2px;
    }

    /* Grid statistik */
    .bp-stats-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 16px;
    }

    .bp-stat { display: flex; flex-direction: column; gap: 6px; }
    .bp-stat--disabled { opacity: 0.65; }

    .bp-stat__icon {
        width: 38px;
        height: 38px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 4px;
    }
    .bp-stat__icon--blue   { background: #eff6ff; color: #1d4ed8; }
    .bp-stat__icon--purple { background: #f5f3ff; color: #6d28d9; }
    .bp-stat__icon--orange { background: #fff7ed; color: #c2410c; }
    .bp-stat__icon--gray   { background: #f3f4f6; color: #6b7280; }

    .bp-stat__value {
        font-size: 26px;
        font-weight: 700;
        line-height: 1;
    }
    .bp-stat__label {
        font-size: 13px;
        color: #6b7280;
        font-weight: 600;
    }
    .bp-stat__breakdown {
        display: flex;
        flex-wrap: wrap;
        gap: 4px 10px;
        font-size: 12px;
        color: #9ca3af;
        margin-top: 4px;
    }

    /* Order terbaru */
    .bp-recent__header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 14px;
    }
    .bp-recent__header h2 {
        margin: 0;
        font-size: 16px;
        font-weight: 700;
    }
    .bp-link {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        font-size: 13px;
        font-weight: 600;
        color: #1d4ed8;
        text-decoration: none;
    }
    .bp-link:hover { text-decoration: underline; }

    .bp-table-wrap { overflow-x: auto; }
    .bp-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 13.5px;
    }
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
        padding: 12px;
        border-bottom: 1px solid #f3f4f6;
        white-space: nowrap;
    }
    .bp-table tbody tr:last-child td { border-bottom: none; }

    .bp-empty {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 10px;
        padding: 40px 0;
        color: #9ca3af;
    }
    .bp-empty p { margin: 0; font-size: 13.5px; }

    /* ====== Responsive ====== */
    @media (max-width: 1024px) {
        .bp-stats-grid { grid-template-columns: repeat(2, 1fr); }
    }

    @media (max-width: 640px) {
        .bp-dash { padding: 16px; gap: 14px; }
        .bp-stats-grid { grid-template-columns: 1fr; }
        .bp-profile-card { flex-direction: column; align-items: flex-start; }

        /* Tabel jadi stacked card di mobile */
        .bp-table thead { display: none; }
        .bp-table, .bp-table tbody, .bp-table tr, .bp-table td { display: block; width: 100%; }
        .bp-table tr {
            border: 1px solid #e5e7eb;
            border-radius: 10px;
            margin-bottom: 10px;
            padding: 6px 4px;
        }
        .bp-table td {
            display: flex;
            justify-content: space-between;
            gap: 10px;
            border-bottom: none;
            padding: 6px 10px;
        }
        .bp-table td::before {
            content: attr(data-label);
            font-weight: 600;
            color: #6b7280;
            font-size: 12px;
        }
    }
</style>

</body>
</html>