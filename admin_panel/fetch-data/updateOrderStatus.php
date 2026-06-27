<?php
// ============================================================
//  ThreadB2B — admin_panel/fetch-data/updateOrderStatus.php
//  Update status pesanan oleh Admin.
//  Jika status baru = 'shipped', otomatis tambah milestone tracking.
//  POST body (JSON):
//    {
//      "id_order":   <int>,
//      "status":     "pending"|"processing"|"shipped"|"done"|"cancelled",
//      "keterangan": "<opsional — catatan untuk tracking>",
//      "resi_no":    "<opsional — nomor resi jika shipped>"
//    }
//  Dipanggil via AJAX POST dari halaman orders Admin.
// ============================================================

session_start();
include __DIR__ . '/../../assets/config.php';
include __DIR__ . '/../../assets/noSessionRedirect.php';
header('Content-Type: application/json; charset=utf-8');

if ($_SESSION['role'] !== 'admin') {
    respond('error', 'Akses ditolak.');
}
requireMethod('GET');

$conn = getDB(); // ← tambahkan baris ini
requireMethod('POST');

$body       = json_decode(file_get_contents('php://input'), true);
$idOrder    = (int)($body['id_order']   ?? 0);
$status     = trim($body['status']      ?? '');
$keterangan = trim($body['keterangan']  ?? '');
$resiNo     = trim($body['resi_no']     ?? '');

$allowedStatus = ['pending', 'processing', 'shipped', 'done', 'cancelled'];

if ($idOrder <= 0) {
    respond('error', 'Parameter id_order tidak valid.');
}
if (!in_array($status, $allowedStatus)) {
    respond('error', 'Status tidak valid. Pilihan: ' . implode(', ', $allowedStatus));
}

// --- Cek order ada ---
$sqlCek = "SELECT id_order, status, no_order FROM orders WHERE id_order = ? LIMIT 1";
$stmtCek = mysqli_prepare($conn, $sqlCek);
mysqli_stmt_bind_param($stmtCek, 'i', $idOrder);
mysqli_stmt_execute($stmtCek);
$order = mysqli_fetch_assoc(mysqli_stmt_get_result($stmtCek));

if (!$order) {
    respond('error', 'Pesanan tidak ditemukan.');
}
if ($order['status'] === 'done' || $order['status'] === 'cancelled') {
    respond('error', "Pesanan sudah berstatus '{$order['status']}', tidak dapat diubah.");
}

// --- Update status order ---
$sqlUpd = "UPDATE orders SET status = ? WHERE id_order = ?";
$stmtUpd = mysqli_prepare($conn, $sqlUpd);
mysqli_stmt_bind_param($stmtUpd, 'si', $status, $idOrder);
mysqli_stmt_execute($stmtUpd);

// --- Tambah milestone tracking ---
$idAdmin    = (int)$_SESSION['id_user'];
$keteranganTracking = $keterangan;

if ($keteranganTracking === '') {
    $labelMap = [
        'pending'    => 'Pesanan menunggu konfirmasi.',
        'processing' => 'Pesanan sedang diproses.',
        'shipped'    => 'Pesanan telah dikirim.' . ($resiNo ? " No. Resi: $resiNo." : ''),
        'done'       => 'Pesanan selesai dan diterima.',
        'cancelled'  => 'Pesanan dibatalkan oleh Admin.',
    ];
    $keteranganTracking = $labelMap[$status] ?? "Status diubah ke $status.";
}

$sqlTracking = "INSERT INTO tracking (id_order, status, keterangan, updated_by, tanggal)
                VALUES (?, ?, ?, ?, NOW())";
$stmtTrk = mysqli_prepare($conn, $sqlTracking);
mysqli_stmt_bind_param($stmtTrk, 'issi', $idOrder, $status, $keteranganTracking, $idAdmin);
mysqli_stmt_execute($stmtTrk);
$idTracking = mysqli_insert_id($conn);

respond('success', "Status pesanan #{$order['no_order']} berhasil diperbarui ke '$status'.", [
    'id_order'   => $idOrder,
    'no_order'   => $order['no_order'],
    'status_baru'=> $status,
    'id_tracking'=> $idTracking,
]);