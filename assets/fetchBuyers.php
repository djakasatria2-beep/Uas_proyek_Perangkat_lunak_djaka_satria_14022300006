<?php
// ============================================================
//  ThreadB2B — assets/fetchBuyers.php
//  Ambil daftar buyer.
//  - Admin     : semua kolom termasuk tenor & riwayat blokir
//  - Marketing : kolom terbatas (tanpa data keuangan)
//  Query params (GET):
//    status   = pending|approved|rejected|blocked
//    keyword  = nama_perusahaan / nama_pic / email
//    page, per_page
// ============================================================

session_start();
include __DIR__ . '/config.php';
include __DIR__ . '/noSessionRedirect.php';
header('Content-Type: application/json; charset=utf-8');

requireMethod('GET');

$role = $_SESSION['role'];
if (!in_array($role, ['marketing', 'admin'])) {
    respond('error', 'Akses ditolak.');
}

$status  = trim($_GET['status']  ?? '');
$keyword = trim($_GET['keyword'] ?? '');
$page    = max(1, (int)($_GET['page']     ?? 1));
$perPage = min(100, max(1, (int)($_GET['per_page'] ?? 20)));
$offset  = ($page - 1) * $perPage;

$conditions = [];
$params     = [];
$types      = '';

$validStatuses = ['pending', 'approved', 'rejected', 'blocked'];
if ($status !== '' && in_array($status, $validStatuses)) {
    $conditions[] = 'bp.status_verifikasi = ?';
    $params[]     = $status;
    $types       .= 's';
}
if ($keyword !== '') {
    $conditions[] = '(bp.nama_perusahaan LIKE ? OR bp.nama_pic LIKE ? OR u.email LIKE ?)';
    $kw = '%' . $keyword . '%';
    $params[] = $kw; $params[] = $kw; $params[] = $kw;
    $types   .= 'sss';
}

$where = count($conditions) ? 'WHERE ' . implode(' AND ', $conditions) : '';

// Count
$sqlCount = "SELECT COUNT(*) AS total
             FROM buyer_profile bp
             JOIN users u ON u.id_user = bp.id_user
             $where";
$stmtCount = mysqli_prepare($conn, $sqlCount);
if ($params) mysqli_stmt_bind_param($stmtCount, $types, ...$params);
mysqli_stmt_execute($stmtCount);
$total = (int)mysqli_fetch_assoc(mysqli_stmt_get_result($stmtCount))['total'];

// Kolom berdasarkan role
$extraCols = $role === 'admin'
    ? ', bp.tenor_hari, bp.tanggal_diblokir, bp.diverifikasi_oleh'
    : '';

$sqlData = "SELECT bp.id_buyer, bp.nama_perusahaan, bp.nama_pic,
                   bp.no_whatsapp, bp.negara, bp.status_verifikasi,
                   u.email, u.created_at
                   $extraCols
            FROM buyer_profile bp
            JOIN users u ON u.id_user = bp.id_user
            $where
            ORDER BY bp.id_buyer DESC
            LIMIT ? OFFSET ?";

$allParams = array_merge($params, [$perPage, $offset]);
$stmtData  = mysqli_prepare($conn, $sqlData);
mysqli_stmt_bind_param($stmtData, $types . 'ii', ...$allParams);
mysqli_stmt_execute($stmtData);
$result = mysqli_stmt_get_result($stmtData);

$buyers = [];
while ($row = mysqli_fetch_assoc($result)) {
    $buyers[] = $row;
}

respond('success', 'Data berhasil diambil.', [
    'buyers'      => $buyers,
    'total'       => $total,
    'page'        => $page,
    'per_page'    => $perPage,
    'total_pages' => (int)ceil($total / $perPage),
]);
