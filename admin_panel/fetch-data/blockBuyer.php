<?php
// ============================================================
//  ThreadB2B — admin_panel/fetch-data/blockBuyer.php
//  Blokir buyer secara manual oleh Admin.
//  POST body (JSON):
//    { "id_buyer": <int>, "alasan": "<opsional>" }
//  - Mengubah status_verifikasi → 'blocked'
//  - Catat tanggal_diblokir = NOW()
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

if ($idBuyer <= 0) {
    respond('error', 'Parameter id_buyer tidak valid.');
}

// --- Cek buyer ada & belum diblokir ---
$sqlCek = "SELECT id_buyer, status_verifikasi FROM buyer_profile WHERE id_buyer = ? LIMIT 1";
$stmtCek = mysqli_prepare($conn, $sqlCek);
mysqli_stmt_bind_param($stmtCek, 'i', $idBuyer);
mysqli_stmt_execute($stmtCek);
$buyer = mysqli_fetch_assoc(mysqli_stmt_get_result($stmtCek));

if (!$buyer) {
    respond('error', 'Buyer tidak ditemukan.');
}
if ($buyer['status_verifikasi'] === 'blocked') {
    respond('error', 'Buyer sudah dalam status blocked.');
}

// --- Blokir ---
$now    = date('Y-m-d H:i:s');
$sqlBlk = "UPDATE buyer_profile
             SET status_verifikasi = 'blocked',
                 tanggal_diblokir  = ?
             WHERE id_buyer = ?";
$stmtBlk = mysqli_prepare($conn, $sqlBlk);
mysqli_stmt_bind_param($stmtBlk, 'si', $now, $idBuyer);
mysqli_stmt_execute($stmtBlk);

if (mysqli_stmt_affected_rows($stmtBlk) === 0) {
    respond('error', 'Gagal memblokir buyer.');
}

respond('success', 'Buyer berhasil diblokir.', [
    'id_buyer'         => $idBuyer,
    'tanggal_diblokir' => $now,
]);
