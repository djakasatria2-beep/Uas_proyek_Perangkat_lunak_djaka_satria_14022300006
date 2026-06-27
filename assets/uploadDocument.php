<?php
// ============================================================
//  ThreadB2B — assets/uploadDocument.php
//  Admin mengupload dokumen pembayaran ke folder uploads/
//  yang sesuai dan menyimpan metadata ke payment_documents.
//  Method : POST (multipart/form-data)
//  Fields : invoice_id, jenis (surat_invoice|surat_jalan|nota),
//           dokumen (file: PDF/JPG/PNG)
//  Batas  : surat_invoice & surat_jalan = 10 MB, nota = 5 MB
// ============================================================

session_start();
include __DIR__ . '/config.php';
include __DIR__ . '/noSessionRedirect.php';
header('Content-Type: application/json; charset=utf-8');

requireMethod('POST');

if ($_SESSION['role'] !== 'admin') {
    respond('error', 'Akses ditolak. Hanya Admin yang dapat mengupload dokumen.');
}

$adminUserId = (int)$_SESSION['user_id'];
$invoiceId   = trim($_POST['invoice_id'] ?? '');
$jenis       = trim($_POST['jenis']      ?? '');

// --- Validasi field teks ---
if ($invoiceId === '') {
    respond('error', 'invoice_id wajib diisi.');
}

$validJenis = ['surat_invoice', 'surat_jalan', 'nota'];
if (!in_array($jenis, $validJenis)) {
    respond('error', 'Jenis dokumen tidak valid. Pilih: surat_invoice, surat_jalan, atau nota.');
}

// --- Cek invoice ada ---
$sqlChk = "SELECT invoice_id FROM invoices WHERE invoice_id = ? LIMIT 1";
$stmtChk = mysqli_prepare($conn, $sqlChk);
mysqli_stmt_bind_param($stmtChk, 's', $invoiceId);
mysqli_stmt_execute($stmtChk);
mysqli_stmt_store_result($stmtChk);
if (mysqli_stmt_num_rows($stmtChk) === 0) {
    respond('error', 'Invoice tidak ditemukan.');
}

// --- Validasi file upload ---
if (empty($_FILES['dokumen']['tmp_name'])) {
    respond('error', 'File dokumen wajib diupload.');
}

$file        = $_FILES['dokumen'];
$maxSizeMap  = [
    'surat_invoice' => 10 * 1024 * 1024,
    'surat_jalan'   => 10 * 1024 * 1024,
    'nota'          => 5  * 1024 * 1024,
];
$maxSize     = $maxSizeMap[$jenis];
$allowedMime = ['application/pdf', 'image/jpeg', 'image/png'];
$destDirMap  = [
    'surat_invoice' => 'invoices',
    'surat_jalan'   => 'surat-jalan',
    'nota'          => 'nota',
];

if ($file['error'] !== UPLOAD_ERR_OK) {
    respond('error', 'Upload gagal. Kode error: ' . $file['error']);
}
if ($file['size'] > $maxSize) {
    respond('error', 'Ukuran file melebihi batas (' . ($maxSize / 1024 / 1024) . ' MB).');
}

$finfo    = finfo_open(FILEINFO_MIME_TYPE);
$mimeType = finfo_file($finfo, $file['tmp_name']);
finfo_close($finfo);

if (!in_array($mimeType, $allowedMime)) {
    respond('error', 'Format file harus PDF, JPG, atau PNG.');
}

// --- Simpan file ---
$ext      = pathinfo($file['name'], PATHINFO_EXTENSION);
$newName  = $jenis . '_' . str_replace('-', '', $invoiceId)
            . '_' . time() . '_' . bin2hex(random_bytes(3)) . '.' . $ext;
$subDir   = $destDirMap[$jenis];
$destDir  = __DIR__ . '/../uploads/' . $subDir . '/';

if (!is_dir($destDir)) {
    mkdir($destDir, 0755, true);
}

if (!move_uploaded_file($file['tmp_name'], $destDir . $newName)) {
    respond('error', 'Gagal menyimpan file. Coba lagi.');
}

$pathFile    = 'uploads/' . $subDir . '/' . $newName;
$ukuranByte  = $file['size'];

// --- Pastikan tabel payment_documents ada ---
mysqli_query($conn, "CREATE TABLE IF NOT EXISTS `payment_documents` (
    `id_doc`      INT           NOT NULL AUTO_INCREMENT,
    `invoice_id`  VARCHAR(30)   NOT NULL,
    `jenis`       ENUM('surat_invoice','surat_jalan','nota') NOT NULL,
    `nama_file`   VARCHAR(255)  NOT NULL,
    `path_file`   VARCHAR(255)  NOT NULL,
    `ukuran_byte` INT           DEFAULT NULL,
    `uploaded_by` INT           NOT NULL,
    `uploaded_at` DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `is_aktif`    TINYINT(1)    NOT NULL DEFAULT 1,
    PRIMARY KEY (`id_doc`),
    KEY `idx_pd_invoice_id` (`invoice_id`),
    KEY `idx_pd_uploaded_by` (`uploaded_by`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

// --- Insert metadata ---
$sqlIns = "INSERT INTO payment_documents
             (invoice_id, jenis, nama_file, path_file, ukuran_byte, uploaded_by)
           VALUES (?, ?, ?, ?, ?, ?)";
$stmtIns = mysqli_prepare($conn, $sqlIns);
mysqli_stmt_bind_param(
    $stmtIns, 'ssssi i',
    $invoiceId, $jenis, $newName, $pathFile, $ukuranByte, $adminUserId
);
// Perbaiki bind — 6 param tanpa spasi
$stmtIns = mysqli_prepare($conn, $sqlIns);
mysqli_stmt_bind_param(
    $stmtIns, 'ssssii',
    $invoiceId, $jenis, $newName, $pathFile, $ukuranByte, $adminUserId
);
mysqli_stmt_execute($stmtIns);
$docId = mysqli_insert_id($conn);

respond('success', 'Dokumen berhasil diupload.', [
    'id_doc'    => $docId,
    'invoice_id' => $invoiceId,
    'jenis'     => $jenis,
    'nama_file' => $newName,
    'path_file' => $pathFile,
]);