<?php
// ============================================================
//  ThreadB2B — assets/deleteDocument.php
//  Soft delete dokumen: set is_aktif = 0 di payment_documents.
//  File fisik TIDAK dihapus (untuk audit trail).
//  Method : POST (JSON body)
//  Body   : { "id_doc": INT }
// ============================================================

session_start();
include __DIR__ . '/config.php';
include __DIR__ . '/noSessionRedirect.php';
header('Content-Type: application/json; charset=utf-8');

requireMethod('POST');

if ($_SESSION['role'] !== 'admin') {
    respond('error', 'Akses ditolak. Hanya Admin yang dapat menghapus dokumen.');
}

$data  = getJsonBody();
$idDoc = (int)($data['id_doc'] ?? 0);

if ($idDoc === 0) {
    respond('error', 'id_doc wajib diisi.');
}

// --- Cek dokumen ada ---
$sqlGet = "SELECT id_doc, invoice_id, jenis, nama_file, is_aktif
           FROM payment_documents WHERE id_doc = ? LIMIT 1";
$stmtGet = mysqli_prepare($conn, $sqlGet);
mysqli_stmt_bind_param($stmtGet, 'i', $idDoc);
mysqli_stmt_execute($stmtGet);
$doc = mysqli_fetch_assoc(mysqli_stmt_get_result($stmtGet));

if (!$doc) {
    respond('error', 'Dokumen tidak ditemukan.');
}
if ($doc['is_aktif'] == 0) {
    respond('error', 'Dokumen sudah dihapus sebelumnya.');
}

// --- Soft delete ---
$sqlDel = "UPDATE payment_documents SET is_aktif = 0 WHERE id_doc = ?";
$stmtDel = mysqli_prepare($conn, $sqlDel);
mysqli_stmt_bind_param($stmtDel, 'i', $idDoc);
mysqli_stmt_execute($stmtDel);

respond('success', 'Dokumen berhasil dihapus.', [
    'id_doc'     => $idDoc,
    'invoice_id' => $doc['invoice_id'],
    'jenis'      => $doc['jenis'],
]);