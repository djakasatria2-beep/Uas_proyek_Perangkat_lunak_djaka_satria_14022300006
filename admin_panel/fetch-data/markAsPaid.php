<?php
// ============================================================
//  ThreadB2B — admin_panel/fetch-data/markAsPaid.php
//  Tandai invoice sebagai PAID.
//  Jika buyer sedang blocked & tidak ada invoice OVERDUE lain
//  → otomatis buka blokir buyer.
//  POST body (JSON):
//    { "invoice_id": "INV-2026-XXXXX" }
//  Dipanggil via AJAX POST dari halaman invoice Admin.
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

// --- Ambil invoice ---
$sqlInv = "SELECT invoice_id, customer_id, status FROM invoices WHERE invoice_id = ? LIMIT 1";
$stmtInv = mysqli_prepare($conn, $sqlInv);
mysqli_stmt_bind_param($stmtInv, 's', $invoiceId);
mysqli_stmt_execute($stmtInv);
$invoice = mysqli_fetch_assoc(mysqli_stmt_get_result($stmtInv));

if (!$invoice) {
    respond('error', 'Invoice tidak ditemukan.');
}
if ($invoice['status'] === 'PAID') {
    respond('error', 'Invoice sudah berstatus PAID.');
}

// --- Update invoice → PAID ---
$sqlPaid = "UPDATE invoices SET status = 'PAID' WHERE invoice_id = ?";
$stmtPaid = mysqli_prepare($conn, $sqlPaid);
mysqli_stmt_bind_param($stmtPaid, 's', $invoiceId);
mysqli_stmt_execute($stmtPaid);

// --- Cek apakah buyer perlu di-unblock ---
$customerId = $invoice['customer_id'];
$unblocked  = false;

if (!preg_match('/^BYR-(\d+)$/', $customerId, $m)) {
    // customer_id format tidak cocok, skip unblock
    respond('success', 'Invoice berhasil ditandai PAID.', [
        'invoice_id' => $invoiceId,
        'unblocked'  => false,
    ]);
}

$idBuyer = (int)$m[1];

// Cek status buyer
$sqlBuyer = "SELECT status_verifikasi FROM buyer_profile WHERE id_buyer = ? LIMIT 1";
$stmtBuyer = mysqli_prepare($conn, $sqlBuyer);
mysqli_stmt_bind_param($stmtBuyer, 'i', $idBuyer);
mysqli_stmt_execute($stmtBuyer);
$buyerRow = mysqli_fetch_assoc(mysqli_stmt_get_result($stmtBuyer));

if ($buyerRow && $buyerRow['status_verifikasi'] === 'blocked') {
    // Cek masih ada invoice OVERDUE lain
    $sqlOvd = "SELECT COUNT(*) AS jumlah
               FROM invoices
               WHERE customer_id = ?
                 AND status = 'OVERDUE'
                 AND invoice_id <> ?";
    $stmtOvd = mysqli_prepare($conn, $sqlOvd);
    mysqli_stmt_bind_param($stmtOvd, 'ss', $customerId, $invoiceId);
    mysqli_stmt_execute($stmtOvd);
    $ovdCount = (int) mysqli_fetch_assoc(mysqli_stmt_get_result($stmtOvd))['jumlah'];

    if ($ovdCount === 0) {
        $sqlUnblk = "UPDATE buyer_profile
                      SET status_verifikasi = 'approved',
                          tanggal_diblokir  = NULL
                      WHERE id_buyer = ?";
        $stmtUnblk = mysqli_prepare($conn, $sqlUnblk);
        mysqli_stmt_bind_param($stmtUnblk, 'i', $idBuyer);
        mysqli_stmt_execute($stmtUnblk);
        $unblocked = true;
    }
}

respond('success', 'Invoice berhasil ditandai PAID.' . ($unblocked ? ' Blokir buyer otomatis dibuka.' : ''), [
    'invoice_id' => $invoiceId,
    'unblocked'  => $unblocked,
    'id_buyer'   => $idBuyer,
]);