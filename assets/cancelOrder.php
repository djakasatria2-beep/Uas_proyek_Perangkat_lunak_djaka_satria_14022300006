<?php
// ============================================================
//  ThreadB2B — assets/cancelOrder.php
//  Buyer membatalkan pesanannya sendiri (hanya status pending).
//  Method : POST (JSON body)
//  Body   : { "id_order": INT }
// ============================================================

session_start();
include __DIR__ . '/config.php';
include __DIR__ . '/noSessionRedirect.php';
header('Content-Type: application/json; charset=utf-8');

requireMethod('POST');

if ($_SESSION['role'] !== 'buyer') {
    respond('error', 'Akses ditolak.');
}

$idBuyer = (int)($_SESSION['id_buyer'] ?? 0);
$data    = getJsonBody();
$idOrder = (int)($data['id_order'] ?? 0);

if ($idOrder === 0) {
    respond('error', 'id_order wajib diisi.');
}

// --- Ambil order + verifikasi kepemilikan ---
$sqlGet = "SELECT id_order, status, no_order, id_buyer
           FROM orders WHERE id_order = ? LIMIT 1";
$stmtGet = mysqli_prepare($conn, $sqlGet);
mysqli_stmt_bind_param($stmtGet, 'i', $idOrder);
mysqli_stmt_execute($stmtGet);
$orderRow = mysqli_fetch_assoc(mysqli_stmt_get_result($stmtGet));

if (!$orderRow) {
    respond('error', 'Pesanan tidak ditemukan.');
}
if ((int)$orderRow['id_buyer'] !== $idBuyer) {
    respond('error', 'Akses ditolak. Pesanan bukan milik Anda.');
}
if ($orderRow['status'] !== 'pending') {
    respond('error', "Hanya pesanan berstatus 'pending' yang dapat dibatalkan. Status saat ini: {$orderRow['status']}.");
}

// --- Batalkan ---
$sqlCancel = "UPDATE orders SET status = 'cancelled' WHERE id_order = ?";
$stmtCancel = mysqli_prepare($conn, $sqlCancel);
mysqli_stmt_bind_param($stmtCancel, 'i', $idOrder);
mysqli_stmt_execute($stmtCancel);

respond('success', 'Pesanan berhasil dibatalkan.', [
    'id_order' => $idOrder,
    'no_order' => $orderRow['no_order'],
]);