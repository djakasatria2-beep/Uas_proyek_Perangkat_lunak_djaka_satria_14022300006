<?php
// ============================================================
//  ThreadB2B — assets/register.php
//  Proses registrasi buyer baru.
//  Method : POST (multipart/form-data — karena ada file upload)
//  Fields : nama_perusahaan, nama_pic, no_whatsapp, alamat,
//           negara, npwp, nib, email, password,
//           upload_dokumen (file: NPWP/NIB, PDF/JPG, max 5MB)
// ============================================================

session_start();
include __DIR__ . '/config.php';
header('Content-Type: application/json; charset=utf-8');

requireMethod('POST');

// --- Ambil field dari $_POST (multipart) ---
$email          = trim($_POST['email']           ?? '');
$password       = trim($_POST['password']        ?? '');
$nama_perusahaan = trim($_POST['nama_perusahaan'] ?? '');
$nama_pic       = trim($_POST['nama_pic']         ?? '');
$no_whatsapp    = trim($_POST['no_whatsapp']      ?? '');
$alamat         = trim($_POST['alamat']           ?? '');
$negara         = trim($_POST['negara']           ?? 'Indonesia');
$npwp           = trim($_POST['npwp']             ?? '');
$nib            = trim($_POST['nib']              ?? '');

// --- Validasi wajib ---
$required = compact('email', 'password', 'nama_perusahaan', 'nama_pic', 'no_whatsapp', 'alamat');
foreach ($required as $field => $val) {
    if ($val === '') {
        respond('error', "Field '$field' wajib diisi.");
    }
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    respond('error', 'Format email tidak valid.');
}
if (strlen($password) < 8) {
    respond('error', 'Password minimal 8 karakter.');
}

// --- Cek duplikat email ---
$sqlCheck = "SELECT id_user FROM users WHERE email = ? LIMIT 1";
$stmtCheck = mysqli_prepare($conn, $sqlCheck);
mysqli_stmt_bind_param($stmtCheck, 's', $email);
mysqli_stmt_execute($stmtCheck);
mysqli_stmt_store_result($stmtCheck);
if (mysqli_stmt_num_rows($stmtCheck) > 0) {
    respond('error', 'Email sudah terdaftar. Gunakan email lain.');
}

// --- Handle upload dokumen (opsional tapi direkomendasikan) ---
$uploadPath = null;
if (!empty($_FILES['upload_dokumen']['tmp_name'])) {
    $file      = $_FILES['upload_dokumen'];
    $maxSize   = 5 * 1024 * 1024; // 5 MB
    $allowedMime = ['application/pdf', 'image/jpeg', 'image/png'];

    if ($file['size'] > $maxSize) {
        respond('error', 'Ukuran file dokumen maksimal 5 MB.');
    }

    $finfo    = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);

    if (!in_array($mimeType, $allowedMime)) {
        respond('error', 'Format file harus PDF, JPG, atau PNG.');
    }

    $ext        = pathinfo($file['name'], PATHINFO_EXTENSION);
    $newFileName = 'doc_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
    $destDir    = __DIR__ . '/../uploads/buyer-docs/';

    if (!is_dir($destDir)) {
        mkdir($destDir, 0755, true);
    }

    if (!move_uploaded_file($file['tmp_name'], $destDir . $newFileName)) {
        respond('error', 'Gagal menyimpan file dokumen. Coba lagi.');
    }

    $uploadPath = 'uploads/buyer-docs/' . $newFileName;
}

// --- Mulai transaksi ---
mysqli_begin_transaction($conn);

try {
    // 1. Insert ke tabel users
    $passwordHash = password_hash($password, PASSWORD_DEFAULT);
    $sqlUser = "INSERT INTO users (email, password, role) VALUES (?, ?, 'buyer')";
    $stmtUser = mysqli_prepare($conn, $sqlUser);
    mysqli_stmt_bind_param($stmtUser, 'ss', $email, $passwordHash);
    mysqli_stmt_execute($stmtUser);
    $newUserId = mysqli_insert_id($conn);

    // 2. Insert ke tabel buyer_profile
    $sqlBuyer = "INSERT INTO buyer_profile
                    (id_user, nama_perusahaan, nama_pic, no_whatsapp,
                     alamat, negara, npwp, nib, upload_dokumen,
                     status_verifikasi, tenor_hari)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending', 30)";
    $stmtBuyer = mysqli_prepare($conn, $sqlBuyer);
    mysqli_stmt_bind_param(
        $stmtBuyer, 'isssssss s',
        $newUserId, $nama_perusahaan, $nama_pic, $no_whatsapp,
        $alamat, $negara, $npwp, $nib, $uploadPath
    );
    // Perbaiki bind — 9 param
    $stmtBuyer = mysqli_prepare($conn, $sqlBuyer);
    mysqli_stmt_bind_param(
        $stmtBuyer, 'issssssss',
        $newUserId, $nama_perusahaan, $nama_pic, $no_whatsapp,
        $alamat, $negara, $npwp, $nib, $uploadPath
    );
    mysqli_stmt_execute($stmtBuyer);

    mysqli_commit($conn);

    respond('success', 'Pendaftaran berhasil! Akun Anda sedang menunggu verifikasi Admin.');

} catch (Exception $e) {
    mysqli_rollback($conn);
    respond('error', 'Terjadi kesalahan saat mendaftar. Silakan coba lagi.');
}