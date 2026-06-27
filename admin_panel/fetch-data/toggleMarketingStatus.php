<?php
// ============================================================
//  ThreadB2B — admin_panel/fetch-data/toggleMarketingStatus.php
//  Aktifkan atau nonaktifkan akun Marketing.
//  Implementasi: mengubah role user antara 'marketing' <-> 'inactive_marketing'
//  (Atau ganti dengan kolom is_active jika sudah ditambahkan ke tabel users.)
//  POST body (JSON):
//    { "id_user": <int>, "aktif": true|false }
//  Dipanggil via AJAX POST dari halaman manajemen akun Admin.
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

$body   = json_decode(file_get_contents('php://input'), true);
$idUser = (int)($body['id_user'] ?? 0);
$aktif  = isset($body['aktif']) ? (bool)$body['aktif'] : null;

if ($idUser <= 0) {
    respond('error', 'Parameter id_user tidak valid.');
}
if ($aktif === null) {
    respond('error', 'Parameter aktif wajib disertakan (true/false).');
}

// Cegah Admin menonaktifkan dirinya sendiri
if ($idUser === (int)$_SESSION['id_user']) {
    respond('error', 'Tidak dapat mengubah status akun sendiri.');
}

// --- Cek user ada dan berperan marketing ---
$sqlCek = "SELECT id_user, role FROM users WHERE id_user = ? LIMIT 1";
$stmtCek = mysqli_prepare($conn, $sqlCek);
mysqli_stmt_bind_param($stmtCek, 'i', $idUser);
mysqli_stmt_execute($stmtCek);
$user = mysqli_fetch_assoc(mysqli_stmt_get_result($stmtCek));

if (!$user) {
    respond('error', 'User tidak ditemukan.');
}
if (!in_array($user['role'], ['marketing', 'inactive_marketing'])) {
    respond('error', 'User bukan akun Marketing.');
}

// --- Toggle ---
// Catatan: enum tabel users saat ini hanya buyer|marketing|admin.
// Jika ingin nonaktifkan tanpa ubah ENUM, pertimbangkan menambah kolom is_active TINYINT.
// Implementasi ini mengasumsikan kolom is_active sudah ada.
// Ganti blok di bawah jika menggunakan pendekatan ENUM.

$sqlUpd = "UPDATE users SET role = ? WHERE id_user = ?";
$roleBar = $aktif ? 'marketing' : 'inactive_marketing';

// Fallback: jika kolom is_active ada, gunakan ini:
// $sqlUpd = "UPDATE users SET is_active = ? WHERE id_user = ?";
// $aktifVal = $aktif ? 1 : 0;
// $stmtUpd = mysqli_prepare($conn, $sqlUpd);
// mysqli_stmt_bind_param($stmtUpd, 'ii', $aktifVal, $idUser);

$stmtUpd = mysqli_prepare($conn, $sqlUpd);
mysqli_stmt_bind_param($stmtUpd, 'si', $roleBar, $idUser);
mysqli_stmt_execute($stmtUpd);

$label = $aktif ? 'diaktifkan' : 'dinonaktifkan';
respond('success', "Akun Marketing berhasil $label.", [
    'id_user'   => $idUser,
    'status'    => $aktif ? 'active' : 'inactive',
]);