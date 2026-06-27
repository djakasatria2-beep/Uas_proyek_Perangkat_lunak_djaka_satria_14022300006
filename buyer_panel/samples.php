<?php
// ============================================================
//  buyer_panel/samples.php
//  Daftar permintaan sample untuk panel Buyer.
// ============================================================

define('REQUIRED_ROLE', 'buyer');
require_once __DIR__ . '/../assets/verifyRoleRedirect.php';
require_once __DIR__ . '/partials/config.php';

$idBuyer = (int) ($currentBuyer['id_buyer'] ?? 0);
$pageTitle = 'Sample Request';

// ------------------------------------------------------------
// Filter status dari query string
// ------------------------------------------------------------
$allowedStatus = ['pending', 'waiting_result', 'result_ready', 'approved', 'rejected', 'revision'];
$filterStatus = strtolower($_GET['status'] ?? '');
if (!in_array($filterStatus, $allowedStatus, true)) {
    $filterStatus = '';
}

// ------------------------------------------------------------
// Ringkasan jumlah per status
// ------------------------------------------------------------
$summary = [
    'pending'        => 0,
    'waiting_result' => 0,
    'result_ready'   => 0,
    'approved'       => 0,
    'rejected'       => 0,
    'revision'       => 0,
];

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
    $summary[$row['status']] = (int) $row['jumlah'];
}
$stmt->close();

$totalSample = array_sum($summary);
$needsAttention = $summary['result_ready']; // Hasil sudah siap, menunggu approval buyer

// ------------------------------------------------------------
// Daftar sample request (dengan filter opsional), JOIN ke sample_results
// untuk menampilkan status approval hasil jika ada.
// ------------------------------------------------------------
$sql = "
    SELECT
        sr.id_request,
        sr.jenis_benang,
        sr.ukuran_benang,
        sr.kode_warna_target,
        sr.tanggal,
        sr.tanggal_dibutuhkan,
        sr.status,
        sres.id_result,
        sres.pilihan,
        sres.status_approval
    FROM sample_requests sr
    LEFT JOIN sample_results sres ON sres.id_request = sr.id_request
    WHERE sr.id_buyer = ?
";
if ($filterStatus !== '') {
    $sql .= " AND sr.status = ? ";
}
$sql .= " ORDER BY sr.tanggal DESC, sr.id_request DESC ";

$stmt = $conn->prepare($sql);
if ($filterStatus !== '') {
    $stmt->bind_param('is', $idBuyer, $filterStatus);
} else {
    $stmt->bind_param('i', $idBuyer);
}
$stmt->execute();
$res = $stmt->get_result();
$samples = [];
while ($row = $res->fetch_assoc()) {
    $samples[] = $row;
}
$stmt->close();

// ------------------------------------------------------------
// Label & warna badge status
// ------------------------------------------------------------
$statusLabel = [
    'pending'        => ['label' => 'Menunggu',        'class' => 'badge-pending'],
    'waiting_result' => ['label' => 'Proses Hasil',     'class' => 'badge-info'],
    'result_ready'   => ['label' => 'Hasil Siap',       'class' => 'badge-attention'],
    'approved'       => ['label' => 'Disetujui',        'class' => 'badge-verified'],
    'rejected'       => ['label' => 'Ditolak',          'class' => 'badge-rejected'],
    'revision'       => ['label' => 'Revisi',           'class' => 'badge-pending'],
];

