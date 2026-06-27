<?php
// ============================================================
//  ThreadB2B — assets/fetchAuditLog.php
//  Ambil audit log aktivitas sistem (Admin only).
//
//  Karena skema belum punya tabel audit_log, file ini
//  meng-aggregate event dari tabel yang ada:
//    - Login terakhir  → tidak tersimpan di DB (bisa dari session log)
//    - Verifikasi buyer (diverifikasi_oleh + tanggal dari buyer_profile)
//    - Perubahan status order (dari tracking)
//    - Blokir buyer (tanggal_diblokir)
//
//  Query params (GET):
//    type      = verify|block|tracking|all  (default: all)
//    date_from = YYYY-MM-DD
//    date_to   = YYYY-MM-DD
//    page, per_page
// ============================================================

session_start();
include __DIR__ . '/config.php';
include __DIR__ . '/noSessionRedirect.php';
header('Content-Type: application/json; charset=utf-8');

requireMethod('GET');

if ($_SESSION['role'] !== 'admin') {
    respond('error', 'Akses ditolak. Hanya Admin.');
}

$type     = trim($_GET['type']      ?? 'all');
$dateFrom = trim($_GET['date_from'] ?? '');
$dateTo   = trim($_GET['date_to']   ?? '');
$page     = max(1, (int)($_GET['page']     ?? 1));
$perPage  = min(100, max(1, (int)($_GET['per_page'] ?? 30)));
$offset   = ($page - 1) * $perPage;

$logs = [];

// --- Verifikasi / reject buyer ---
if (in_array($type, ['all', 'verify'])) {
    $sql = "SELECT 'verify_buyer' AS type,
                   CONCAT('Buyer ', bp.nama_perusahaan, ' → ', bp.status_verifikasi) AS keterangan,
                   u.email AS dilakukan_oleh,
                   bp.id_buyer AS referensi_id,
                   NULL AS tanggal
            FROM buyer_profile bp
            LEFT JOIN users u ON u.id_user = bp.diverifikasi_oleh
            WHERE bp.status_verifikasi IN ('approved','rejected')
              AND bp.diverifikasi_oleh IS NOT NULL";
    // Tidak ada kolom tanggal verifikasi di schema, diisi NULL
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    while ($r = mysqli_fetch_assoc($res)) $logs[] = $r;
}

// --- Blokir buyer ---
if (in_array($type, ['all', 'block'])) {
    $sql = "SELECT 'block_buyer' AS type,
                   CONCAT('Buyer ', bp.nama_perusahaan, ' diblokir') AS keterangan,
                   'system/admin' AS dilakukan_oleh,
                   bp.id_buyer AS referensi_id,
                   bp.tanggal_diblokir AS tanggal
            FROM buyer_profile bp
            WHERE bp.status_verifikasi = 'blocked'
              AND bp.tanggal_diblokir IS NOT NULL";
    $cond = [];
    $params = [];
    $types  = '';
    if ($dateFrom) { $cond[] = 'bp.tanggal_diblokir >= ?'; $params[] = $dateFrom; $types .= 's'; }
    if ($dateTo)   { $cond[] = 'bp.tanggal_diblokir <= ?'; $params[] = $dateTo . ' 23:59:59'; $types .= 's'; }
    if ($cond) $sql .= ' AND ' . implode(' AND ', $cond);

    $stmt = mysqli_prepare($conn, $sql);
    if ($params) mysqli_stmt_bind_param($stmt, $types, ...$params);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    while ($r = mysqli_fetch_assoc($res)) $logs[] = $r;
}

// --- Perubahan status tracking (milestone) ---
if (in_array($type, ['all', 'tracking'])) {
    $sql = "SELECT 'tracking' AS type,
                   CONCAT('Order ', o.no_order, ' — ', t.status) AS keterangan,
                   u.email AS dilakukan_oleh,
                   t.id_order AS referensi_id,
                   t.tanggal AS tanggal
            FROM tracking t
            JOIN orders o ON o.id_order = t.id_order
            JOIN users  u ON u.id_user  = t.updated_by";
    $cond = [];
    $params = [];
    $types  = '';
    if ($dateFrom) { $cond[] = 't.tanggal >= ?'; $params[] = $dateFrom; $types .= 's'; }
    if ($dateTo)   { $cond[] = 't.tanggal <= ?'; $params[] = $dateTo . ' 23:59:59'; $types .= 's'; }
    if ($cond) $sql .= ' WHERE ' . implode(' AND ', $cond);
    $sql .= ' ORDER BY t.tanggal DESC';

    $stmt = mysqli_prepare($conn, $sql);
    if ($params) mysqli_stmt_bind_param($stmt, $types, ...$params);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    while ($r = mysqli_fetch_assoc($res)) $logs[] = $r;
}

// Sort semua log by tanggal DESC (null paling bawah)
usort($logs, function ($a, $b) {
    if ($a['tanggal'] === null && $b['tanggal'] === null) return 0;
    if ($a['tanggal'] === null) return 1;
    if ($b['tanggal'] === null) return -1;
    return strcmp($b['tanggal'], $a['tanggal']);
});

$total  = count($logs);
$paged  = array_slice($logs, $offset, $perPage);

respond('success', 'Audit log berhasil diambil.', [
    'logs'        => $paged,
    'total'       => $total,
    'page'        => $page,
    'per_page'    => $perPage,
    'total_pages' => (int)ceil($total / $perPage),
]);
