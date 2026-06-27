<?php
// ============================================================
//  ThreadB2B — admin_panel/fetch-data/unblockBuyer.php
//  Buka blokir buyer — ubah status kembali ke 'approved'.
//  Hanya bisa dilakukan jika tidak ada invoice OVERDUE aktif.
//  POST body (JSON):
//    { "id_buyer": <int> }
//  Dipanggil via AJAX POST dari halaman manajemen buyer Admin.
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

$body    = json_decode(file_get_contents('php://input'), true);
$idBuyer = (int)($body['id_buyer'] ?? 0);

if ($idBuyer <= 0) {
    respond('error', 'Parameter id_buyer tidak valid.');
}

// --- Cek buyer ada & sedang blocked ---
$sqlCek = "SELECT id_buyer, status_verifikasi FROM buyer_profile WHERE id_buyer = ? LIMIT 1";
$stmtCek = mysqli_prepare($conn, $sqlCek);
mysqli_stmt_bind_param($stmtCek, 'i', $idBuyer);
mysqli_stmt_execute($stmtCek);
$buyer = mysqli_fetch_assoc(mysqli_stmt_get_result($stmtCek));

if (!$buyer) {
    respond('error', 'Buyer tidak ditemukan.');
}
if ($buyer['status_verifikasi'] !== 'blocked') {
    respond('error', "Buyer tidak sedang diblokir (status: {$buyer['status_verifikasi']}).");
}

// --- Cek masih ada invoice OVERDUE ---
$customerId = 'BYR-' . str_pad($idBuyer, 4, '0', STR_PAD_LEFT);
$sqlOvd     = "SELECT COUNT(*) AS jumlah FROM invoices
               WHERE customer_id = ? AND status = 'OVERDUE'";
$stmtOvd = mysqli_prepare($conn, $sqlOvd);
mysqli_stmt_bind_param($stmtOvd, 's', $customerId);
mysqli_stmt_execute($stmtOvd);
$ovdCount = (int) mysqli_fetch_assoc(mysqli_stmt_get_result($stmtOvd))['jumlah'];

if ($ovdCount > 0) {
    respond('error', "Tidak dapat membuka blokir: buyer masih memiliki {$ovdCount} invoice OVERDUE yang belum dilunasi.");
}

// --- Buka blokir ---
$sqlUnblk = "UPDATE buyer_profile
              SET status_verifikasi = 'approved',
                  tanggal_diblokir  = NULL
              WHERE id_buyer = ?";
$stmtUnblk = mysqli_prepare($conn, $sqlUnblk);
mysqli_stmt_bind_param($stmtUnblk, 'i', $idBuyer);
mysqli_stmt_execute($stmtUnblk);

if (mysqli_stmt_affected_rows($stmtUnblk) === 0) {
    respond('error', 'Gagal membuka blokir buyer.');
}

respond('success', 'Blokir buyer berhasil dibuka.', [
    'id_buyer'   => $idBuyer,
    'status_baru'=> 'approved',
]);