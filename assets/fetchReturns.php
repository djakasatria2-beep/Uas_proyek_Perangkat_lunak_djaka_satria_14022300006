<?php
// ============================================================
//  ThreadB2B — assets/fetchReturns.php
//  Ambil daftar retur/komplain.
//  - Buyer     : hanya retur dari pesanannya sendiri
//  - Marketing : semua retur + info buyer & pesanan
//  - Admin     : semua retur + info lengkap
//  Query params (GET):
//    status   = submitted|under_review|approved|resolved|rejected
//    id_buyer = INT  (Marketing/Admin)
//    keyword  = no_return / no_order / nama_perusahaan
//    page, per_page
// ============================================================

session_start();
include __DIR__ . '/config.php';
include __DIR__ . '/noSessionRedirect.php';
header('Content-Type: application/json; charset=utf-8');

requireMethod('GET');

$role    = $_SESSION['role'];
$idBuyer = (int)($_SESSION['id_buyer'] ?? 0);

$status      = trim($_GET['status']    ?? '');
$filterBuyer = (int)($_GET['id_buyer'] ?? 0);
$keyword     = trim($_GET['keyword']   ?? '');
$page        = max(1, (int)($_GET['page']     ?? 1));
$perPage     = min(100, max(1, (int)($_GET['per_page'] ?? 20)));
$offset      = ($page - 1) * $perPage;

$conditions = [];
$params     = [];
$types      = '';

// Batasi berdasarkan role
if ($role === 'buyer') {
    $conditions[] = 'bp.id_buyer = ?';
    $params[]     = $idBuyer;
    $types       .= 'i';
} elseif (in_array($role, ['marketing','admin']) && $filterBuyer > 0) {
    $conditions[] = 'bp.id_buyer = ?';
    $params[]     = $filterBuyer;
    $types       .= 'i';
}

$validStatuses = ['submitted','under_review','approved','resolved','rejected'];
if ($status !== '' && in_array($status, $validStatuses)) {
    $conditions[] = 'r.status = ?';
    $params[]     = $status;
    $types       .= 's';
}
if ($keyword !== '') {
    $conditions[] = '(r.no_return LIKE ? OR o.no_order LIKE ? OR bp.nama_perusahaan LIKE ?)';
    $kw = '%' . $keyword . '%';
    $params[] = $kw; $params[] = $kw; $params[] = $kw;
    $types   .= 'sss';
}

$where = count($conditions) ? 'WHERE ' . implode(' AND ', $conditions) : '';

// --- Count ---
$sqlCount = "SELECT COUNT(*) AS total
             FROM order_returns r
             JOIN orders o      ON o.id_order   = r.id_order
             JOIN buyer_profile bp ON bp.id_buyer = o.id_buyer
             $where";
$stmtCount = mysqli_prepare($conn, $sqlCount);
if ($params) mysqli_stmt_bind_param($stmtCount, $types, ...$params);
mysqli_stmt_execute($stmtCount);
$total = (int)mysqli_fetch_assoc(mysqli_stmt_get_result($stmtCount))['total'];

// --- Data ---
$sqlData = "SELECT r.id_return, r.no_return, r.alasan_kategori,
                   r.alasan, r.foto, r.respons_admin, r.status,
                   o.id_order, o.no_order, o.jenis_benang, o.status AS order_status,
                   bp.id_buyer, bp.nama_perusahaan, bp.nama_pic
            FROM order_returns r
            JOIN orders o         ON o.id_order   = r.id_order
            JOIN buyer_profile bp ON bp.id_buyer  = o.id_buyer
            $where
            ORDER BY r.id_return DESC
            LIMIT ? OFFSET ?";

$allParams = array_merge($params, [$perPage, $offset]);
$stmtData  = mysqli_prepare($conn, $sqlData);
mysqli_stmt_bind_param($stmtData, $types . 'ii', ...$allParams);
mysqli_stmt_execute($stmtData);
$result  = mysqli_stmt_get_result($stmtData);

$returns = [];
while ($row = mysqli_fetch_assoc($result)) {
    // Decode JSON array foto menjadi array PHP
    if (!empty($row['foto'])) {
        $row['foto'] = json_decode($row['foto'], true) ?? [];
    } else {
        $row['foto'] = [];
    }
    $returns[] = $row;
}

respond('success', 'Data berhasil diambil.', [
    'returns'     => $returns,
    'total'       => $total,
    'page'        => $page,
    'per_page'    => $perPage,
    'total_pages' => (int)ceil($total / $perPage),
]);