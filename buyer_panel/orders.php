<?php
// ============================================================
//  buyer_panel/orders.php
//  Daftar pesanan untuk panel Buyer.
// ============================================================

define('REQUIRED_ROLE', 'buyer');
require_once __DIR__ . '/../assets/verifyRoleRedirect.php';
require_once __DIR__ . '/partials/config.php';

$idBuyer  = (int) ($currentBuyer['id_buyer'] ?? 0);
$pageTitle = 'Pesanan Saya';

// ------------------------------------------------------------
// Filter status dari query string
// ------------------------------------------------------------
$allowedStatus = ['pending', 'processing', 'shipped', 'done', 'cancelled'];
$filterStatus  = strtolower($_GET['status'] ?? '');
if (!in_array($filterStatus, $allowedStatus, true)) {
    $filterStatus = '';
}

// ------------------------------------------------------------
// Ringkasan jumlah per status
// ------------------------------------------------------------
$summary = [
    'pending'    => 0,
    'processing' => 0,
    'shipped'    => 0,
    'done'       => 0,
    'cancelled'  => 0,
];

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
    if (isset($summary[$row['status']])) {
        $summary[$row['status']] = (int) $row['jumlah'];
    }
}
$stmt->close();

$totalOrders    = array_sum($summary);
$activeOrders   = $summary['pending'] + $summary['processing'];
$needsAttention = $summary['shipped']; // sudah dikirim, menunggu konfirmasi penerimaan

// ------------------------------------------------------------
// Daftar pesanan (dengan filter opsional)
// ------------------------------------------------------------
$sql = "
    SELECT
        o.id_order,
        o.no_order,
        o.jenis_benang,
        o.ukuran_benang,
        o.kode_warna,
        o.nama_warna,
        o.qty,
        o.harga_benang,
        o.tanggal,
        o.catatan,
        o.status
    FROM orders o
    WHERE o.id_buyer = ?
";
if ($filterStatus !== '') {
    $sql .= " AND o.status = ? ";
}
$sql .= " ORDER BY o.tanggal DESC, o.id_order DESC ";

$stmt = $conn->prepare($sql);
if ($filterStatus !== '') {
    $stmt->bind_param('is', $idBuyer, $filterStatus);
} else {
    $stmt->bind_param('i', $idBuyer);
}
$stmt->execute();
$res    = $stmt->get_result();
$orders = [];
while ($row = $res->fetch_assoc()) {
    $orders[] = $row;
}
$stmt->close();

// ------------------------------------------------------------
// Label & warna badge status
// ------------------------------------------------------------
$statusLabel = [
    'pending'    => ['label' => 'Menunggu',    'class' => 'badge-pending'],
    'processing' => ['label' => 'Diproses',    'class' => 'badge-info'],
    'shipped'    => ['label' => 'Dikirim',     'class' => 'badge-attention'],
    'done'       => ['label' => 'Selesai',     'class' => 'badge-verified'],
    'cancelled'  => ['label' => 'Dibatalkan',  'class' => 'badge-rejected'],
];

