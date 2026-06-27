<?php
// ============================================================
//  buyer_panel/tracking.php
//  Lacak status & riwayat pengiriman pesanan milik buyer yang login.
// ============================================================

define('REQUIRED_ROLE', 'buyer');
require_once __DIR__ . '/../assets/verifyRoleRedirect.php';
require_once __DIR__ . '/partials/config.php';

$idBuyer   = (int) ($currentBuyer['id_buyer'] ?? 0);
$pageTitle = 'Lacak Pesanan';

// ------------------------------------------------------------
// Filter & search
// ------------------------------------------------------------
$filterStatus = $_GET['status'] ?? '';
$search       = trim($_GET['q'] ?? '');

$allowedStatus = ['pending', 'processing', 'shipped', 'done', 'cancelled'];
if (!in_array($filterStatus, $allowedStatus)) $filterStatus = '';

// ------------------------------------------------------------
// Build query - pesanan milik buyer
// ------------------------------------------------------------
$where  = ['o.id_buyer = ?'];
$params = [$idBuyer];
$types  = 'i';

if ($filterStatus !== '') {
    $where[]  = 'o.status = ?';
    $params[] = $filterStatus;
    $types   .= 's';
}
if ($search !== '') {
    $like     = '%' . $search . '%';
    $where[]  = '(o.no_order LIKE ? OR o.jenis_benang LIKE ? OR o.nama_warna LIKE ?)';
    $params[] = $like;
    $params[] = $like;
    $params[] = $like;
    $types   .= 'sss';
}

$whereSQL = 'WHERE ' . implode(' AND ', $where);

$sql = "
    SELECT o.*
    FROM orders o
    $whereSQL
    ORDER BY o.tanggal DESC, o.id_order DESC
";

$stmt = $conn->prepare($sql);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$orders = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// ------------------------------------------------------------
// Riwayat tracking untuk semua pesanan yang tampil
// ------------------------------------------------------------
$trackingByOrder = [];

if (!empty($orders)) {
    $orderIds      = array_column($orders, 'id_order');
    $placeholders  = implode(',', array_fill(0, count($orderIds), '?'));
    $typesT        = str_repeat('i', count($orderIds));

    $sqlT = "
        SELECT id_order, status, keterangan, tanggal
        FROM tracking
        WHERE id_order IN ($placeholders)
        ORDER BY tanggal ASC, id_tracking ASC
    ";
    $stmtT = $conn->prepare($sqlT);
    $stmtT->bind_param($typesT, ...$orderIds);
    $stmtT->execute();
    $rowsT = $stmtT->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmtT->close();

    foreach ($rowsT as $row) {
        $trackingByOrder[$row['id_order']][] = $row;
    }
}