$filterTabs = [
    ''               => 'Semua',
    'pending'        => 'Menunggu',
    'waiting_result' => 'Proses Hasil',
    'result_ready'   => 'Hasil Siap',
    'approved'       => 'Disetujui',
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
    <main class="bp-smp">

        <div class="bp-smp__heading">
            <div>
                <h1>Sample Request</h1>
                <p>Kelola dan pantau permintaan sample warna benang Anda.</p>
            </div>
            <a href="<?= BUYER_URL ?>/samples-new.php" class="bp-btn-primary">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="18" height="18">
                    <line x1="12" y1="5" x2="12" y2="19"></line>
                    <line x1="5" y1="12" x2="19" y2="12"></line>
                </svg>
                <span>Ajukan Sample Baru</span>
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
                    <strong><?= $needsAttention ?> hasil sample</strong> sudah siap dan menunggu persetujuan Anda.
                </span>
            </div>
        <?php endif; ?>

        <!-- ============ Ringkasan kartu ============ -->
        <section class="bp-stats-grid">
            <div class="bp-card bp-stat">
                <div class="bp-stat__icon bp-stat__icon--blue">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="20" height="20">
                        <path d="M9 2v6L4 19a1 1 0 0 0 1 1h14a1 1 0 0 0 1-1L15 8V2"></path>
                        <line x1="9" y1="2" x2="15" y2="2"></line>
                    </svg>
                </div>
                <div class="bp-stat__value"><?= $totalSample ?></div>
                <div class="bp-stat__label">Total Permintaan</div>
            </div>

            <div class="bp-card bp-stat">
                <div class="bp-stat__icon bp-stat__icon--gray">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="20" height="20">
                        <circle cx="12" cy="12" r="10"></circle>
                        <path d="M12 6v6l4 2"></path>
                    </svg>
                </div>
                <div class="bp-stat__value"><?= $summary['pending'] + $summary['waiting_result'] ?></div>
                <div class="bp-stat__label">Sedang Diproses</div>
            </div>

            <div class="bp-card bp-stat">
                <div class="bp-stat__icon bp-stat__icon--orange">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="20" height="20">
                        <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path>
                        <line x1="12" y1="9" x2="12" y2="13"></line>
                        <line x1="12" y1="17" x2="12.01" y2="17"></line>
                    </svg>
                </div>
                <div class="bp-stat__value"><?= $summary['result_ready'] ?></div>
                <div class="bp-stat__label">Menunggu Persetujuan</div>
            </div>

            <div class="bp-card bp-stat">
                <div class="bp-stat__icon bp-stat__icon--purple">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="20" height="20">
                        <polyline points="20 6 9 17 4 12"></polyline>
                    </svg>
                </div>
                <div class="bp-stat__value"><?= $summary['approved'] ?></div>
                <div class="bp-stat__label">Disetujui</div>
            </div>
        </section>

        <!-- ============ Tabel sample request ============ -->
        <section class="bp-card bp-smp-table-card">
            <div class="bp-smp-table-card__header">
                <h2>Daftar Permintaan</h2>
                <div class="bp-filter-tabs">
                    <?php foreach ($filterTabs as $value => $label): ?>
                        <a href="<?= BUYER_URL ?>/samples.php<?= $value !== '' ? '?status=' . $value : '' ?>"
                           class="bp-filter-tab <?= $filterStatus === $value ? 'is-active' : '' ?>">
                            <?= htmlspecialchars($label) ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>

            <?php if (empty($samples)): ?>
                <div class="bp-empty">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="40" height="40">
                        <path d="M9 2v6L4 19a1 1 0 0 0 1 1h14a1 1 0 0 0 1-1L15 8V2"></path>
                        <line x1="9" y1="2" x2="15" y2="2"></line>
                    </svg>
                    <p>Belum ada permintaan sample.</p>
                    <a href="<?= BUYER_URL ?>/samples-new.php" class="bp-link">Ajukan sample pertama Anda</a>
                </div>
            <?php else: ?>
                <div class="bp-table-wrap">
                    <table class="bp-table bp-table--clickable">
                        <thead>
                            <tr>
                                <th>Jenis Benang</th>
                                <th>Ukuran</th>
                                <th>Kode Warna</th>
                                <th>Tgl. Permintaan</th>
                                <th>Dibutuhkan</th>
                                <th>Hasil</th>
                                <th>Status</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($samples as $s): ?>
                                <?php
                                $st = $statusLabel[$s['status']] ?? ['label' => $s['status'], 'class' => 'badge-pending'];
                                $needsAttn = $s['status'] === 'result_ready';

                                // Tampilan kolom hasil
                                if ($s['id_result']) {
                                    $pilihanLabel = ['A' => 'Pilihan A', 'B' => 'Pilihan B', 'rejected' => 'Ditolak', 'pending' => 'Belum dipilih'][$s['pilihan']] ?? '-';
                                } else {
                                    $pilihanLabel = '-';
                                }
                                ?>
                                <tr class="<?= $needsAttn ? 'bp-row--attention' : '' ?>"
                                    onclick="window.location='<?= BUYER_URL ?>/samples-detail.php?id=<?= urlencode((string) $s['id_request']) ?>'">
                                    <td data-label="Jenis Benang"><?= htmlspecialchars($s['jenis_benang']) ?></td>
                                    <td data-label="Ukuran"><?= htmlspecialchars($s['ukuran_benang'] ?? '-') ?></td>
                                    <td data-label="Kode Warna"><?= htmlspecialchars($s['kode_warna_target'] ?? '-') ?></td>
                                    <td data-label="Tgl. Permintaan"><?= date('d M Y', strtotime($s['tanggal'])) ?></td>
                                    <td data-label="Dibutuhkan">
                                        <?= $s['tanggal_dibutuhkan'] ? date('d M Y', strtotime($s['tanggal_dibutuhkan'])) : '-' ?>
                                    </td>
                                    <td data-label="Hasil"><?= htmlspecialchars($pilihanLabel) ?></td>
                                    <td data-label="Status">
                                        <span class="bp-badge <?= $st['class'] ?>"><?= htmlspecialchars($st['label']) ?></span>
                                    </td>
                                    <td data-label="" class="bp-table__action">
                                        <a href="<?= BUYER_URL ?>/samples-detail.php?id=<?= urlencode((string) $s['id_request']) ?>"
                                           class="bp-icon-link" aria-label="Lihat detail sample" onclick="event.stopPropagation();">
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

    .bp-smp {
        padding: 24px;
        max-width: 1200px;
        margin: 0 auto;
        display: flex;
        flex-direction: column;
        gap: 18px;
    }

    .bp-smp__heading {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 14px;
        flex-wrap: wrap;
    }
    .bp-smp__heading h1 { margin: 0 0 4px; font-size: 22px; font-weight: 700; }
    .bp-smp__heading p { margin: 0; font-size: 14px; color: #6b7280; }

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

    /* Alert perhatian (hasil siap) */
    .bp-alert {
        display: flex;
        align-items: flex-start;
        gap: 10px;
        padding: 12px 16px;
        border-radius: 10px;
        font-size: 13.5px;
        line-height: 1.4;
    }
    .bp-alert--attention {
        background: #fff7ed;
        color: #c2410c;
    }
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

    /* Tabel card header + filter tabs */
    .bp-smp-table-card__header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 12px;
        margin-bottom: 16px;
    }
    .bp-smp-table-card__header h2 { margin: 0; font-size: 16px; font-weight: 700; }

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
        .bp-smp { padding: 16px; gap: 14px; }
        .bp-smp__heading { flex-direction: column; align-items: stretch; }
        .bp-btn-primary { justify-content: center; }
        .bp-stats-grid { grid-template-columns: 1fr; }
        .bp-smp-table-card__header { flex-direction: column; align-items: flex-start; }
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