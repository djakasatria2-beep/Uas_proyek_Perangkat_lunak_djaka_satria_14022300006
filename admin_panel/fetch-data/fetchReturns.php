<?php
// ============================================================
//  ThreadB2B — admin_panel/fetch-data/fetchDocuments.php
//  Ambil daftar dokumen yang terhubung ke invoice atau order.
//  Query param (minimal salah satu):
//    ?invoice_id=INV-2026-XXXXX
//    ?tipe=invoice|surat_jalan|nota|all  (default: all)
//  Dipanggil via AJAX GET dari panel dokumen Admin.
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

$invoiceId = trim($_GET['invoice_id'] ?? '');
$tipe      = trim($_GET['tipe']       ?? 'all');

$allowedTipe = ['invoice', 'surat_jalan', 'nota', 'all'];
if (!in_array($tipe, $allowedTipe)) {
    respond('error', 'Tipe tidak valid.');
}
if ($invoiceId === '') {
    respond('error', 'Parameter invoice_id wajib disertakan.');
}

// --- Validasi invoice ada ---
$sqlInvCek = "SELECT invoice_id FROM invoices WHERE invoice_id = ? LIMIT 1";
$stmtInvCek = mysqli_prepare($conn, $sqlInvCek);
mysqli_stmt_bind_param($stmtInvCek, 's', $invoiceId);
mysqli_stmt_execute($stmtInvCek);
if (!mysqli_fetch_assoc(mysqli_stmt_get_result($stmtInvCek))) {
    respond('error', 'Invoice tidak ditemukan.');
}

// --- Bangun query ---
$conditions = ['pd.invoice_id = ?', 'pd.is_aktif = 1'];
$params     = [$invoiceId];
$types      = 's';

if ($tipe !== 'all') {
    $conditions[] = 'pd.tipe_dokumen = ?';
    $params[]     = $tipe;
    $types       .= 's';
}

$where = 'WHERE ' . implode(' AND ', $conditions);

$sql = "SELECT
            pd.id,
            pd.invoice_id,
            pd.tipe_dokumen,
            pd.nama_file,
            pd.path,
            pd.keterangan,
            pd.created_at,
            u.email AS diupload_oleh
        FROM payment_documents pd
        LEFT JOIN users u ON u.id_user = pd.uploaded_by
        $where
        ORDER BY pd.created_at DESC";

$stmt = mysqli_prepare($conn, $sql);

if (!$stmt) {
    // Tabel payment_documents belum dibuat
    respond('success', 'Dokumen berhasil diambil. (Tabel payment_documents belum tersedia.)', [
        'invoice_id' => $invoiceId,
        'documents'  => [],
        'total'      => 0,
    ]);
}

mysqli_stmt_bind_param($stmt, $types, ...$params);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

$docs = [];
while ($row = mysqli_fetch_assoc($result)) {
    // Tambahkan URL download langsung
    $row['url'] = rtrim(BASE_URL ?? '', '/') . '/' . ltrim($row['path'], '/');
    $docs[] = $row;
}

respond('success', 'Dokumen berhasil diambil.', [
    'invoice_id' => $invoiceId,
    'documents'  => $docs,
    'total'      => count($docs),
]);