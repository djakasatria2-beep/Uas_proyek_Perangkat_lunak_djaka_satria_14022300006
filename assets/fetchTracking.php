<?php
// ============================================================
//  ThreadB2B — assets/fetchTracking.php
//  Ambil semua milestone tracking suatu order.
//  Query params (GET):
//    id_order  = INT     (prioritas)
//    no_order  = STRING
// ============================================================

session_start();
include __DIR__ . '/config.php';
include __DIR__ . '/noSessionRedirect.php';
header('Content-Type: application/json; charset=utf-8');

requireMethod('GET');

$role    = $_SESSION['role'];
$idBuyer = (int)($_SESSION['id_buyer'] ?? 0);

$idOrder = (int)($_GET['id_order'] ?? 0);
$noOrder = trim($_GET['no_order'] ?? '');

if ($idOrder === 0 && $noOrder === '') {
    respond('error', 'Parameter id_order atau no_order wajib diisi.');
}

// Resolve id_order dari no_order jika perlu, sekaligus validasi akses buyer
$sqlOrder = "SELECT o.id_order, o.id_buyer
             FROM orders o
             WHERE " . ($idOrder > 0 ? 'o.id_order = ?' : 'o.no_order = ?');
$stmtOrder = mysqli_prepare($conn, $sqlOrder);
if ($idOrder > 0) {
    mysqli_stmt_bind_param($stmtOrder, 'i', $idOrder);
} else {
    mysqli_stmt_bind_param($stmtOrder, 's', $noOrder);
}
mysqli_stmt_execute($stmtOrder);
$order = mysqli_fetch_assoc(mysqli_stmt_get_result($stmtOrder));

if (!$order) respond('error', 'Order tidak ditemukan.');
if ($role === 'buyer' && (int)$order['id_buyer'] !== $idBuyer) {
    respond('error', 'Akses ditolak.');
}

$resolvedId = (int)$order['id_order'];

// Ambil milestones
$sql = "SELECT t.id_tracking, t.status, t.keterangan, t.tanggal,
               u.id_user, u.role AS updated_by_role,
               COALESCE(bp.nama_pic, u.email) AS updated_by_name
        FROM tracking t
        JOIN users u ON u.id_user = t.updated_by
        LEFT JOIN buyer_profile bp ON bp.id_user = u.id_user
        WHERE t.id_order = ?
        ORDER BY t.tanggal ASC";

$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, 'i', $resolvedId);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

$milestones = [];
while ($row = mysqli_fetch_assoc($result)) {
    $milestones[] = $row;
}

respond('success', 'Data tracking berhasil diambil.', [
    'id_order'   => $resolvedId,
    'milestones' => $milestones,
    'total'      => count($milestones),
]);
