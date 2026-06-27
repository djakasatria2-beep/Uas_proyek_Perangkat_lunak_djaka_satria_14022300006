<?php
// ============================================================
//  ThreadB2B — assets/addReturnResponse.php
//  Marketing / Admin simpan / update respons tertulis untuk retur.
//  Body (JSON):
//    id_return     INT    (wajib)
//    respons_admin STRING (wajib)
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

$body         = getJsonBody();
$idReturn     = (int)($body['id_return'] ?? 0);
$responsAdmin = trim($body['respons_admin'] ?? '');

if ($idReturn === 0)    respond('error', 'id_return wajib diisi.');
if ($responsAdmin === '') respond('error', 'respons_admin tidak boleh kosong.');

// Pastikan retur ada
$check = mysqli_prepare($conn,
    "SELECT id_return FROM order_returns WHERE id_return = ?");
mysqli_stmt_bind_param($check, 'i', $idReturn);
mysqli_stmt_execute($check);
if (!mysqli_fetch_assoc(mysqli_stmt_get_result($check))) {
    respond('error', 'Data retur tidak ditemukan.');
}

$upd = mysqli_prepare($conn,
    "UPDATE order_returns SET respons_admin = ? WHERE id_return = ?");
mysqli_stmt_bind_param($upd, 'si', $responsAdmin, $idReturn);

if (!mysqli_stmt_execute($upd)) {
    respond('error', 'Gagal menyimpan respons: ' . mysqli_error($conn));
}

respond('success', 'Respons berhasil disimpan.', [
    'id_return'    => $idReturn,
    'respons_admin' => $responsAdmin,
]);
