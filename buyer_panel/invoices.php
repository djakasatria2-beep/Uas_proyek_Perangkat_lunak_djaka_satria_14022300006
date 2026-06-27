<?php
// ============================================================
//  buyer_panel/invoices.php
//  Daftar invoice / tagihan untuk panel Buyer.
// ============================================================

define('REQUIRED_ROLE', 'buyer');
require_once __DIR__ . '/../assets/verifyRoleRedirect.php';
require_once __DIR__ . '/partials/config.php';

$kodePelanggan = $currentBuyer['kode_pelanggan'] ?? null;
$pageTitle = 'Invoice / Tagihan';

// ------------------------------------------------------------
// Filter status dari query string (?status=overdue|paid|issued|draft)
// ------------------------------------------------------------
$allowedStatus = ['DRAFT', 'ISSUED', 'PAID', 'OVERDUE'];
$filterStatus = strtoupper($_GET['status'] ?? '');
if (!in_array($filterStatus, $allowedStatus, true)) {
    $filterStatus = '';
}

// ------------------------------------------------------------
// Ambil daftar invoice (hanya jika kode_pelanggan sudah terisi)
// ------------------------------------------------------------
$invoices = [];
$summary = ['ISSUED' => 0, 'PAID' => 0, 'OVERDUE' => 0, 'DRAFT' => 0];
$totalOutstanding = 0.0;

