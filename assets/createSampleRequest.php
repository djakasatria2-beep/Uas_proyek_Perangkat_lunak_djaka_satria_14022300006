<?php
// ============================================================
//  ThreadB2B — assets/createSampleRequest.php
//  Buyer mengajukan permintaan sampel warna baru.
//  Method : POST (multipart/form-data — ada file upload)
//  Fields : jenis_benang, ukuran_benang, kode_warna_target,
//           tanggal_dibutuhkan, catatan,
//           upload_sampel (file: foto/referensi, JPG/PNG/PDF, max 5MB)
// ============================================================

session_start();
include __DIR__ . '/config.php';
include __DIR__ . '/noSessionRedirect.php';
header('Content-Type: application/json; charset=utf-8');

requireMethod('POST');

if ($_SESSION['role'] !== 'buyer') {
    respond('error', 'Akses ditolak. Hanya Buyer yang dapat mengajukan sampel.');
}

$idBuyer = (int)($_SESSION['id_buyer'] ?? 0);
if ($idBuyer === 0) {
    respond('error', 'Data buyer tidak ditemukan pada sesi.');
}

// --- Validasi status buyer ---
$sqlStatus = "SELECT status_verifikasi FROM buyer_profile WHERE id_buyer = ? LIMIT 1";
$stmtSt = mysqli_prepare($conn, $sqlStatus);
mysqli_stmt_bind_param($stmtSt, 'i', $idBuyer);
mysqli_stmt_execute($stmtSt);
$buyerRow = mysqli_fetch_assoc(mysqli_stmt_get_result($stmtSt));

if (!$buyerRow || $buyerRow['status_verifikasi'] !== 'approved') {
    respond('error', 'Akun Anda belum diverifikasi atau diblokir.');
}

// --- Ambil field POST ---
$jenisBenang       = trim($_POST['jenis_benang']       ?? '');
$ukuranBenang      = trim($_POST['ukuran_benang']      ?? '');
$kodeWarnaTarget   = trim($_POST['kode_warna_target']  ?? '');
$tanggalDibutuhkan = trim($_POST['tanggal_dibutuhkan'] ?? '');
$catatan           = trim($_POST['catatan']             ?? '');
$tanggal           = date('Y-m-d');

if ($jenisBenang === '') {
    respond('error', 'Jenis benang wajib diisi.');
}

// --- Handle upload file referensi (opsional) ---
$uploadPath = null;
if (!empty($_FILES['upload_sampel']['tmp_name'])) {
    $file        = $_FILES['upload_sampel'];
    $maxSize     = 5 * 1024 * 1024;
    $allowedMime = ['image/jpeg', 'image/png', 'application/pdf'];

    if ($file['size'] > $maxSize) {
        respond('error', 'Ukuran file referensi maksimal 5 MB.');
    }
    $finfo    = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);

    if (!in_array($mimeType, $allowedMime)) {
        respond('error', 'Format file harus JPG, PNG, atau PDF.');
    }

    $ext        = pathinfo($file['name'], PATHINFO_EXTENSION);
    $newName    = 'ref_' . $idBuyer . '_' . time() . '_' . bin2hex(random_bytes(3)) . '.' . $ext;
    $destDir    = __DIR__ . '/../uploads/sample-refs/';

    if (!is_dir($destDir)) mkdir($destDir, 0755, true);

    if (!move_uploaded_file($file['tmp_name'], $destDir . $newName)) {
        respond('error', 'Gagal menyimpan file referensi.');
    }
    $uploadPath = 'uploads/sample-refs/' . $newName;
}

// --- Insert ke sample_requests ---
$tanggalDibutuhkanVal = $tanggalDibutuhkan ?: null;

$sqlInsert = "INSERT INTO sample_requests
                (id_buyer, jenis_benang, ukuran_benang, kode_warna_target,
                 upload_sampel, tanggal, tanggal_dibutuhkan, catatan, status)
              VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'pending')";
$stmtIns = mysqli_prepare($conn, $sqlInsert);
mysqli_stmt_bind_param(
    $stmtIns, 'isssssss',
    $idBuyer, $jenisBenang, $ukuranBenang, $kodeWarnaTarget,
    $uploadPath, $tanggal, $tanggalDibutuhkanVal, $catatan
);
mysqli_stmt_execute($stmtIns);
$newRequestId = mysqli_insert_id($conn);

respond('success', 'Permintaan sampel berhasil diajukan.', [
    'id_request' => $newRequestId,
]);