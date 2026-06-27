<?php
// ============================================================
//  ThreadB2B — assets/fetchProfileDetails.php
//  Ambil data profil lengkap user yang sedang login.
//  Dipakai oleh halaman settings / profile semua panel.
// ============================================================

session_start();
include __DIR__ . '/config.php';
include __DIR__ . '/noSessionRedirect.php';
header('Content-Type: application/json; charset=utf-8');

requireMethod('GET');

$role   = $_SESSION['role'];
$idUser = (int)$_SESSION['user_id'];

$profile = [];

// Data dasar dari tabel users
$stmt = mysqli_prepare($conn,
    "SELECT id_user, email, role, created_at FROM users WHERE id_user = ?");
mysqli_stmt_bind_param($stmt, 'i', $idUser);
mysqli_stmt_execute($stmt);
$user = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

if (!$user) respond('error', 'User tidak ditemukan.');

$profile['user'] = $user;

if ($role === 'buyer') {
    $idBuyer = (int)($_SESSION['id_buyer'] ?? 0);
    $stmt2   = mysqli_prepare($conn,
        "SELECT id_buyer, nama_perusahaan, nama_pic, no_whatsapp, alamat,
                negara, contact_person, no_telp, npwp, nib,
                upload_dokumen, status_verifikasi, tenor_hari
         FROM buyer_profile WHERE id_buyer = ?");
    mysqli_stmt_bind_param($stmt2, 'i', $idBuyer);
    mysqli_stmt_execute($stmt2);
    $buyer = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt2));

    // Pisahkan foto profil dari upload_dokumen
    if ($buyer && str_starts_with($buyer['upload_dokumen'] ?? '', 'uploads/profile-pics/')) {
        $buyer['photo'] = $buyer['upload_dokumen'];
        $buyer['upload_dokumen'] = null;
    } else {
        $buyer['photo'] = null;
    }

    $profile['buyer'] = $buyer;
} else {
    // Marketing / Admin — hanya data users + photo dari session
    $profile['photo'] = $_SESSION['photo'] ?? null;
}

respond('success', 'Data profil berhasil diambil.', ['profile' => $profile]);
