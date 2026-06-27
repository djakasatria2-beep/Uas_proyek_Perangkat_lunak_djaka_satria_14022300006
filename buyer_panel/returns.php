<?php
// ============================================================
//  buyer_panel/returns.php
//  Daftar semua pengajuan return milik buyer yang login.
// ============================================================

define('REQUIRED_ROLE', 'buyer');
require_once __DIR__ . '/../assets/verifyRoleRedirect.php';
require_once __DIR__ . '/partials/config.php';

$idBuyer   = (int) ($currentBuyer['id_buyer'] ?? 0);
$pageTitle = 'Pengajuan Return';

// ------------------------------------------------------------
// Filter & search
// ------------------------------------------------------------
$filterStatus = $_GET['status'] ?? '';
$filterKat    = $_GET['kategori'] ?? '';
$search       = trim($_GET['q'] ?? '');

$allowedStatus = ['submitted', 'under_review', 'approved', 'resolved', 'rejected'];
$allowedKat    = ['deviasi_warna', 'kualitas', 'barang_rusak', 'spesifikasi_salah', 'lainnya'];

if (!in_array($filterStatus, $allowedStatus)) $filterStatus = '';
if (!in_array($filterKat, $allowedKat))       $filterKat    = '';

// ------------------------------------------------------------
// Build query
// ------------------------------------------------------------
$where  = ['o.id_buyer = ?'];
$params = [$idBuyer];
$types  = 'i';

if ($filterStatus !== '') {
    $where[]  = 'r.status = ?';
    $params[] = $filterStatus;
    $types   .= 's';
}
if ($filterKat !== '') {
    $where[]  = 'r.alasan_kategori = ?';
    $params[] = $filterKat;
    $types   .= 's';
}
if ($search !== '') {
    $like     = '%' . $search . '%';
    $where[]  = '(r.no_return LIKE ? OR o.no_order LIKE ? OR o.jenis_benang LIKE ?)';
    $params[] = $like;
    $params[] = $like;
    $params[] = $like;
    $types   .= 'sss';
}

$whereSQL = 'WHERE ' . implode(' AND ', $where);

$sql = "
    SELECT r.*,
           o.no_order, o.jenis_benang, o.ukuran_benang,
           o.kode_warna, o.nama_warna, o.status AS order_status
    FROM order_returns r
    JOIN orders o ON o.id_order = r.id_order
    $whereSQL
    ORDER BY r.id_return DESC
";

