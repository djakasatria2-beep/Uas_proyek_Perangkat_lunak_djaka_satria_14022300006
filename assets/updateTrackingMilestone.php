<?php
// ============================================================
//  ThreadB2B — assets/updateTrackingMilestone.php
//  Marketing / Admin update keterangan milestone yang sudah ada.
//  Body (JSON):
//    id_tracking INT    (wajib)
//    status      STRING (opsional)
//    keterangan  STRING (opsional)
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
$idTracking = (int)($body['id_tracking'] ?? 0);
$status     = isset($body['status'])     ? trim($body['status'])     : null;
$keterangan = isset($body['keterangan']) ? trim($body['keterangan']) : null;

if ($idTracking === 0) respond('error', 'id_tracking wajib diisi.');
if ($status === null && $keterangan === null) {
    respond('error', 'Minimal satu field (status atau keterangan) harus dikirim.');
}

// Pastikan milestone ada
$check = mysqli_prepare($conn,
    "SELECT id_tracking FROM tracking WHERE id_tracking = ?");
mysqli_stmt_bind_param($check, 'i', $idTracking);
mysqli_stmt_execute($check);
if (!mysqli_fetch_assoc(mysqli_stmt_get_result($check))) {
    respond('error', 'Milestone tidak ditemukan.');
}

// Build query dinamis
$setClauses = ['updated_by = ?'];
$params     = [$idUser];
$types      = 'i';

if ($status !== null) {
    $setClauses[] = 'status = ?';
    $params[]     = $status;
    $types       .= 's';
}
if ($keterangan !== null) {
    $setClauses[] = 'keterangan = ?';
    $params[]     = $keterangan;
    $types       .= 's';
}

$params[] = $idTracking;
$types   .= 'i';

$sql  = 'UPDATE tracking SET ' . implode(', ', $setClauses) . ' WHERE id_tracking = ?';
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, $types, ...$params);

if (!mysqli_stmt_execute($stmt)) {
    respond('error', 'Gagal memperbarui milestone: ' . mysqli_error($conn));
}

respond('success', 'Milestone berhasil diperbarui.', [
    'id_tracking' => $idTracking,
]);
