<?php
// ============================================================
//  ThreadB2B — assets/addTrackingMilestone.php
//  Marketing / Admin tambah milestone tracking baru.
//  Body (JSON):
//    id_order   INT    (wajib)
//    status     STRING (wajib) — label milestone, mis. "Pesanan Diproses"
//    keterangan STRING (opsional) — detail tambahan
// ============================================================

session_start();
include __DIR__ . '/config.php';
include __DIR__ . '/noSessionRedirect.php';
header('Content-Type: application/json; charset=utf-8');

requireMethod('POST');

$role   = $_SESSION['role'];
$idUser = (int)$_SESSION['user_id'];

if (!in_array($role, ['marketing', 'admin'])) {
    respond('error', 'Akses ditolak. Hanya Marketing dan Admin.');
}

$body       = getJsonBody();
$idOrder    = (int)($body['id_order'] ?? 0);
$status     = trim($body['status'] ?? '');
$keterangan = trim($body['keterangan'] ?? '');

if ($idOrder === 0) respond('error', 'id_order wajib diisi.');
if ($status === '')  respond('error', 'status / label milestone wajib diisi.');

// Pastikan order ada
$check = mysqli_prepare($conn, "SELECT id_order FROM orders WHERE id_order = ?");
mysqli_stmt_bind_param($check, 'i', $idOrder);
mysqli_stmt_execute($check);
if (!mysqli_fetch_assoc(mysqli_stmt_get_result($check))) {
    respond('error', 'Order tidak ditemukan.');
}

$ins = mysqli_prepare($conn,
    "INSERT INTO tracking (id_order, status, keterangan, updated_by, tanggal)
     VALUES (?, ?, ?, ?, NOW())");
mysqli_stmt_bind_param($ins, 'issi', $idOrder, $status, $keterangan, $idUser);

if (!mysqli_stmt_execute($ins)) {
    respond('error', 'Gagal menambah milestone: ' . mysqli_error($conn));
}

$idTracking = (int)mysqli_insert_id($conn);

respond('success', 'Milestone berhasil ditambahkan.', [
    'id_tracking' => $idTracking,
    'id_order'    => $idOrder,
    'status'      => $status,
    'keterangan'  => $keterangan,
]);
