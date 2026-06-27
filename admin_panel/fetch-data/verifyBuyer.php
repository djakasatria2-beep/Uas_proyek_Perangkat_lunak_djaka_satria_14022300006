<?php
// ============================================================
//  ThreadB2B — admin_panel/fetch-data/verifyBuyer.php
//  Setujui atau tolak registrasi buyer.
//  POST body (JSON):
//    { "id_buyer": <int>, "aksi": "approved"|"rejected", "catatan": "<opsional>" }
//  - Mengubah status_verifikasi buyer
//  - Mencatat id Admin yang memverifikasi (diverifikasi_oleh)
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

// Ambil koneksi database lewat helper getDB() di config.php
$conn = getDB();

$body    = json_decode(file_get_contents('php://input'), true);
$idBuyer = (int)($body['id_buyer'] ?? 0);
$aksi    = $body['aksi'] ?? '';

if ($idBuyer <= 0) {
    respond('error', 'Parameter id_buyer tidak valid.');
}
if (!in_array($aksi, ['approved', 'rejected'])) {
    respond('error', 'Aksi tidak valid. Gunakan: approved atau rejected.');
}

// --- Cek buyer ada & masih pending ---
$sqlCek = "SELECT id_buyer, status_verifikasi FROM buyer_profile WHERE id_buyer = ? LIMIT 1";
$stmtCek = mysqli_prepare($conn, $sqlCek);
mysqli_stmt_bind_param($stmtCek, 'i', $idBuyer);
mysqli_stmt_execute($stmtCek);
$buyer = mysqli_fetch_assoc(mysqli_stmt_get_result($stmtCek));

if (!$buyer) {
    respond('error', 'Buyer tidak ditemukan.');
}
if ($buyer['status_verifikasi'] !== 'pending') {
    respond('error', "Buyer sudah dalam status '{$buyer['status_verifikasi']}', tidak bisa diverifikasi ulang.");
}

// --- Update status ---
// Catatan: $_SESSION['user_id'] (bukan 'id_user') — sesuai struktur session
// yang disimpan oleh login-backend.php.
$idAdmin = (int)$_SESSION['user_id'];
$sqlUpd  = "UPDATE buyer_profile
             SET status_verifikasi = ?,
                 diverifikasi_oleh = ?
             WHERE id_buyer = ?";
$stmtUpd = mysqli_prepare($conn, $sqlUpd);
mysqli_stmt_bind_param($stmtUpd, 'sii', $aksi, $idAdmin, $idBuyer);
mysqli_stmt_execute($stmtUpd);

if (mysqli_stmt_affected_rows($stmtUpd) === 0) {
    respond('error', 'Gagal memperbarui status buyer.');
}

$label = $aksi === 'approved' ? 'disetujui' : 'ditolak';
respond('success', "Buyer berhasil $label.", [
    'id_buyer'          => $idBuyer,
    'status_baru'       => $aksi,
    'diverifikasi_oleh' => $idAdmin,
]);