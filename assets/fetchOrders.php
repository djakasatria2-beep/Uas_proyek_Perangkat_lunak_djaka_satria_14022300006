<?php
// ============================================================
//  ThreadB2B — assets/fetchOrders.php
//  Ambil daftar pesanan (JSON). Hasil difilter berdasarkan
//  role yang sedang login:
//    - Buyer     : hanya pesanan miliknya sendiri
//    - Marketing : semua pesanan + info nama perusahaan buyer
//    - Admin     : semua pesanan + info buyer
//  Query params (GET):
//    status     = pending|processing|shipped|done|cancelled
//    id_buyer   = INT  (Admin/Marketing saja)
//    dari       = YYYY-MM-DD
//    sampai     = YYYY-MM-DD
//    keyword    = pencarian no_order / nama perusahaan
//    page       = INT (default 1)
//    per_page   = INT (default 20, max 100)
// ============================================================

session_start();
include __DIR__ . '/config.php';
include __DIR__ . '/noSessionRedirect.php';
header('Content-Type: application/json; charset=utf-8');

requireMethod('GET');

$role    = $_SESSION['role'];
$userId  = (int)$_SESSION['user_id'];
$idBuyer = (int)($_SESSION['id_buyer'] ?? 0);

// --- Param filter ---
$status    = $_GET['status']   ?? '';
$filterBuyer = (int)($_GET['id_buyer'] ?? 0);
$dari      = $_GET['dari']     ?? '';
$sampai    = $_GET['sampai']   ?? '';
$keyword   = trim($_GET['keyword'] ?? '');
$page      = max(1, (int)($_GET['page']     ?? 1));
$perPage   = min(100, max(1, (int)($_GET['per_page'] ?? 20)));
$offset    = ($page - 1) * $perPage;

// --- Build query ---
$conditions = [];
$params     = [];
$types      = '';

// Role filter: Buyer hanya lihat milik sendiri
if ($role === 'buyer') {
    $conditions[] = 'o.id_buyer = ?';
    $params[]     = $idBuyer;
    $types       .= 'i';
} elseif (($role === 'marketing' || $role === 'admin') && $filterBuyer > 0) {
    $conditions[] = 'o.id_buyer = ?';
    $params[]     = $filterBuyer;
    $types       .= 'i';
}

if ($status !== '' && in_array($status, ['pending','processing','shipped','done','cancelled'])) {
    $conditions[] = 'o.status = ?';
    $params[]     = $status;
    $types       .= 's';
}
if ($dari !== '') {
    $conditions[] = 'o.tanggal >= ?';
    $params[]     = $dari;
    $types       .= 's';
}
if ($sampai !== '') {
    $conditions[] = 'o.tanggal <= ?';
    $params[]     = $sampai;
    $types       .= 's';
}
if ($keyword !== '') {
    $conditions[] = '(o.no_order LIKE ? OR bp.nama_perusahaan LIKE ?)';
    $kw           = '%' . $keyword . '%';
    $params[]     = $kw;
    $params[]     = $kw;
    $types       .= 'ss';
}

$where = count($conditions) ? 'WHERE ' . implode(' AND ', $conditions) : '';

// --- Count total ---
$sqlCount = "SELECT COUNT(*) AS total
             FROM orders o
             JOIN buyer_profile bp ON bp.id_buyer = o.id_buyer
             $where";
$stmtCount = mysqli_prepare($conn, $sqlCount);
if ($params) {
    mysqli_stmt_bind_param($stmtCount, $types, ...$params);
}
mysqli_stmt_execute($stmtCount);
$totalRow = mysqli_fetch_assoc(mysqli_stmt_get_result($stmtCount));
$total    = (int)$totalRow['total'];

// --- Ambil data ---
$sqlData = "SELECT o.id_order, o.no_order, o.kode_warna, o.nama_warna,
                   o.jenis_benang, o.ukuran_benang, o.qty, o.harga_benang,
                   o.tanggal, o.status, o.catatan,
                   bp.nama_perusahaan, bp.nama_pic, bp.id_buyer
            FROM orders o
            JOIN buyer_profile bp ON bp.id_buyer = o.id_buyer
            $where
            ORDER BY o.id_order DESC
            LIMIT ? OFFSET ?";

$allParams = array_merge($params, [$perPage, $offset]);
$allTypes  = $types . 'ii';
$stmtData  = mysqli_prepare($conn, $sqlData);
mysqli_stmt_bind_param($stmtData, $allTypes, ...$allParams);
mysqli_stmt_execute($stmtData);
$result = mysqli_stmt_get_result($stmtData);

$orders = [];
while ($row = mysqli_fetch_assoc($result)) {
    $orders[] = $row;
}

respond('success', 'Data berhasil diambil.', [
    'orders'      => $orders,
    'total'       => $total,
    'page'        => $page,
    'per_page'    => $perPage,
    'total_pages' => (int)ceil($total / $perPage),
]);