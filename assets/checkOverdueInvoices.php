<?php
// ============================================================
//  ThreadB2B — assets/checkOverdueInvoices.php
//  Cek semua invoice yang melewati due_date.
//  Update status → OVERDUE, blokir buyer terkait.
//  Dirancang untuk dijalankan via:
//    - Cron job harian  : php /path/to/assets/checkOverdueInvoices.php
//    - On-demand AJAX   : GET (hanya Admin)
//    - Dashboard refresh: dipanggil saat buyer/Admin buka dashboard
// ============================================================

session_start();
include __DIR__ . '/config.php';
header('Content-Type: application/json; charset=utf-8');

// Jika dipanggil via HTTP, wajib Admin
$isCli = (php_sapi_name() === 'cli');
if (!$isCli) {
    include __DIR__ . '/noSessionRedirect.php';
    if ($_SESSION['role'] !== 'admin') {
        respond('error', 'Akses ditolak.');
    }
    requireMethod('GET');
}

// --- Temukan invoice yang sudah lewat due_date tapi belum OVERDUE/PAID ---
$sqlFind = "SELECT invoice_id, customer_id
            FROM invoices
            WHERE due_date < CURDATE()
              AND status NOT IN ('PAID', 'OVERDUE')";
$result  = mysqli_query($conn, $sqlFind);

$updatedInvoices = [];
$blockedBuyers   = [];

while ($row = mysqli_fetch_assoc($result)) {
    $invId      = $row['invoice_id'];
    $customerId = $row['customer_id'];

    // Update invoice → OVERDUE
    $sqlUpdInv = "UPDATE invoices SET status = 'OVERDUE' WHERE invoice_id = ?";
    $stmtUpd   = mysqli_prepare($conn, $sqlUpdInv);
    mysqli_stmt_bind_param($stmtUpd, 's', $invId);
    mysqli_stmt_execute($stmtUpd);
    $updatedInvoices[] = $invId;

    // Parse id_buyer dari customer_id (BYR-NNNN)
    if (!preg_match('/^BYR-(\d+)$/', $customerId, $m)) continue;
    $idBuyer = (int)$m[1];

    // Blokir buyer jika belum diblokir
    $sqlBuyer = "SELECT status_verifikasi FROM buyer_profile
                 WHERE id_buyer = ? LIMIT 1";
    $stmtBuyer = mysqli_prepare($conn, $sqlBuyer);
    mysqli_stmt_bind_param($stmtBuyer, 'i', $idBuyer);
    mysqli_stmt_execute($stmtBuyer);
    $buyerRow = mysqli_fetch_assoc(mysqli_stmt_get_result($stmtBuyer));

    if ($buyerRow && $buyerRow['status_verifikasi'] !== 'blocked') {
        $now        = date('Y-m-d H:i:s');
        $sqlBlock   = "UPDATE buyer_profile
                       SET status_verifikasi = 'blocked',
                           tanggal_diblokir  = ?
                       WHERE id_buyer = ?";
        $stmtBlock  = mysqli_prepare($conn, $sqlBlock);
        mysqli_stmt_bind_param($stmtBlock, 'si', $now, $idBuyer);
        mysqli_stmt_execute($stmtBlock);
        $blockedBuyers[] = $idBuyer;
    }
}

$msg = sprintf(
    '%d invoice diperbarui ke OVERDUE. %d buyer diblokir.',
    count($updatedInvoices),
    count($blockedBuyers)
);

if ($isCli) {
    echo $msg . PHP_EOL;
    exit(0);
}

respond('success', $msg, [
    'updated_invoices' => $updatedInvoices,
    'blocked_buyers'   => $blockedBuyers,
]);