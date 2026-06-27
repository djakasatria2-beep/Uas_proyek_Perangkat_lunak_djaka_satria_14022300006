<?php
// ============================================================
//  ThreadB2B — assets/updateReturnStatus.php
//  Marketing / Admin update status retur.
//  Body (JSON):
//    id_return INT    (wajib)
//    status    STRING (wajib): under_review|approved|resolved|rejected
// ============================================================

session_start();
include __DIR__ . '/config.php';
include __DIR__ . '/noSessionRedirect.php';
header('Content-Type: application/json; charset=utf-8');

requireMethod('POST');

$role = $_SESSION['role'];
if (!in_array($role, ['marketing', 'admin'])) {
    respond('error', 'Akses ditolak. Hanya Marketing dan Admin.');
}

$body     = getJsonBody();
$idReturn = (int)($body['id_return'] ?? 0);
$status   = trim($body['status'] ?? '');

if ($idReturn === 0) respond('error', 'id_return wajib diisi.');

$validStatuses = ['under_review', 'approved', 'resolved', 'rejected'];
if (!in_array($status, $validStatuses)) {
    respond('error', 'Status tidak valid. Gunakan: ' . implode(', ', $validStatuses));
}

// Pastikan retur ada
$check = mysqli_prepare($conn,
    "SELECT id_return, status FROM order_returns WHERE id_return = ?");
mysqli_stmt_bind_param($check, 'i', $idReturn);
mysqli_stmt_execute($check);
$existing = mysqli_fetch_assoc(mysqli_stmt_get_result($check));

if (!$existing) respond('error', 'Data retur tidak ditemukan.');
if ($existing['status'] === 'resolved' || $existing['status'] === 'rejected') {
    respond('error', 'Retur yang sudah resolved atau rejected tidak dapat diubah statusnya.');
}

$upd = mysqli_prepare($conn,
    "UPDATE order_returns SET status = ? WHERE id_return = ?");
mysqli_stmt_bind_param($upd, 'si', $status, $idReturn);

if (!mysqli_stmt_execute($upd)) {
    respond('error', 'Gagal memperbarui status: ' . mysqli_error($conn));
}

respond('success', 'Status retur berhasil diperbarui.', [
    'id_return'  => $idReturn,
    'status_baru' => $status,
]);