$filterTabs = [
    ''           => 'Semua',
    'pending'    => 'Menunggu',
    'processing' => 'Diproses',
    'shipped'    => 'Dikirim',
    'done'       => 'Selesai',
];
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
    <main class="bp-ord">

        <div class="bp-ord__heading">
            <div>
                <h1>Pesanan Saya</h1>
                <p>Kelola dan pantau seluruh pesanan benang Anda.</p>
            </div>
            <a href="<?= BUYER_URL ?>/orders-new.php" class="bp-btn-primary">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="18" height="18">
                    <line x1="12" y1="5" x2="12" y2="19"></line>
                    <line x1="5" y1="12" x2="19" y2="12"></line>
                </svg>
                <span>Buat Pesanan Baru</span>
            </a>
        </div>

        <?php if ($needsAttention > 0): ?>
            <div class="bp-alert bp-alert--attention">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="18" height="18">
                    <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path>
                    <line x1="12" y1="9" x2="12" y2="13"></line>
                    <line x1="12" y1="17" x2="12.01" y2="17"></line>
                </svg>
                <span>
                    <strong><?= $needsAttention ?> pesanan</strong> sudah dikirim dan menunggu konfirmasi penerimaan dari Anda.
                </span>
            </div>
        <?php endif; ?>

        <!-- ============ Ringkasan kartu ============ -->
        <section class="bp-stats-grid">
            <div class="bp-card bp-stat">
                <div class="bp-stat__icon bp-stat__icon--blue">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="20" height="20">
                        <path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"></path>
                        <line x1="3" y1="6" x2="21" y2="6"></line>
                        <path d="M16 10a4 4 0 0 1-8 0"></path>
                    </svg>
                </div>
                <div class="bp-stat__value"><?= $totalOrders ?></div>
                <div class="bp-stat__label">Total Pesanan</div>
            </div>

            <div class="bp-card bp-stat">
                <div class="bp-stat__icon bp-stat__icon--gray">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="20" height="20">
                        <circle cx="12" cy="12" r="10"></circle>
                        <path d="M12 6v6l4 2"></path>
                    </svg>
                </div>
                <div class="bp-stat__value"><?= $activeOrders ?></div>
                <div class="bp-stat__label">Sedang Berjalan</div>
            </div>

            <div class="bp-card bp-stat">
                <div class="bp-stat__icon bp-stat__icon--orange">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="20" height="20">
                        <rect x="1" y="3" width="15" height="13" rx="1"></rect>
                        <path d="M16 8h4l3 5v3h-7V8z"></path>
                        <circle cx="5.5" cy="18.5" r="2.5"></circle>
                        <circle cx="18.5" cy="18.5" r="2.5"></circle>
                    </svg>
                </div>
                <div class="bp-stat__value"><?= $summary['shipped'] ?></div>
                <div class="bp-stat__label">Dalam Pengiriman</div>
            </div>

            <div class="bp-card bp-stat">
                <div class="bp-stat__icon bp-stat__icon--purple">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="20" height="20">
                        <polyline points="20 6 9 17 4 12"></polyline>
                    </svg>
                </div>
                <div class="bp-stat__value"><?= $summary['done'] ?></div>
                <div class="bp-stat__label">Selesai</div>
            </div>
        </section>

        <!-- ============ Tabel pesanan ============ -->
        <section class="bp-card bp-ord-table-card">
            <div class="bp-ord-table-card__header">
                <h2>Daftar Pesanan</h2>
                <div class="bp-filter-tabs">
                    <?php foreach ($filterTabs as $value => $label): ?>
                        <a href="<?= BUYER_URL ?>/orders.php<?= $value !== '' ? '?status=' . $value : '' ?>"
                           class="bp-filter-tab <?= $filterStatus === $value ? 'is-active' : '' ?>">
                            <?= htmlspecialchars($label) ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>

            <?php if (empty($orders)): ?>
                <div class="bp-empty">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="40" height="40">
                        <path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"></path>
                        <line x1="3" y1="6" x2="21" y2="6"></line>
                        <path d="M16 10a4 4 0 0 1-8 0"></path>
                    </svg>
                    <p>Belum ada pesanan.</p>
                    <a href="<?= BUYER_URL ?>/orders-new.php" class="bp-link">Buat pesanan pertama Anda</a>
                </div>
            <?php else: ?>
                <div class="bp-table-wrap">
                    <table class="bp-table bp-table--clickable">
                        <thead>
                            <tr>
                                <th>No. Pesanan</th>
                                <th>Jenis Benang</th>
                                <th>Ukuran</th>
                                <th>Kode / Nama Warna</th>
                                <th>Qty (kg)</th>
                                <th>Harga / kg</th>
                                <th>Total</th>
                                <th>Tgl. Pesan</th>
                                <th>Status</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($orders as $o): ?>
                                <?php
                                $st        = $statusLabel[$o['status']] ?? ['label' => $o['status'], 'class' => 'badge-pending'];
                                $needsAttn = $o['status'] === 'shipped';
                                $total     = (float) $o['harga_benang'] * (int) $o['qty'];
                                ?>
                                <tr class="<?= $needsAttn ? 'bp-row--attention' : '' ?>"
                                    onclick="window.location='<?= BUYER_URL ?>/orders-detail.php?id=<?= urlencode((string) $o['id_order']) ?>'">
                                    <td data-label="No. Pesanan">
                                        <span class="bp-order-no"><?= htmlspecialchars($o['no_order']) ?></span>
                                    </td>
                                    <td data-label="Jenis Benang"><?= htmlspecialchars($o['jenis_benang']) ?></td>
                                    <td data-label="Ukuran"><?= htmlspecialchars($o['ukuran_benang'] ?? '-') ?></td>
                                    <td data-label="Kode / Nama Warna">
                                        <?php if ($o['kode_warna'] || $o['nama_warna']): ?>
                                            <span class="bp-color-code"><?= htmlspecialchars($o['kode_warna'] ?? '') ?></span>
                                            <?php if ($o['nama_warna']): ?>
                                                <span class="bp-color-name"><?= htmlspecialchars($o['nama_warna']) ?></span>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            -
                                        <?php endif; ?>
                                    </td>
                                    <td data-label="Qty (kg)"><?= number_format($o['qty'], 0, ',', '.') ?></td>
                                    <td data-label="Harga / kg">Rp <?= number_format((float) $o['harga_benang'], 0, ',', '.') ?></td>
                                    <td data-label="Total">Rp <?= number_format($total, 0, ',', '.') ?></td>
                                    <td data-label="Tgl. Pesan"><?= date('d M Y', strtotime($o['tanggal'])) ?></td>
                                    <td data-label="Status">
                                        <span class="bp-badge <?= $st['class'] ?>"><?= htmlspecialchars($st['label']) ?></span>
                                    </td>
                                    <td data-label="" class="bp-table__action">
                                        <a href="<?= BUYER_URL ?>/orders-detail.php?id=<?= urlencode((string) $o['id_order']) ?>"
                                           class="bp-icon-link" aria-label="Lihat detail pesanan" onclick="event.stopPropagation();">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="18" height="18">
                                                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                                <circle cx="12" cy="12" r="3"></circle>
                                            </svg>
                                        </a>
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

    .bp-ord {
        padding: 24px;
        max-width: 1200px;
        margin: 0 auto;
        display: flex;
        flex-direction: column;
        gap: 18px;
    }

    .bp-ord__heading {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 14px;
        flex-wrap: wrap;
    }
    .bp-ord__heading h1 { margin: 0 0 4px; font-size: 22px; font-weight: 700; }
    .bp-ord__heading p  { margin: 0; font-size: 14px; color: #6b7280; }

    .bp-btn-primary {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 10px 16px;
        background: #2563eb;
        color: #ffffff;
        border-radius: 10px;
        text-decoration: none;
        font-size: 13.5px;
        font-weight: 600;
        white-space: nowrap;
        transition: background 0.15s ease;
    }
    .bp-btn-primary:hover { background: #1d4ed8; }

    .bp-card {
        background: #ffffff;
        border: 1px solid #e5e7eb;
        border-radius: 14px;
        padding: 18px 20px;
    }

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
    .bp-alert--attention { background: #fff7ed; color: #c2410c; }
    .bp-alert svg { flex-shrink: 0; margin-top: 1px; }

    /* Badge */
    .bp-badge {
        display: inline-flex;
        align-items: center;
        font-size: 12px;
        font-weight: 600;
        padding: 5px 10px;
        border-radius: 999px;
        white-space: nowrap;
    }
    .badge-verified  { background: #ecfdf5; color: #047857; }
    .badge-pending   { background: #fffbeb; color: #b45309; }
    .badge-rejected  { background: #fef2f2; color: #b91c1c; }
    .badge-info      { background: #eff6ff; color: #1d4ed8; }
    .badge-attention { background: #fff7ed; color: #c2410c; }

    /* Grid statistik */
    .bp-stats-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 16px;
    }
    .bp-stat { display: flex; flex-direction: column; gap: 6px; }
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
    .bp-stat__value { font-size: 24px; font-weight: 700; line-height: 1; }
    .bp-stat__label { font-size: 13px; color: #6b7280; font-weight: 600; }

    /* Tabel card header */
    .bp-ord-table-card__header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 12px;
        margin-bottom: 16px;
    }
    .bp-ord-table-card__header h2 { margin: 0; font-size: 16px; font-weight: 700; }

    /* Filter tabs */
    .bp-filter-tabs {
        display: flex;
        gap: 6px;
        flex-wrap: wrap;
        background: #f3f4f6;
        padding: 4px;
        border-radius: 10px;
    }
    .bp-filter-tab {
        padding: 7px 14px;
        border-radius: 8px;
        font-size: 13px;
        font-weight: 600;
        color: #6b7280;
        text-decoration: none;
        white-space: nowrap;
    }
    .bp-filter-tab:hover { color: #111827; }
    .bp-filter-tab.is-active {
        background: #ffffff;
        color: #1d4ed8;
        box-shadow: 0 1px 2px rgba(0,0,0,0.06);
    }

    /* Nomor order */
    .bp-order-no {
        font-family: 'SF Mono', 'Fira Code', 'Consolas', monospace;
        font-size: 12.5px;
        font-weight: 600;
        color: #374151;
        background: #f3f4f6;
        padding: 3px 7px;
        border-radius: 6px;
    }

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
    .bp-color-name {
        font-size: 13px;
        color: #374151;
    }

    /* Tabel */
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
        padding: 12px;
        border-bottom: 1px solid #f3f4f6;
        white-space: nowrap;
    }
    .bp-table tbody tr:last-child td { border-bottom: none; }
    .bp-table--clickable tbody tr { cursor: pointer; }
    .bp-table--clickable tbody tr:hover { background: #f9fafb; }
    .bp-row--attention { background: #fffaf5; }
    .bp-row--attention:hover { background: #fff3e8; }

    .bp-icon-link {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 32px;
        height: 32px;
        border-radius: 8px;
        color: #6b7280;
        text-decoration: none;
    }
    .bp-icon-link:hover { background: #e5e7eb; color: #1d4ed8; }

    .bp-empty {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 10px;
        padding: 40px 0;
        color: #9ca3af;
    }
    .bp-empty p { margin: 0; font-size: 13.5px; }
    .bp-link { font-size: 13px; font-weight: 600; color: #1d4ed8; text-decoration: none; }
    .bp-link:hover { text-decoration: underline; }

    /* ====== Responsive ====== */
    @media (max-width: 1024px) {
        .bp-stats-grid { grid-template-columns: repeat(2, 1fr); }
    }

    @media (max-width: 640px) {
        .bp-ord { padding: 16px; gap: 14px; }
        .bp-ord__heading { flex-direction: column; align-items: stretch; }
        .bp-btn-primary { justify-content: center; }
        .bp-stats-grid { grid-template-columns: 1fr; }
        .bp-ord-table-card__header { flex-direction: column; align-items: flex-start; }
        .bp-filter-tabs { width: 100%; overflow-x: auto; }

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

</body>
</html>