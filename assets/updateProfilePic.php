<?php
// ============================================================
//  ThreadB2B — assets/updateProfilePic.php
//  Upload foto profil ke uploads/profile-pics/, simpan path ke DB.
//  Form field: profile_pic (file)
//  Aturan: JPG/PNG, max 2 MB
// ============================================================

session_start();
include __DIR__ . '/config.php';
include __DIR__ . '/noSessionRedirect.php';
header('Content-Type: application/json; charset=utf-8');

requireMethod('POST');

$idUser = (int)$_SESSION['user_id'];
$role   = $_SESSION['role'];

if (empty($_FILES['profile_pic'])) {
    respond('error', 'File profile_pic wajib dikirim.');
}

$file     = $_FILES['profile_pic'];
$maxBytes = 2 * 1024 * 1024; // 2 MB

if ($file['error'] !== UPLOAD_ERR_OK) {
    respond('error', 'Upload gagal (kode error: ' . $file['error'] . ').');
}
if ($file['size'] > $maxBytes) {
    respond('error', 'Ukuran file melebihi batas 2 MB.');
}

$allowedMime = ['image/jpeg', 'image/png'];
$finfo       = new finfo(FILEINFO_MIME_TYPE);
$mime        = $finfo->file($file['tmp_name']);
if (!in_array($mime, $allowedMime)) {
    respond('error', 'Tipe file tidak diizinkan. Gunakan JPG atau PNG.');
}

$ext      = $mime === 'image/png' ? 'png' : 'jpg';
$filename = 'pp_' . $idUser . '_' . time() . '.' . $ext;
$destDir  = __DIR__ . '/../uploads/profile-pics/';
$destPath = $destDir . $filename;

if (!is_dir($destDir)) mkdir($destDir, 0755, true);

if (!move_uploaded_file($file['tmp_name'], $destPath)) {
    respond('error', 'Gagal menyimpan file.');
}

$dbPath = 'uploads/profile-pics/' . $filename;

// Hapus foto lama jika ada
if ($role === 'buyer') {
    $idBuyer = (int)($_SESSION['id_buyer'] ?? 0);
    $old = mysqli_prepare($conn,
        "SELECT upload_dokumen FROM buyer_profile WHERE id_buyer = ?");
    mysqli_stmt_bind_param($old, 'i', $idBuyer);
    mysqli_stmt_execute($old);
    $oldRow = mysqli_fetch_assoc(mysqli_stmt_get_result($old));
    if ($oldRow && str_starts_with($oldRow['upload_dokumen'] ?? '', 'uploads/profile-pics/')) {
        @unlink(__DIR__ . '/../' . $oldRow['upload_dokumen']);
    }

    $upd = mysqli_prepare($conn,
        "UPDATE buyer_profile SET upload_dokumen = ? WHERE id_buyer = ?");
    mysqli_stmt_bind_param($upd, 'si', $dbPath, $idBuyer);
    mysqli_stmt_execute($upd);
} else {
    // Marketing / Admin — simpan di session saja (atau tabel users bisa ditambah kolom photo)
    $_SESSION['photo'] = $dbPath;
}

respond('success', 'Foto profil berhasil diperbarui.', ['path' => $dbPath]);