// ------------------------------------------------------------
// Statistik ringkas (tanpa filter)
// ------------------------------------------------------------
$statsStmt = $conn->prepare("
    SELECT status, COUNT(*) AS cnt
    FROM orders
    WHERE id_buyer = ?
    GROUP BY status
");
$statsStmt->bind_param('i', $idBuyer);
$statsStmt->execute();
$statsRaw = $statsStmt->get_result()->fetch_all(MYSQLI_ASSOC);
$statsStmt->close();

$stats = ['total' => 0, 'pending' => 0, 'processing' => 0,
          'shipped' => 0, 'done' => 0, 'cancelled' => 0];
foreach ($statsRaw as $s) {
    $stats[$s['status']] = (int) $s['cnt'];
    $stats['total']     += (int) $s['cnt'];
}

// ------------------------------------------------------------
// Helpers
// ------------------------------------------------------------
function trkStatusBadge(string $s): string {
    $map = [
        'pending'    => ['Menunggu Konfirmasi', 'badge-pending'],
        'processing' => ['Diproses',            'badge-processing'],
        'shipped'    => ['Dikirim',              'badge-shipped'],
        'done'       => ['Selesai',              'badge-done'],
        'cancelled'  => ['Dibatalkan',           'badge-cancelled'],
    ];
    $d = $map[$s] ?? [ucfirst($s), 'badge-pending'];
    return '<span class="bp-badge ' . $d[1] . '">' . $d[0] . '</span>';
}

// Tahapan progress (cancelled ditangani terpisah, tidak masuk garis progress)
function trkStep(string $s): int {
    $order = ['pending' => 1, 'processing' => 2, 'shipped' => 3, 'done' => 4];
    return $order[$s] ?? 0;
}

function trkDate(string $dt, string $fmt = 'd M Y, H:i'): string {
    $ts = strtotime($dt);
    return $ts ? date($fmt, $ts) : $dt;
}

function trkRupiah($n): string {
    return 'Rp' . number_format((float) $n, 0, ',', '.');
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
    <main class="bp-tracking">

        <!-- ===== Heading ===== -->
        <div class="bp-tracking__heading">
            <div>
                <h1>Lacak Pesanan</h1>
                <p>Pantau status &amp; riwayat pengiriman setiap pesanan Anda secara real-time.</p>
            </div>
            <a href="<?= BUYER_URL ?>/orders.php" class="bp-btn-secondary">
                Lihat Semua Pesanan
            </a>
        </div>

        <!-- ===== Stat Cards ===== -->
        <div class="bp-stat-grid">
            <div class="bp-stat-card">
                <div class="bp-stat-card__value"><?= $stats['total'] ?></div>
                <div class="bp-stat-card__label">Total Pesanan</div>
            </div>
            <div class="bp-stat-card bp-stat-card--pending">
                <div class="bp-stat-card__value"><?= $stats['pending'] + $stats['processing'] ?></div>
                <div class="bp-stat-card__label">Diproses</div>
            </div>
            <div class="bp-stat-card bp-stat-card--shipped">
                <div class="bp-stat-card__value"><?= $stats['shipped'] ?></div>
                <div class="bp-stat-card__label">Dikirim</div>
            </div>
            <div class="bp-stat-card bp-stat-card--done">
                <div class="bp-stat-card__value"><?= $stats['done'] ?></div>
                <div class="bp-stat-card__label">Selesai</div>
            </div>
        </div>

        <!-- ===== Filter & Search ===== -->
        <form method="GET" action="" class="bp-filter-bar">
            <div class="bp-filter-bar__search">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                     stroke-linecap="round" stroke-linejoin="round" width="15" height="15" class="bp-filter-bar__icon">
                    <circle cx="11" cy="11" r="8"></circle>
                    <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                </svg>
                <input type="text" name="q" placeholder="Cari no. pesanan, jenis benang, warna…"
                       value="<?= htmlspecialchars($search) ?>">
            </div>

            <select name="status">
                <option value="">Semua Status</option>
                <option value="pending"    <?= $filterStatus === 'pending'    ? 'selected' : '' ?>>Menunggu Konfirmasi</option>
                <option value="processing" <?= $filterStatus === 'processing' ? 'selected' : '' ?>>Diproses</option>
                <option value="shipped"    <?= $filterStatus === 'shipped'    ? 'selected' : '' ?>>Dikirim</option>
                <option value="done"       <?= $filterStatus === 'done'       ? 'selected' : '' ?>>Selesai</option>
                <option value="cancelled"  <?= $filterStatus === 'cancelled'  ? 'selected' : '' ?>>Dibatalkan</option>
            </select>

            <button type="submit" class="bp-btn-filter">Terapkan</button>

            <?php if ($filterStatus || $search): ?>
                <a href="<?= BUYER_URL ?>/tracking.php" class="bp-btn-reset">Reset</a>
            <?php endif; ?>
        </form>

        <!-- ===== List / Empty ===== -->
        <?php if (empty($orders)): ?>
            <div class="bp-empty-card">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"
                     stroke-linecap="round" stroke-linejoin="round" width="48" height="48" class="bp-empty-card__icon">
                    <rect x="1" y="3" width="15" height="13"></rect>
                    <polygon points="16 8 20 8 23 11 23 16 16 16 16 8"></polygon>
                    <circle cx="5.5" cy="18.5" r="2.5"></circle>
                    <circle cx="18.5" cy="18.5" r="2.5"></circle>
                </svg>
                <p class="bp-empty-card__title">
                    <?= ($filterStatus || $search)
                        ? 'Tidak ada pesanan yang sesuai filter.'
                        : 'Belum ada pesanan untuk dilacak.' ?>
                </p>
                <p class="bp-empty-card__sub">
                    <?= ($filterStatus || $search)
                        ? 'Coba ubah atau reset filter pencarian.'
                        : 'Pesanan yang sudah dibuat akan muncul di sini lengkap dengan riwayat pengirimannya.' ?>
                </p>
                <?php if (!$filterStatus && !$search): ?>
                    <a href="<?= BUYER_URL ?>/orders-new.php" class="bp-btn-secondary">Buat Pesanan Baru</a>
                <?php endif; ?>
            </div>

        <?php else: ?>

            <div class="bp-track-meta">
                <span class="bp-table-count"><?= count($orders) ?> pesanan ditemukan</span>
            </div>

            <div class="bp-track-list">
                <?php foreach ($orders as $ord):
                    $history     = $trackingByOrder[$ord['id_order']] ?? [];
                    $historyDesc = array_reverse($history); // terbaru di atas
                    $step        = trkStep($ord['status']);
                    $isCancelled = $ord['status'] === 'cancelled';
                ?>
                <div class="bp-track-card">

                    <!-- ===== Card Header ===== -->
                    <div class="bp-track-card__head">
                        <div class="bp-track-card__main">
                            <div class="bp-track-card__no">
                                <span class="bp-mono"><?= htmlspecialchars($ord['no_order']) ?></span>
                                <?= trkStatusBadge($ord['status']) ?>
                            </div>
                            <div class="bp-product-cell">
                                <span class="bp-product-name"><?= htmlspecialchars($ord['jenis_benang']) ?></span>
                                <span class="bp-product-sub">
                                    <?php if ($ord['ukuran_benang']): ?>
                                        <?= htmlspecialchars($ord['ukuran_benang']) ?> ·
                                    <?php endif; ?>
                                    <?= number_format((float) $ord['qty'], 0, ',', '.') ?> Kg
                                    <?php if ($ord['kode_warna'] || $ord['nama_warna']): ?>
                                        ·
                                        <?php if ($ord['kode_warna']): ?>
                                            <span class="bp-color-code"><?= htmlspecialchars($ord['kode_warna']) ?></span>
                                        <?php endif; ?>
                                        <?= htmlspecialchars($ord['nama_warna'] ?? '') ?>
                                    <?php endif; ?>
                                </span>
                            </div>
                        </div>
                        <div class="bp-track-card__side">
                            <div class="bp-track-card__date">
                                <span class="bp-track-card__date-label">Tgl. Pesan</span>
                                <span class="bp-track-card__date-value"><?= trkDate($ord['tanggal'], 'd M Y') ?></span>
                            </div>
                            <a href="<?= BUYER_URL ?>/orders-detail.php?id=<?= $ord['id_order'] ?>" class="bp-btn-detail">
                                Detail Pesanan
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                     stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                     width="13" height="13">
                                    <polyline points="9 18 15 12 9 6"></polyline>
                                </svg>
                            </a>
                        </div>
                    </div>

                    <!-- ===== Progress Bar ===== -->
                    <?php if ($isCancelled): ?>
                        <div class="bp-track-cancelled">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                 stroke-linecap="round" stroke-linejoin="round" width="16" height="16">
                                <circle cx="12" cy="12" r="10"></circle>
                                <line x1="15" y1="9" x2="9" y2="15"></line>
                                <line x1="9" y1="9" x2="15" y2="15"></line>
                            </svg>
                            Pesanan ini telah dibatalkan.
                        </div>
                    <?php else: ?>
                        <div class="bp-progress">
                            <?php
                            $stages = [
                                1 => 'Menunggu Konfirmasi',
                                2 => 'Diproses',
                                3 => 'Dikirim',
                                4 => 'Selesai',
                            ];
                            foreach ($stages as $n => $label):
                                $state = $n < $step ? 'done' : ($n === $step ? 'active' : 'upcoming');
                            ?>
                                <div class="bp-progress__step bp-progress__step--<?= $state ?>">
                                    <div class="bp-progress__dot">
                                        <?php if ($state === 'done'): ?>
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"
                                                 stroke-linecap="round" stroke-linejoin="round" width="11" height="11">
                                                <polyline points="20 6 9 17 4 12"></polyline>
                                            </svg>
                                        <?php endif; ?>
                                    </div>
                                    <span class="bp-progress__label"><?= $label ?></span>
                                </div>
                                <?php if ($n < 4): ?>
                                    <div class="bp-progress__line bp-progress__line--<?= $n < $step ? 'done' : 'upcoming' ?>"></div>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>

                    <!-- ===== Timeline Riwayat ===== -->
                    <div class="bp-track-history">
                        <?php if (empty($historyDesc)): ?>
                            <p class="bp-track-history__empty">
                                Belum ada update tracking. Tim kami akan segera memperbarui status pesanan Anda.
                            </p>
                        <?php else: ?>
                            <ul class="bp-timeline">
                                <?php foreach ($historyDesc as $i => $h): ?>
                                    <li class="bp-timeline__item <?= $i === 0 ? 'bp-timeline__item--current' : '' ?>">
                                        <span class="bp-timeline__dot"></span>
                                        <div class="bp-timeline__body">
                                            <div class="bp-timeline__row">
                                                <span class="bp-timeline__status"><?= htmlspecialchars($h['status']) ?></span>
                                                <span class="bp-timeline__time"><?= trkDate($h['tanggal']) ?></span>
                                            </div>
                                            <?php if ($h['keterangan']): ?>
                                                <p class="bp-timeline__note"><?= htmlspecialchars($h['keterangan']) ?></p>
                                            <?php endif; ?>
                                        </div>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php endif; ?>
                    </div>

                </div>
                <?php endforeach; ?>
            </div>

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

    /* ===== Layout ===== */
    .bp-tracking {
        padding: 24px;
        max-width: 1080px;
        margin: 0 auto;
        display: flex;
        flex-direction: column;
        gap: 18px;
    }

    /* Heading */
    .bp-tracking__heading {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 12px;
    }
    .bp-tracking__heading h1 { margin: 0 0 4px; font-size: 22px; font-weight: 700; }
    .bp-tracking__heading p  { margin: 0; font-size: 13.5px; color: #6b7280; }

    /* ===== Stat Cards ===== */
    .bp-stat-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 12px;
    }
    .bp-stat-card {
        background: #fff;
        border: 1px solid #e5e7eb;
        border-radius: 12px;
        padding: 16px 18px;
    }
    .bp-stat-card__value {
        font-size: 26px;
        font-weight: 800;
        color: #111827;
        line-height: 1;
        margin-bottom: 4px;
    }
    .bp-stat-card__label { font-size: 12.5px; color: #6b7280; font-weight: 500; }
    .bp-stat-card--pending .bp-stat-card__value { color: #b45309; }
    .bp-stat-card--shipped .bp-stat-card__value { color: #0369a1; }
    .bp-stat-card--done    .bp-stat-card__value { color: #15803d; }

    /* ===== Filter bar ===== */
    .bp-filter-bar {
        display: flex;
        align-items: center;
        gap: 10px;
        flex-wrap: wrap;
    }
    .bp-filter-bar__search {
        position: relative;
        flex: 1;
        min-width: 200px;
    }
    .bp-filter-bar__icon {
        position: absolute;
        left: 11px;
        top: 50%;
        transform: translateY(-50%);
        color: #9ca3af;
        pointer-events: none;
    }
    .bp-filter-bar__search input {
        width: 100%;
        padding: 9px 12px 9px 34px;
        border: 1px solid #d1d5db;
        border-radius: 9px;
        font-size: 13.5px;
        color: #111827;
        background: #fff;
        outline: none;
        box-sizing: border-box;
        transition: border-color 0.15s;
        font-family: inherit;
    }
    .bp-filter-bar__search input:focus { border-color: #2563eb; box-shadow: 0 0 0 3px rgba(37,99,235,.1); }

    .bp-filter-bar select {
        padding: 9px 12px;
        border: 1px solid #d1d5db;
        border-radius: 9px;
        font-size: 13px;
        color: #374151;
        background: #fff;
        outline: none;
        cursor: pointer;
        font-family: inherit;
        transition: border-color 0.15s;
    }
    .bp-filter-bar select:focus { border-color: #2563eb; }

    .bp-btn-filter {
        padding: 9px 18px;
        background: #2563eb;
        color: #fff;
        border: none;
        border-radius: 9px;
        font-size: 13px;
        font-weight: 600;
        cursor: pointer;
        font-family: inherit;
        transition: background 0.15s;
    }
    .bp-btn-filter:hover { background: #1d4ed8; }

    .bp-btn-reset {
        padding: 9px 14px;
        background: #fff;
        color: #6b7280;
        border: 1px solid #d1d5db;
        border-radius: 9px;
        font-size: 13px;
        font-weight: 600;
        cursor: pointer;
        text-decoration: none;
        transition: background 0.15s;
    }
    .bp-btn-reset:hover { background: #f3f4f6; }

    /* ===== Track meta / list ===== */
    .bp-track-meta { padding: 2px 2px 0; }
    .bp-table-count { font-size: 13px; color: #6b7280; font-weight: 500; }

    .bp-track-list {
        display: flex;
        flex-direction: column;
        gap: 14px;
    }

    .bp-track-card {
        background: #fff;
        border: 1px solid #e5e7eb;
        border-radius: 14px;
        padding: 18px 20px;
        display: flex;
        flex-direction: column;
        gap: 16px;
    }

    /* Card head */
    .bp-track-card__head {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 14px;
    }
    .bp-track-card__main { display: flex; flex-direction: column; gap: 6px; min-width: 220px; }
    .bp-track-card__no { display: flex; align-items: center; gap: 10px; flex-wrap: wrap; }

    .bp-track-card__side {
        display: flex;
        align-items: center;
        gap: 14px;
        flex-wrap: wrap;
    }
    .bp-track-card__date { display: flex; flex-direction: column; gap: 2px; text-align: right; }
    .bp-track-card__date-label { font-size: 11px; color: #9ca3af; font-weight: 600; text-transform: uppercase; letter-spacing: 0.03em; }
    .bp-track-card__date-value { font-size: 13px; color: #374151; font-weight: 600; }

    /* Shared cell helpers */
    .bp-mono {
        font-family: 'SF Mono', 'Fira Code', 'Consolas', monospace;
        font-size: 13px;
        font-weight: 700;
        color: #111827;
    }
    .bp-product-cell { display: flex; flex-direction: column; gap: 2px; }
    .bp-product-name { font-size: 13.5px; font-weight: 600; color: #111827; }
    .bp-product-sub  { font-size: 12px; color: #6b7280; }
    .bp-color-code {
        font-family: 'SF Mono', 'Fira Code', monospace;
        font-size: 11px;
        font-weight: 600;
        background: #f3f4f6;
        color: #6b7280;
        padding: 1px 6px;
        border-radius: 4px;
    }

    .bp-btn-detail {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        padding: 7px 13px;
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        font-size: 12.5px;
        font-weight: 600;
        color: #374151;
        text-decoration: none;
        background: #fff;
        white-space: nowrap;
        transition: all 0.15s;
    }
    .bp-btn-detail:hover { background: #f9fafb; border-color: #2563eb; color: #2563eb; }

    /* ===== Progress bar ===== */
    .bp-progress {
        display: flex;
        align-items: center;
        padding: 4px 4px 0;
    }
    .bp-progress__step {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 6px;
        flex-shrink: 0;
        width: 92px;
    }
    .bp-progress__dot {
        width: 22px;
        height: 22px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        background: #f3f4f6;
        border: 2px solid #e5e7eb;
        color: #fff;
        flex-shrink: 0;
    }
    .bp-progress__step--done .bp-progress__dot {
        background: #16a34a;
        border-color: #16a34a;
    }
    .bp-progress__step--active .bp-progress__dot {
        background: #2563eb;
        border-color: #2563eb;
        box-shadow: 0 0 0 4px rgba(37,99,235,.15);
    }
    .bp-progress__step--upcoming .bp-progress__dot { background: #f9fafb; }

    .bp-progress__label {
        font-size: 11px;
        font-weight: 600;
        color: #9ca3af;
        text-align: center;
        line-height: 1.25;
    }
    .bp-progress__step--done .bp-progress__label,
    .bp-progress__step--active .bp-progress__label { color: #111827; }

    .bp-progress__line {
        flex: 1;
        height: 2px;
        background: #e5e7eb;
        margin: 0 -2px;
        margin-bottom: 18px;
    }
    .bp-progress__line--done { background: #16a34a; }

    /* ===== Cancelled banner ===== */
    .bp-track-cancelled {
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 10px 14px;
        background: #fef2f2;
        color: #b91c1c;
        border-radius: 10px;
        font-size: 13px;
        font-weight: 600;
    }

    /* ===== Timeline riwayat ===== */
    .bp-track-history {
        border-top: 1px solid #f3f4f6;
        padding-top: 14px;
    }
    .bp-track-history__empty {
        margin: 0;
        font-size: 13px;
        color: #9ca3af;
        font-style: italic;
    }

    .bp-timeline {
        list-style: none;
        margin: 0;
        padding: 0;
        position: relative;
    }
    .bp-timeline__item {
        position: relative;
        padding: 0 0 18px 22px;
        border-left: 2px solid #e5e7eb;
        margin-left: 5px;
    }
    .bp-timeline__item:last-child {
        padding-bottom: 0;
        border-left-color: transparent;
    }
    .bp-timeline__dot {
        position: absolute;
        left: -7px;
        top: 1px;
        width: 12px;
        height: 12px;
        border-radius: 50%;
        background: #d1d5db;
        border: 2px solid #fff;
    }
    .bp-timeline__item--current .bp-timeline__dot {
        background: #2563eb;
        box-shadow: 0 0 0 4px rgba(37,99,235,.15);
    }
    .bp-timeline__row {
        display: flex;
        align-items: baseline;
        justify-content: space-between;
        gap: 10px;
        flex-wrap: wrap;
    }
    .bp-timeline__status {
        font-size: 13.5px;
        font-weight: 700;
        color: #111827;
    }
    .bp-timeline__item--current .bp-timeline__status { color: #2563eb; }
    .bp-timeline__time {
        font-size: 12px;
        color: #9ca3af;
        white-space: nowrap;
    }
    .bp-timeline__note {
        margin: 3px 0 0;
        font-size: 13px;
        color: #6b7280;
        line-height: 1.5;
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

    /* ===== Buttons ===== */
    .bp-btn-secondary {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 10px 18px;
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

    /* ===== Empty state ===== */
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
    }
    .bp-empty-card__icon { color: #d1d5db; }
    .bp-empty-card__title {
        margin: 0;
        font-size: 16px;
        font-weight: 700;
        color: #374151;
    }
    .bp-empty-card__sub {
        margin: 0;
        font-size: 13.5px;
        color: #9ca3af;
        max-width: 380px;
    }

    /* ===== Responsive ===== */
    @media (max-width: 768px) {
        .bp-tracking { padding: 16px; gap: 14px; }
        .bp-tracking__heading { flex-direction: column; align-items: flex-start; }
        .bp-stat-grid { grid-template-columns: repeat(2, 1fr); }
        .bp-filter-bar { flex-direction: column; align-items: stretch; }
        .bp-filter-bar__search { min-width: unset; }
        .bp-filter-bar select { width: 100%; }
        .bp-btn-filter, .bp-btn-reset { width: 100%; text-align: center; }

        .bp-track-card { padding: 16px; }
        .bp-track-card__head { flex-direction: column; }
        .bp-track-card__side { width: 100%; justify-content: space-between; }
        .bp-track-card__date { text-align: left; }

        .bp-progress { overflow-x: auto; padding-bottom: 4px; }
        .bp-progress__step { width: 78px; }
        .bp-progress__label { font-size: 10px; }
    }

    @media (max-width: 480px) {
        .bp-stat-grid { grid-template-columns: repeat(2, 1fr); }
    }
</style>

</body>
</html>