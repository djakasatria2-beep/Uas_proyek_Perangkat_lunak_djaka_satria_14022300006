<?php
// ============================================================
//  ThreadB2B — assets/loadProfilePic.php
//  Ambil path foto profil aktif user yang sedang login.
// ============================================================

session_start();
include __DIR__ . '/config.php';
include __DIR__ . '/noSessionRedirect.php';
header('Content-Type: application/json; charset=utf-8');

requireMethod('GET');

$role   = $_SESSION['role'];
$idUser = (int)$_SESSION['user_id'];

$photo = null;

if ($role === 'buyer') {
    $idBuyer = (int)($_SESSION['id_buyer'] ?? 0);
    $stmt = mysqli_prepare($conn,
        "SELECT upload_dokumen FROM buyer_profile WHERE id_buyer = ?");
    mysqli_stmt_bind_param($stmt, 'i', $idBuyer);
    mysqli_stmt_execute($stmt);
    $row = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    if ($row && str_starts_with($row['upload_dokumen'] ?? '', 'uploads/profile-pics/')) {
        $photo = $row['upload_dokumen'];
    }
} else {
    // Dari session jika sudah pernah diupload
    $photo = $_SESSION['photo'] ?? null;
}

respond('success', 'Data berhasil diambil.', [
    'photo'       => $photo,
    'has_custom'  => $photo !== null,
]);
