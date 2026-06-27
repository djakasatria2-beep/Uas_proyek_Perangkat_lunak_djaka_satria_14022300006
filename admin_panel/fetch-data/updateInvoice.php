<?php
// ============================================================
//  ThreadB2B — admin_panel/fetch-data/updateInvoice.php
//  Update data invoice: nominal, jatuh tempo, atau catatan.
//  Hanya invoice berstatus DRAFT atau ISSUED yang boleh diupdate.
//  POST body (JSON):
//    {
//      "invoice_id":  "INV-2026-XXXXX",
//      "due_date":    "YYYY-MM-DD",     // opsional
//      "ppn_pct":     11,               // opsional — recalculate ppn & total
//      "status":      "DRAFT"|"ISSUED", // opsional
//      "created_by":  "Rosmala"         // opsional
//    }
//  Dipanggil via AJAX POST dari form edit invoice Admin.
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

$body      = json_decode(file_get_contents('php://input'), true);
$invoiceId = trim($body['invoice_id'] ?? '');

if ($invoiceId === '') {
    respond('error', 'Parameter invoice_id tidak boleh kosong.');
}

// --- Ambil invoice saat ini ---
$sqlGet = "SELECT * FROM invoices WHERE invoice_id = ? LIMIT 1";
$stmtGet = mysqli_prepare($conn, $sqlGet);
mysqli_stmt_bind_param($stmtGet, 's', $invoiceId);
mysqli_stmt_execute($stmtGet);
$inv = mysqli_fetch_assoc(mysqli_stmt_get_result($stmtGet));

if (!$inv) {
    respond('error', 'Invoice tidak ditemukan.');
}
if (!in_array($inv['status'], ['DRAFT', 'ISSUED'])) {
    respond('error', "Invoice berstatus '{$inv['status']}' tidak dapat diedit.");
}

// --- Kumpulkan field yang akan diupdate ---
$sets   = [];
$params = [];
$types  = '';

if (isset($body['due_date'])) {
    $dueDate = trim($body['due_date']);
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $dueDate)) {
        respond('error', 'Format due_date tidak valid (YYYY-MM-DD).');
    }
    $sets[]   = 'due_date = ?';
    $params[] = $dueDate;
    $types   .= 's';
}

if (isset($body['ppn_pct'])) {
    $ppnPct   = (float)$body['ppn_pct'];
    $ppnIdr   = round((float)$inv['subtotal_idr'] * ($ppnPct / 100), 2);
    $totalIdr = round((float)$inv['subtotal_idr'] + $ppnIdr, 2);

    $sets[]   = 'ppn_pct = ?';
    $params[] = $ppnPct;
    $types   .= 'd';

    $sets[]   = 'ppn_idr = ?';
    $params[] = $ppnIdr;
    $types   .= 'd';

    $sets[]   = 'total_idr = ?';
    $params[] = $totalIdr;
    $types   .= 'd';
}

if (isset($body['status'])) {
    $newStatus = strtoupper(trim($body['status']));
    if (!in_array($newStatus, ['DRAFT', 'ISSUED'])) {
        respond('error', "Status hanya boleh DRAFT atau ISSUED melalui endpoint ini.");
    }
    $sets[]   = 'status = ?';
    $params[] = $newStatus;
    $types   .= 's';
}

if (isset($body['created_by'])) {
    $sets[]   = 'created_by = ?';
    $params[] = trim($body['created_by']);
    $types   .= 's';
}

if (empty($sets)) {
    respond('error', 'Tidak ada field yang diupdate.');
}

// --- Jalankan update ---
$params[] = $invoiceId;
$types   .= 's';

$sql  = 'UPDATE invoices SET ' . implode(', ', $sets) . ' WHERE invoice_id = ?';
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, $types, ...$params);
mysqli_stmt_execute($stmt);

if (mysqli_stmt_affected_rows($stmt) === 0) {
    respond('error', 'Tidak ada perubahan yang disimpan.');
}

respond('success', "Invoice $invoiceId berhasil diperbarui.", [
    'invoice_id'   => $invoiceId,
    'fields_updated'=> array_map(fn($s) => explode(' =', $s)[0], $sets),
]);