if ($kodePelanggan) {
    // Ringkasan jumlah per status
    $stmt = $conn->prepare("
        SELECT status, COUNT(*) AS jumlah, SUM(total_idr) AS total
        FROM invoices
        WHERE customer_id = ?
        GROUP BY status
    ");
    $stmt->bind_param('s', $kodePelanggan);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($row = $res->fetch_assoc()) {
        $summary[$row['status']] = (int) $row['jumlah'];
        if (in_array($row['status'], ['ISSUED', 'OVERDUE'], true)) {
            $totalOutstanding += (float) $row['total'];
        }
    }
    $stmt->close();

    // Daftar invoice (dengan filter opsional)
    $sql = "
        SELECT invoice_id, invoice_date, due_date, subtotal_idr, ppn_idr, total_idr, status
        FROM invoices
        WHERE customer_id = ?
    ";
    if ($filterStatus !== '') {
        $sql .= " AND status = ? ";
    }
    $sql .= " ORDER BY invoice_date DESC, invoice_id DESC ";

    $stmt = $conn->prepare($sql);
    if ($filterStatus !== '') {
        $stmt->bind_param('ss', $kodePelanggan, $filterStatus);
    } else {
        $stmt->bind_param('s', $kodePelanggan);
    }
    $stmt->execute();
    $res = $stmt->get_result();
    while ($row = $res->fetch_assoc()) {
        $invoices[] = $row;
    }
    $stmt->close();
}

$totalInvoice = array_sum($summary);

$statusLabel = [
    'DRAFT'   => ['label' => 'Draft',     'class' => 'badge-pending'],
    'ISSUED'  => ['label' => 'Terbit',    'class' => 'badge-info'],
    'PAID'    => ['label' => 'Lunas',     'class' => 'badge-verified'],
    'OVERDUE' => ['label' => 'Jatuh Tempo', 'class' => 'badge-rejected'],
];

$filterTabs = [
    ''         => 'Semua',
    'ISSUED'   => 'Terbit',
    'OVERDUE'  => 'Jatuh Tempo',
    'PAID'     => 'Lunas',
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
    <main class="bp-inv">

        <div class="bp-inv__heading">
            <h1>Invoice / Tagihan</h1>
            <p>Kelola dan pantau status pembayaran invoice perusahaan Anda.</p>
        </div>

        <?php if (!$kodePelanggan): ?>

            <!-- ============ Belum ada relasi kode pelanggan ============ -->
            <section class="bp-card bp-inv-empty-state">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="42" height="42">
                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                    <polyline points="14 2 14 8 20 8"></polyline>
                    <line x1="8" y1="13" x2="16" y2="13"></line>
                    <line x1="8" y1="17" x2="16" y2="17"></line>
                </svg>
                <h3>Data Invoice Belum Tersedia</h3>
                <p>Akun Anda belum terhubung dengan kode pelanggan di sistem. Silakan hubungi admin untuk mengaktifkan akses invoice.</p>
            </section>

        <?php else: ?>

            <!-- ============ Ringkasan kartu ============ -->
            <section class="bp-stats-grid">
                <div class="bp-card bp-stat">
                    <div class="bp-stat__icon bp-stat__icon--blue">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="20" height="20">
                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                            <polyline points="14 2 14 8 20 8"></polyline>
                        </svg>
                    </div>
                    <div class="bp-stat__value"><?= $totalInvoice ?></div>
                    <div class="bp-stat__label">Total Invoice</div>
                </div>

                <div class="bp-card bp-stat">
                    <div class="bp-stat__icon bp-stat__icon--orange">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="20" height="20">
                            <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path>
                            <line x1="12" y1="9" x2="12" y2="13"></line>
                            <line x1="12" y1="17" x2="12.01" y2="17"></line>
                        </svg>
                    </div>
                    <div class="bp-stat__value"><?= $summary['OVERDUE'] ?></div>
                    <div class="bp-stat__label">Jatuh Tempo</div>
                </div>

                <div class="bp-card bp-stat">
                    <div class="bp-stat__icon bp-stat__icon--purple">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="20" height="20">
                            <line x1="12" y1="1" x2="12" y2="23"></line>
                            <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path>
                        </svg>
                    </div>
                    <div class="bp-stat__value"><?= $summary['PAID'] ?></div>
                    <div class="bp-stat__label">Lunas</div>
                </div>

                <div class="bp-card bp-stat">
                    <div class="bp-stat__icon bp-stat__icon--gray">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="20" height="20">
                            <circle cx="12" cy="12" r="10"></circle>
                            <path d="M12 6v6l4 2"></path>
                        </svg>
                    </div>
                    <div class="bp-stat__value">Rp<?= number_format($totalOutstanding, 0, ',', '.') ?></div>
                    <div class="bp-stat__label">Total Belum Lunas</div>
                </div>
            </section>

            <!-- ============ Tabel invoice ============ -->
            <section class="bp-card bp-inv-table-card">
                <div class="bp-inv-table-card__header">
                    <h2>Daftar Invoice</h2>
                    <div class="bp-filter-tabs">
                        <?php foreach ($filterTabs as $value => $label): ?>
                            <a href="<?= BUYER_URL ?>/invoices.php<?= $value !== '' ? '?status=' . strtolower($value) : '' ?>"
                               class="bp-filter-tab <?= $filterStatus === $value ? 'is-active' : '' ?>">
                                <?= htmlspecialchars($label) ?>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>

                <?php if (empty($invoices)): ?>
                    <div class="bp-empty">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="40" height="40">
                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                            <polyline points="14 2 14 8 20 8"></polyline>
                        </svg>
                        <p>Tidak ada invoice untuk ditampilkan.</p>
                    </div>
                <?php else: ?>
                    <div class="bp-table-wrap">
                        <table class="bp-table">
                            <thead>
                                <tr>
                                    <th>No. Invoice</th>
                                    <th>Tgl. Invoice</th>
                                    <th>Jatuh Tempo</th>
                                    <th>Subtotal</th>
                                    <th>PPN</th>
                                    <th>Total</th>
                                    <th>Status</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($invoices as $inv): ?>
                                    <?php
                                    $st = $statusLabel[$inv['status']] ?? ['label' => $inv['status'], 'class' => 'badge-pending'];
                                    $isOverdueRow = $inv['status'] === 'OVERDUE';
                                    ?>
                                    <tr class="<?= $isOverdueRow ? 'bp-row--alert' : '' ?>">
                                        <td data-label="No. Invoice"><?= htmlspecialchars($inv['invoice_id']) ?></td>
                                        <td data-label="Tgl. Invoice"><?= date('d M Y', strtotime($inv['invoice_date'])) ?></td>
                                        <td data-label="Jatuh Tempo">
                                            <?= $inv['due_date'] ? date('d M Y', strtotime($inv['due_date'])) : '-' ?>
                                        </td>
                                        <td data-label="Subtotal">Rp<?= number_format((float) $inv['subtotal_idr'], 0, ',', '.') ?></td>
                                        <td data-label="PPN">Rp<?= number_format((float) $inv['ppn_idr'], 0, ',', '.') ?></td>
                                        <td data-label="Total"><strong>Rp<?= number_format((float) $inv['total_idr'], 0, ',', '.') ?></strong></td>
                                        <td data-label="Status">
                                            <span class="bp-badge <?= $st['class'] ?>"><?= htmlspecialchars($st['label']) ?></span>
                                        </td>
                                        <td data-label="" class="bp-table__action">
                                            <a href="<?= BUYER_URL ?>/invoice-detail.php?id=<?= urlencode($inv['invoice_id']) ?>" class="bp-icon-link" aria-label="Lihat detail invoice">
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

    .bp-inv {
        padding: 24px;
        max-width: 1200px;
        margin: 0 auto;
        display: flex;
        flex-direction: column;
        gap: 18px;
    }

    .bp-inv__heading h1 { margin: 0 0 4px; font-size: 22px; font-weight: 700; }
    .bp-inv__heading p { margin: 0; font-size: 14px; color: #6b7280; }

    .bp-card {
        background: #ffffff;
        border: 1px solid #e5e7eb;
        border-radius: 14px;
        padding: 18px 20px;
    }

    /* Empty state khusus belum ada kode_pelanggan */
    .bp-inv-empty-state {
        display: flex;
        flex-direction: column;
        align-items: center;
        text-align: center;
        gap: 10px;
        padding: 48px 24px;
        color: #6b7280;
    }
    .bp-inv-empty-state svg { color: #9ca3af; }
    .bp-inv-empty-state h3 { margin: 6px 0 0; font-size: 16px; color: #111827; }
    .bp-inv-empty-state p { margin: 0; font-size: 13.5px; max-width: 380px; }

    /* Badge & status */
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

    /* Tabel card header + filter tabs */
    .bp-inv-table-card__header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 12px;
        margin-bottom: 16px;
    }
    .bp-inv-table-card__header h2 { margin: 0; font-size: 16px; font-weight: 700; }

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
    .bp-row--alert { background: #fef9f9; }

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
    .bp-icon-link:hover { background: #f3f4f6; color: #1d4ed8; }

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
        .bp-inv { padding: 16px; gap: 14px; }
        .bp-stats-grid { grid-template-columns: 1fr; }
        .bp-inv-table-card__header { flex-direction: column; align-items: flex-start; }
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
            gap: 10px;
            border-bottom: none;
            padding: 6px 10px;
        }
        .bp-table td[data-label]:not([data-label=""])::before {
            content: attr(data-label);
            font-weight: 600;
            color: #6b7280;
            font-size: 12px;
        }
        .bp-table__action { justify-content: flex-end; }
    }
</style>

</body>
</html>