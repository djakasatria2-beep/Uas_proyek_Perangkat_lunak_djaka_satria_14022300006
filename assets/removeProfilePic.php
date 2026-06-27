<?php
// ============================================================
//  ThreadB2B — assets/removeProfilePic.php
//  Hapus foto profil, reset ke avatar default.
// ============================================================

session_start();
include __DIR__ . '/config.php';
include __DIR__ . '/noSessionRedirect.php';
header('Content-Type: application/json; charset=utf-8');

requireMethod('POST');

$role   = $_SESSION['role'];
$idUser = (int)$_SESSION['user_id'];

if ($role === 'buyer') {
    $idBuyer = (int)($_SESSION['id_buyer'] ?? 0);

    // Ambil path lama
    $stmt = mysqli_prepare($conn,
        "SELECT upload_dokumen FROM buyer_profile WHERE id_buyer = ?");
    mysqli_stmt_bind_param($stmt, 'i', $idBuyer);
    mysqli_stmt_execute($stmt);
    $row = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

    if ($row && str_starts_with($row['upload_dokumen'] ?? '', 'uploads/profile-pics/')) {
        $fullPath = __DIR__ . '/../' . $row['upload_dokumen'];
        if (file_exists($fullPath)) @unlink($fullPath);

        $null = null;
        $upd  = mysqli_prepare($conn,
            "UPDATE buyer_profile SET upload_dokumen = NULL WHERE id_buyer = ?");
        mysqli_stmt_bind_param($upd, 'i', $idBuyer);
        mysqli_stmt_execute($upd);
    }
} else {
    // Hapus dari session
    if (isset($_SESSION['photo'])) {
        $fullPath = __DIR__ . '/../' . $_SESSION['photo'];
        if (file_exists($fullPath)) @unlink($fullPath);
        unset($_SESSION['photo']);
    }
}

respond('success', 'Foto profil berhasil dihapus. Avatar default aktif.');
