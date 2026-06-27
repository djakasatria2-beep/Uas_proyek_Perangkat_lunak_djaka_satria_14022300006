<?php
// ============================================================
//  ThreadB2B — assets/fetchReturnDetail.php
//  Ambil detail satu retur beserta foto dan respons.
//  Query params (GET):
//    id_return = INT   (prioritas)
//    no_return = STRING
// ============================================================

session_start();
include __DIR__ . '/config.php';
include __DIR__ . '/noSessionRedirect.php';
header('Content-Type: application/json; charset=utf-8');

requireMethod('GET');

$role    = $_SESSION['role'];
$idUser  = (int)$_SESSION['user_id'];
$idBuyer = (int)($_SESSION['id_buyer'] ?? 0);

$idReturn = (int)($_GET['id_return'] ?? 0);
$noReturn = trim($_GET['no_return'] ?? '');

if ($idReturn === 0 && $noReturn === '') {
    respond('error', 'Parameter id_return atau no_return wajib diisi.');
}

// --- Ambil data retur ---
$sql = "SELECT r.id_return, r.no_return, r.alasan_kategori, r.alasan,
               r.foto, r.respons_admin, r.status,
               o.id_order, o.no_order, o.jenis_benang, o.ukuran_benang,
               o.kode_warna, o.nama_warna, o.qty, o.status AS order_status,
               bp.id_buyer, bp.nama_perusahaan, bp.nama_pic, bp.no_whatsapp
        FROM order_returns r
        JOIN orders o         ON o.id_order  = r.id_order
        JOIN buyer_profile bp ON bp.id_buyer = o.id_buyer
        WHERE " . ($idReturn > 0 ? 'r.id_return = ?' : 'r.no_return = ?');

$stmt = mysqli_prepare($conn, $sql);
if ($idReturn > 0) {
    mysqli_stmt_bind_param($stmt, 'i', $idReturn);
} else {
    mysqli_stmt_bind_param($stmt, 's', $noReturn);
}
mysqli_stmt_execute($stmt);
$row = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

if (!$row) {
    respond('error', 'Data retur tidak ditemukan.');
}

// Buyer hanya boleh lihat retur milik sendiri
if ($role === 'buyer' && (int)$row['id_buyer'] !== $idBuyer) {
    respond('error', 'Akses ditolak.');
}

// Decode JSON foto
$row['foto'] = !empty($row['foto']) ? (json_decode($row['foto'], true) ?? []) : [];

respond('success', 'Data berhasil diambil.', ['return' => $row]);
