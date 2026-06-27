<?php
// ============================================================
//  ThreadB2B — admin_panel/fetch-data/uploadDocument.php
//  Upload dokumen ke folder uploads/ yang sesuai,
//  simpan metadata ke tabel payment_documents.
//  Tipe dokumen yang didukung:
//    - invoice  → uploads/invoices/
//    - surat_jalan → uploads/surat-jalan/
//    - nota     → uploads/nota/
//  POST (multipart/form-data):
//    file        : file yang diupload (PDF, JPG, PNG — max 5MB)
//    invoice_id  : "INV-2026-XXXXX" (wajib)
//    tipe_dokumen: "invoice"|"surat_jalan"|"nota"
//    keterangan  : <opsional>
//  Dipanggil via AJAX POST dari halaman dokumen Admin.
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

$invoiceId    = trim($_POST['invoice_id']   ?? '');
$tipeDokumen  = trim($_POST['tipe_dokumen'] ?? '');
$keterangan   = trim($_POST['keterangan']   ?? '');

$allowedTipe = ['invoice', 'surat_jalan', 'nota'];
$folderMap   = [
    'invoice'     => 'invoices',
    'surat_jalan' => 'surat-jalan',
    'nota'        => 'nota',
];

if ($invoiceId === '') {
    respond('error', 'Parameter invoice_id tidak boleh kosong.');
}
if (!in_array($tipeDokumen, $allowedTipe)) {
    respond('error', 'Tipe dokumen tidak valid. Pilihan: ' . implode(', ', $allowedTipe));
}
if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
    respond('error', 'File tidak ditemukan atau gagal diupload.');
}

// --- Validasi invoice ada ---
$sqlInv = "SELECT invoice_id FROM invoices WHERE invoice_id = ? LIMIT 1";
$stmtInv = mysqli_prepare($conn, $sqlInv);
mysqli_stmt_bind_param($stmtInv, 's', $invoiceId);
mysqli_stmt_execute($stmtInv);
if (!mysqli_fetch_assoc(mysqli_stmt_get_result($stmtInv))) {
    respond('error', 'Invoice tidak ditemukan.');
}

// --- Validasi file ---
$file      = $_FILES['file'];
$maxSize   = 5 * 1024 * 1024; // 5MB
$allowedMime = ['application/pdf', 'image/jpeg', 'image/png'];

if ($file['size'] > $maxSize) {
    respond('error', 'Ukuran file melebihi batas 5MB.');
}

$finfo    = finfo_open(FILEINFO_MIME_TYPE);
$mimeType = finfo_file($finfo, $file['tmp_name']);
finfo_close($finfo);

if (!in_array($mimeType, $allowedMime)) {
    respond('error', 'Tipe file tidak diizinkan. Gunakan PDF, JPG, atau PNG.');
}

// --- Tentukan folder tujuan ---
$uploadBase  = __DIR__ . '/../../uploads/' . $folderMap[$tipeDokumen] . '/';
if (!is_dir($uploadBase)) {
    mkdir($uploadBase, 0755, true);
}

$ext         = match ($mimeType) {
    'application/pdf' => 'pdf',
    'image/jpeg'      => 'jpg',
    'image/png'       => 'png',
    default           => 'bin',
};
$namaFile    = sprintf(
    '%s_%s_%s.%s',
    $tipeDokumen,
    str_replace('-', '', $invoiceId),
    date('YmdHis'),
    $ext
);
$targetPath  = $uploadBase . $namaFile;
$storedPath  = 'uploads/' . $folderMap[$tipeDokumen] . '/' . $namaFile;

if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
    respond('error', 'Gagal menyimpan file ke server.');
}

// --- Simpan metadata ke payment_documents ---
// Catatan: tabel payment_documents belum ada di schema rev-2.
// Query ini siap pakai setelah tabel dibuat:
//   CREATE TABLE payment_documents (
//     id INT AUTO_INCREMENT PRIMARY KEY,
//     invoice_id VARCHAR(30) NOT NULL,
//     tipe_dokumen ENUM('invoice','surat_jalan','nota') NOT NULL,
//     nama_file VARCHAR(255) NOT NULL,
//     path VARCHAR(500) NOT NULL,
//     keterangan TEXT,
//     uploaded_by INT NOT NULL,
//     is_aktif TINYINT(1) DEFAULT 1,
//     created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
//   );
$sqlDoc  = "INSERT INTO payment_documents
                (invoice_id, tipe_dokumen, nama_file, path, keterangan, uploaded_by)
            VALUES (?, ?, ?, ?, ?, ?)";
$idAdmin = (int)$_SESSION['id_user'];
$stmtDoc = mysqli_prepare($conn, $sqlDoc);

if (!$stmtDoc) {
    // Tabel belum dibuat — kembalikan sukses parsial dengan pesan info
    respond('success', 'File berhasil diupload. (Tabel payment_documents belum dibuat — metadata belum tersimpan.)', [
        'nama_file'    => $namaFile,
        'path'         => $storedPath,
        'tipe_dokumen' => $tipeDokumen,
        'invoice_id'   => $invoiceId,
    ]);
}

mysqli_stmt_bind_param($stmtDoc, 'sssssi', $invoiceId, $tipeDokumen, $namaFile, $storedPath, $keterangan, $idAdmin);
mysqli_stmt_execute($stmtDoc);
$docId = mysqli_insert_id($conn);

respond('success', 'Dokumen berhasil diupload.', [
    'id'           => $docId,
    'invoice_id'   => $invoiceId,
    'tipe_dokumen' => $tipeDokumen,
    'nama_file'    => $namaFile,
    'path'         => $storedPath,
]);