<?php
// ============================================================
//  ThreadB2B — assets/updateOrderStatus.php
//  Update status pesanan. Hanya Marketing dan Admin.
//  Catat siapa yang mengubah di tabel tracking.
//  Method : POST (JSON body)
//  Body   : { "id_order": INT, "status": "...", "keterangan": "..." }
//  Status valid: processing | shipped | done | cancelled
// ============================================================

session_start();
include __DIR__ . '/config.php';
include __DIR__ . '/noSessionRedirect.php';
header('Content-Type: application/json; charset=utf-8');

requireMethod('POST');

$role   = $_SESSION['role'];
$userId = (int)$_SESSION['user_id'];

if (!in_array($role, ['marketing', 'admin'])) {
    respond('error', 'Akses ditolak. Hanya Marketing dan Admin yang dapat mengubah status pesanan.');
}

$data       = getJsonBody();
$idOrder    = (int)($data['id_order']   ?? 0);
$newStatus  = trim($data['status']      ?? '');
$keterangan = trim($data['keterangan']  ?? '');

if ($idOrder === 0) {
    respond('error', 'id_order wajib diisi.');
}

$validStatuses = ['processing', 'shipped', 'done', 'cancelled'];
if (!in_array($newStatus, $validStatuses)) {
    respond('error', 'Status tidak valid. Pilih: ' . implode(', ', $validStatuses));
}

// --- Cek order ada ---
$sqlGet = "SELECT id_order, status, no_order FROM orders WHERE id_order = ? LIMIT 1";
$stmtGet = mysqli_prepare($conn, $sqlGet);
mysqli_stmt_bind_param($stmtGet, 'i', $idOrder);
mysqli_stmt_execute($stmtGet);
$orderRow = mysqli_fetch_assoc(mysqli_stmt_get_result($stmtGet));

if (!$orderRow) {
    respond('error', 'Pesanan tidak ditemukan.');
}

// --- Cegah update pesanan yang sudah final ---
if (in_array($orderRow['status'], ['done', 'cancelled'])) {
    respond('error', "Pesanan dengan status '{$orderRow['status']}' tidak dapat diubah lagi.");
}

// --- Update status di tabel orders ---
$sqlUpdate = "UPDATE orders SET status = ? WHERE id_order = ?";
$stmtUpd   = mysqli_prepare($conn, $sqlUpdate);
mysqli_stmt_bind_param($stmtUpd, 'si', $newStatus, $idOrder);
mysqli_stmt_execute($stmtUpd);

// --- Catat milestone tracking ---
$labelMap = [
    'processing' => 'Pesanan sedang diproses',
    'shipped'    => 'Pesanan dikirim',
    'done'       => 'Pesanan selesai',
    'cancelled'  => 'Pesanan dibatalkan',
];
$trackingStatus = $labelMap[$newStatus] ?? ucfirst($newStatus);
$trackingNote   = $keterangan ?: $trackingStatus;

$sqlTracking = "INSERT INTO tracking (id_order, status, keterangan, updated_by)
                VALUES (?, ?, ?, ?)";
$stmtTr = mysqli_prepare($conn, $sqlTracking);
mysqli_stmt_bind_param($stmtTr, 'issi', $idOrder, $trackingStatus, $trackingNote, $userId);
mysqli_stmt_execute($stmtTr);

respond('success', 'Status pesanan berhasil diperbarui.', [
    'id_order'  => $idOrder,
    'no_order'  => $orderRow['no_order'],
    'status'    => $newStatus,
]);