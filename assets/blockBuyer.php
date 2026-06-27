<?php
// ============================================================
//  ThreadB2B — assets/blockBuyer.php
//  Admin: blokir buyer secara manual.
//  Body (JSON):
//    id_buyer INT    (wajib)
//    alasan   STRING (opsional)
// ============================================================

session_start();
include __DIR__ . '/config.php';
include __DIR__ . '/noSessionRedirect.php';
header('Content-Type: application/json; charset=utf-8');

requireMethod('POST');

if ($_SESSION['role'] !== 'admin') {
    respond('error', 'Akses ditolak. Hanya Admin.');
}

$body    = getJsonBody();
$idBuyer = (int)($body['id_buyer'] ?? 0);

if ($idBuyer === 0) respond('error', 'id_buyer wajib diisi.');

$check = mysqli_prepare($conn,
    "SELECT id_buyer, status_verifikasi FROM buyer_profile WHERE id_buyer = ?");
mysqli_stmt_bind_param($check, 'i', $idBuyer);
mysqli_stmt_execute($check);
$buyer = mysqli_fetch_assoc(mysqli_stmt_get_result($check));

if (!$buyer) respond('error', 'Buyer tidak ditemukan.');
if ($buyer['status_verifikasi'] === 'blocked') {
    respond('error', 'Buyer sudah dalam status blocked.');
}

$upd = mysqli_prepare($conn,
    "UPDATE buyer_profile
     SET status_verifikasi = 'blocked', tanggal_diblokir = NOW()
     WHERE id_buyer = ?");
mysqli_stmt_bind_param($upd, 'i', $idBuyer);
if (!mysqli_stmt_execute($upd)) {
    respond('error', 'Gagal memblokir buyer: ' . mysqli_error($conn));
}

respond('success', 'Buyer berhasil diblokir.', [
    'id_buyer' => $idBuyer,
    'status'   => 'blocked',
]);