$stmt = $conn->prepare($sql);
if ($types !== 'i' || count($params) > 1) {
    $stmt->bind_param($types, ...$params);
} else {
    $stmt->bind_param($types, $idBuyer);
}
$stmt->execute();
$returns = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// ------------------------------------------------------------
// Statistik ringkas (tanpa filter)
// ------------------------------------------------------------
$statsRes = $conn->prepare("
    SELECT r.status, COUNT(*) AS cnt
    FROM order_returns r
    JOIN orders o ON o.id_order = r.id_order
    WHERE o.id_buyer = ?
    GROUP BY r.status
");
$statsRes->bind_param('i', $idBuyer);
$statsRes->execute();
$statsRaw = $statsRes->get_result()->fetch_all(MYSQLI_ASSOC);
$statsRes->close();

$stats = ['total' => 0, 'submitted' => 0, 'under_review' => 0,
          'approved' => 0, 'resolved' => 0, 'rejected' => 0];
foreach ($statsRaw as $s) {
    $stats[$s['status']] = (int) $s['cnt'];
    $stats['total']     += (int) $s['cnt'];
}

// ------------------------------------------------------------
// Helpers
// ------------------------------------------------------------
function retStatusBadge(string $s): string {
    $map = [
        'submitted'    => ['Diajukan',   'badge-pending'],
        'under_review' => ['Ditinjau',   'badge-processing'],
        'approved'     => ['Disetujui',  'badge-shipped'],
        'resolved'     => ['Selesai',    'badge-done'],
        'rejected'     => ['Ditolak',    'badge-cancelled'],
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

$justSubmitted = isset($_GET['submitted']);
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
    <main class="bp-returns">

        <!-- ===== Heading ===== -->
        <div class="bp-returns__heading">
            <div>
                <h1>Pengajuan Return</h1>
                <p>Kelola semua pengajuan return pesanan Anda.</p>
            </div>
            <a href="<?= BUYER_URL ?>/returns-new.php" class="bp-btn-primary">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                     stroke-linecap="round" stroke-linejoin="round" width="15" height="15">
                    <line x1="12" y1="5" x2="12" y2="19"></line>
                    <line x1="5" y1="12" x2="19" y2="12"></line>
                </svg>
                Ajukan Return Baru
            </a>
        </div>

        <?php if ($justSubmitted): ?>
            <div class="bp-alert bp-alert--success">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                     stroke-linecap="round" stroke-linejoin="round" width="18" height="18">
                    <polyline points="20 6 9 17 4 12"></polyline>
                </svg>
                <span>Return berhasil diajukan dan sedang dalam proses peninjauan.</span>
            </div>
        <?php endif; ?>

        <!-- ===== Stat Cards ===== -->
        <div class="bp-stat-grid">
            <div class="bp-stat-card">
                <div class="bp-stat-card__value"><?= $stats['total'] ?></div>
                <div class="bp-stat-card__label">Total Return</div>
            </div>
            <div class="bp-stat-card bp-stat-card--pending">
                <div class="bp-stat-card__value"><?= $stats['submitted'] + $stats['under_review'] ?></div>
                <div class="bp-stat-card__label">Diproses</div>
            </div>
            <div class="bp-stat-card bp-stat-card--done">
                <div class="bp-stat-card__value"><?= $stats['approved'] + $stats['resolved'] ?></div>
                <div class="bp-stat-card__label">Disetujui</div>
            </div>
            <div class="bp-stat-card bp-stat-card--cancelled">
                <div class="bp-stat-card__value"><?= $stats['rejected'] ?></div>
                <div class="bp-stat-card__label">Ditolak</div>
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
                <input type="text" name="q" placeholder="Cari no. return, no. order, jenis benang…"
                       value="<?= htmlspecialchars($search) ?>">
            </div>

            <select name="status">
                <option value="">Semua Status</option>
                <option value="submitted"    <?= $filterStatus === 'submitted'    ? 'selected' : '' ?>>Diajukan</option>
                <option value="under_review" <?= $filterStatus === 'under_review' ? 'selected' : '' ?>>Ditinjau</option>
                <option value="approved"     <?= $filterStatus === 'approved'     ? 'selected' : '' ?>>Disetujui</option>
                <option value="resolved"     <?= $filterStatus === 'resolved'     ? 'selected' : '' ?>>Selesai</option>
                <option value="rejected"     <?= $filterStatus === 'rejected'     ? 'selected' : '' ?>>Ditolak</option>
            </select>

            <select name="kategori">
                <option value="">Semua Kategori</option>
                <option value="deviasi_warna"     <?= $filterKat === 'deviasi_warna'     ? 'selected' : '' ?>>Deviasi Warna</option>
                <option value="kualitas"          <?= $filterKat === 'kualitas'          ? 'selected' : '' ?>>Kualitas</option>
                <option value="barang_rusak"      <?= $filterKat === 'barang_rusak'      ? 'selected' : '' ?>>Barang Rusak</option>
                <option value="spesifikasi_salah" <?= $filterKat === 'spesifikasi_salah' ? 'selected' : '' ?>>Spesifikasi Salah</option>
                <option value="lainnya"           <?= $filterKat === 'lainnya'           ? 'selected' : '' ?>>Lainnya</option>
            </select>

            <button type="submit" class="bp-btn-filter">Terapkan</button>

            <?php if ($filterStatus || $filterKat || $search): ?>
                <a href="<?= BUYER_URL ?>/returns.php" class="bp-btn-reset">Reset</a>
            <?php endif; ?>
        </form>

        <!-- ===== Tabel / Empty ===== -->
        <?php if (empty($returns)): ?>
            <div class="bp-empty-card">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"
                     stroke-linecap="round" stroke-linejoin="round" width="48" height="48" class="bp-empty-card__icon">
                    <polyline points="1 4 1 10 7 10"></polyline>
                    <path d="M3.51 15a9 9 0 1 0 .49-3.51"></path>
                </svg>
                <p class="bp-empty-card__title">
                    <?= ($filterStatus || $filterKat || $search)
                        ? 'Tidak ada return yang sesuai filter.'
                        : 'Belum ada pengajuan return.' ?>
                </p>
                <p class="bp-empty-card__sub">
                    <?= ($filterStatus || $filterKat || $search)
                        ? 'Coba ubah atau reset filter pencarian.'
                        : 'Pesanan yang sudah dikirim atau selesai dapat diajukan return.' ?>
                </p>
                <?php if (!$filterStatus && !$filterKat && !$search): ?>
                    <a href="<?= BUYER_URL ?>/orders.php" class="bp-btn-secondary">Lihat Pesanan Saya</a>
                <?php endif; ?>
            </div>

        <?php else: ?>
            <div class="bp-card bp-table-card">
                <div class="bp-table-meta">
                    <span class="bp-table-count"><?= count($returns) ?> pengajuan ditemukan</span>
                </div>
                <div class="bp-table-wrap">
                    <table class="bp-table">
                        <thead>
                            <tr>
                                <th>No. Return</th>
                                <th>Pesanan</th>
                                <th>Produk</th>
                                <th>Kategori</th>
                                <th>Status Return</th>
                                <th>Respons Admin</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($returns as $ret): ?>
                                <tr>
                                    <!-- No. Return -->
                                    <td data-label="No. Return">
                                        <span class="bp-mono"><?= htmlspecialchars($ret['no_return']) ?></span>
                                    </td>

                                    <!-- No. Order -->
                                    <td data-label="Pesanan">
                                        <a href="<?= BUYER_URL ?>/orders-detail.php?id=<?= $ret['id_order'] ?>"
                                           class="bp-link">
                                            <?= htmlspecialchars($ret['no_order']) ?>
                                        </a>
                                    </td>

                                    <!-- Produk -->
                                    <td data-label="Produk">
                                        <div class="bp-product-cell">
                                            <span class="bp-product-name"><?= htmlspecialchars($ret['jenis_benang']) ?></span>
                                            <?php if ($ret['ukuran_benang']): ?>
                                                <span class="bp-product-sub"><?= htmlspecialchars($ret['ukuran_benang']) ?></span>
                                            <?php endif; ?>
                                            <?php if ($ret['kode_warna'] || $ret['nama_warna']): ?>
                                                <span class="bp-product-sub">
                                                    <?php if ($ret['kode_warna']): ?>
                                                        <span class="bp-color-code"><?= htmlspecialchars($ret['kode_warna']) ?></span>
                                                    <?php endif; ?>
                                                    <?= htmlspecialchars($ret['nama_warna'] ?? '') ?>
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                    </td>

                                    <!-- Kategori -->
                                    <td data-label="Kategori">
                                        <span class="bp-kat-chip"><?= retKatLabel($ret['alasan_kategori']) ?></span>
                                    </td>

                                    <!-- Status -->
                                    <td data-label="Status Return">
                                        <?= retStatusBadge($ret['status']) ?>
                                    </td>

                                    <!-- Respons Admin -->
                                    <td data-label="Respons Admin">
                                        <?php if ($ret['respons_admin']): ?>
                                            <span class="bp-respons-snippet"
                                                  title="<?= htmlspecialchars($ret['respons_admin']) ?>">
                                                <?= htmlspecialchars(mb_strimwidth($ret['respons_admin'], 0, 45, '…')) ?>
                                            </span>
                                        <?php else: ?>
                                            <span class="bp-no-respons">—</span>
                                        <?php endif; ?>
                                    </td>

                                    <!-- Aksi -->
                                    <td class="bp-table__action">
                                        <a href="<?= BUYER_URL ?>/returns-detail.php?id=<?= $ret['id_return'] ?>"
                                           class="bp-btn-detail">
                                            Detail
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                 stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                                 width="13" height="13">
                                                <polyline points="9 18 15 12 9 6"></polyline>
                                            </svg>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
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
    .bp-returns {
        padding: 24px;
        max-width: 1080px;
        margin: 0 auto;
        display: flex;
        flex-direction: column;
        gap: 18px;
    }

    /* Heading */
    .bp-returns__heading {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 12px;
    }
    .bp-returns__heading h1 { margin: 0 0 4px; font-size: 22px; font-weight: 700; }
    .bp-returns__heading p  { margin: 0; font-size: 13.5px; color: #6b7280; }

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
    .bp-alert--success { background: #f0fdf4; color: #166534; }
    .bp-alert svg { flex-shrink: 0; margin-top: 1px; }

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
    .bp-stat-card--pending  .bp-stat-card__value { color: #b45309; }
    .bp-stat-card--done     .bp-stat-card__value { color: #15803d; }
    .bp-stat-card--cancelled .bp-stat-card__value { color: #b91c1c; }

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

    /* ===== Card & Table ===== */
    .bp-card {
        background: #fff;
        border: 1px solid #e5e7eb;
        border-radius: 14px;
    }
    .bp-table-card { overflow: hidden; }
    .bp-table-meta {
        padding: 14px 20px 10px;
        border-bottom: 1px solid #f3f4f6;
    }
    .bp-table-count { font-size: 13px; color: #6b7280; font-weight: 500; }

    .bp-table-wrap { overflow-x: auto; }
    .bp-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 13.5px;
    }
    .bp-table th {
        text-align: left;
        padding: 10px 14px;
        font-size: 11.5px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        color: #6b7280;
        border-bottom: 1px solid #e5e7eb;
        white-space: nowrap;
        background: #fafafa;
    }
    .bp-table td {
        padding: 13px 14px;
        border-bottom: 1px solid #f3f4f6;
        vertical-align: middle;
    }
    .bp-table tbody tr:last-child td { border-bottom: none; }
    .bp-table tbody tr:hover { background: #fafafa; }
    .bp-table__action { text-align: right; }

    /* Cell helpers */
    .bp-mono {
        font-family: 'SF Mono', 'Fira Code', 'Consolas', monospace;
        font-size: 12.5px;
        font-weight: 600;
        color: #374151;
    }
    .bp-link {
        font-size: 13px;
        font-weight: 600;
        color: #2563eb;
        text-decoration: none;
        font-family: 'SF Mono', 'Fira Code', monospace;
    }
    .bp-link:hover { text-decoration: underline; }

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
        margin-right: 4px;
    }

    .bp-kat-chip {
        display: inline-block;
        padding: 3px 10px;
        border-radius: 99px;
        font-size: 12px;
        font-weight: 600;
        background: #f3f4f6;
        color: #374151;
        white-space: nowrap;
    }

    .bp-respons-snippet { font-size: 13px; color: #374151; }
    .bp-no-respons { color: #d1d5db; font-size: 16px; }

    .bp-btn-detail {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        padding: 6px 12px;
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
    .bp-btn-primary {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        padding: 10px 18px;
        background: #2563eb;
        color: #fff;
        border: none;
        border-radius: 10px;
        font-size: 13.5px;
        font-weight: 600;
        cursor: pointer;
        text-decoration: none;
        transition: background 0.15s;
        white-space: nowrap;
    }
    .bp-btn-primary:hover { background: #1d4ed8; }

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
        .bp-returns { padding: 16px; gap: 14px; }
        .bp-returns__heading { flex-direction: column; align-items: flex-start; }
        .bp-stat-grid { grid-template-columns: repeat(2, 1fr); }
        .bp-filter-bar { flex-direction: column; align-items: stretch; }
        .bp-filter-bar__search { min-width: unset; }
        .bp-filter-bar select { width: 100%; }
        .bp-btn-filter, .bp-btn-reset { width: 100%; text-align: center; }

        /* Responsive table */
        .bp-table thead { display: none; }
        .bp-table, .bp-table tbody, .bp-table tr, .bp-table td { display: block; width: 100%; }
        .bp-table tr {
            border: 1px solid #e5e7eb;
            border-radius: 10px;
            margin: 0 0 10px;
            padding: 4px 2px;
        }
        .bp-table td {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 10px;
            border-bottom: none;
            padding: 8px 12px;
        }
        .bp-table td[data-label]:not([data-label=""])::before {
            content: attr(data-label);
            font-size: 11.5px;
            font-weight: 700;
            color: #9ca3af;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            flex-shrink: 0;
        }
        .bp-table__action { justify-content: flex-end; }
        .bp-product-cell { align-items: flex-end; text-align: right; }
    }

    @media (max-width: 480px) {
        .bp-stat-grid { grid-template-columns: repeat(2, 1fr); }
    }
</style>

</body>
</html>