<?php
// ============================================================
//  ThreadB2B — assets/markInvoicePaid.php
//  Admin menandai invoice sebagai PAID.
//  Jika buyer sebelumnya di-blocked karena overdue,
//  status buyer otomatis kembali ke approved.
//  Method : POST (JSON body)
//  Body   : { "invoice_id": "INV-YYYY-NNNNN", "catatan": "..." }
// ============================================================

session_start();
include __DIR__ . '/config.php';
include __DIR__ . '/noSessionRedirect.php';
header('Content-Type: application/json; charset=utf-8');

requireMethod('POST');

if ($_SESSION['role'] !== 'admin') {
    respond('error', 'Akses ditolak. Hanya Admin yang dapat mengkonfirmasi pembayaran.');
}

$adminUserId = (int)$_SESSION['user_id'];
$data        = getJsonBody();
$invoiceId   = trim($data['invoice_id'] ?? '');
$catatan     = trim($data['catatan']    ?? '');

if ($invoiceId === '') {
    respond('error', 'invoice_id wajib diisi.');
}

// --- Ambil invoice ---
$sqlGet = "SELECT invoice_id, customer_id, status FROM invoices
           WHERE invoice_id = ? LIMIT 1";
$stmtGet = mysqli_prepare($conn, $sqlGet);
mysqli_stmt_bind_param($stmtGet, 's', $invoiceId);
mysqli_stmt_execute($stmtGet);
$invoice = mysqli_fetch_assoc(mysqli_stmt_get_result($stmtGet));

if (!$invoice) {
    respond('error', 'Invoice tidak ditemukan.');
}
if ($invoice['status'] === 'PAID') {
    respond('error', 'Invoice sudah berstatus PAID.');
}

mysqli_begin_transaction($conn);

try {
    // --- Update status invoice → PAID ---
    $sqlUpd = "UPDATE invoices SET status = 'PAID' WHERE invoice_id = ?";
    $stmtUpd = mysqli_prepare($conn, $sqlUpd);
    mysqli_stmt_bind_param($stmtUpd, 's', $invoiceId);
    mysqli_stmt_execute($stmtUpd);

    // --- Parse id_buyer dari customer_id (format BYR-NNNN) ---
    $customerId = $invoice['customer_id'];
    $idBuyer    = 0;
    if (preg_match('/^BYR-(\d+)$/', $customerId, $m)) {
        $idBuyer = (int)$m[1];
    }

    $buyerUnblocked = false;

    if ($idBuyer > 0) {
        // --- Cek apakah buyer sedang blocked ---
        $sqlBuyer = "SELECT id_buyer, status_verifikasi
                     FROM buyer_profile WHERE id_buyer = ? LIMIT 1";
        $stmtBuyer = mysqli_prepare($conn, $sqlBuyer);
        mysqli_stmt_bind_param($stmtBuyer, 'i', $idBuyer);
        mysqli_stmt_execute($stmtBuyer);
        $buyerRow = mysqli_fetch_assoc(mysqli_stmt_get_result($stmtBuyer));

        if ($buyerRow && $buyerRow['status_verifikasi'] === 'blocked') {
            // Cek apakah masih ada invoice overdue lain untuk buyer ini
            $custIdEsc = mysqli_real_escape_string($conn, $customerId);
            $sqlOtherOverdue = "SELECT COUNT(*) AS cnt
                                FROM invoices
                                WHERE customer_id = ?
                                  AND invoice_id  != ?
                                  AND status      = 'OVERDUE'";
            $stmtOvd = mysqli_prepare($conn, $sqlOtherOverdue);
            mysqli_stmt_bind_param($stmtOvd, 'ss', $customerId, $invoiceId);
            mysqli_stmt_execute($stmtOvd);
            $otherOverdue = (int)mysqli_fetch_assoc(mysqli_stmt_get_result($stmtOvd))['cnt'];

            if ($otherOverdue === 0) {
                // Buka blokir
                $sqlUnblock = "UPDATE buyer_profile
                               SET status_verifikasi = 'approved',
                                   tanggal_diblokir  = NULL
                               WHERE id_buyer = ?";
                $stmtUnb = mysqli_prepare($conn, $sqlUnblock);
                mysqli_stmt_bind_param($stmtUnb, 'i', $idBuyer);
                mysqli_stmt_execute($stmtUnb);
                $buyerUnblocked = true;
            }
        }
    }

    mysqli_commit($conn);

    respond('success', 'Invoice berhasil ditandai sebagai PAID.', [
        'invoice_id'      => $invoiceId,
        'buyer_unblocked' => $buyerUnblocked,
    ]);

} catch (Exception $e) {
    mysqli_rollback($conn);
    respond('error', 'Terjadi kesalahan. Silakan coba lagi.');
}