<?php
// ============================================================
//  ThreadB2B — assets/createInvoice.php
//  Admin membuat invoice baru.
//  Auto-generate nomor INV-YYYY-NNNNN.
//  Auto-hitung due_date berdasarkan tenor_hari buyer.
//  Method : POST (JSON body)
//  Body   : {
//             "id_buyer"      : INT,
//             "id_order"      : INT   (opsional, untuk referensi),
//             "invoice_date"  : "YYYY-MM-DD",
//             "subtotal_idr"  : FLOAT,
//             "ppn_pct"       : FLOAT  (default 11),
//             "catatan"       : STRING (opsional)
//           }
// ============================================================

session_start();
include __DIR__ . '/config.php';
include __DIR__ . '/noSessionRedirect.php';
header('Content-Type: application/json; charset=utf-8');

requireMethod('POST');

if ($_SESSION['role'] !== 'admin') {
    respond('error', 'Akses ditolak. Hanya Admin yang dapat membuat invoice.');
}

$adminUserId = (int)$_SESSION['user_id'];
$data        = getJsonBody();

$idBuyer     = (int)($data['id_buyer']     ?? 0);
$invoiceDate = trim($data['invoice_date']  ?? date('Y-m-d'));
$subtotal    = (float)($data['subtotal_idr'] ?? 0);
$ppnPct      = (float)($data['ppn_pct']    ?? 11.00);
$catatan     = trim($data['catatan']        ?? '');

// --- Validasi ---
if ($idBuyer === 0) {
    respond('error', 'id_buyer wajib diisi.');
}
if ($subtotal <= 0) {
    respond('error', 'Subtotal harus lebih dari 0.');
}
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $invoiceDate)) {
    respond('error', 'Format invoice_date harus YYYY-MM-DD.');
}

// --- Ambil data buyer: customer_id & tenor_hari ---
$sqlBuyer = "SELECT bp.id_buyer, bp.tenor_hari,
                    CONCAT(bp.id_buyer) AS customer_id,
                    bp.nama_perusahaan,
                    u.email AS created_by_email
             FROM buyer_profile bp
             JOIN users u ON u.id_user = bp.id_user
             WHERE bp.id_buyer = ? AND bp.status_verifikasi = 'approved'
             LIMIT 1";
$stmtBuyer = mysqli_prepare($conn, $sqlBuyer);
mysqli_stmt_bind_param($stmtBuyer, 'i', $idBuyer);
mysqli_stmt_execute($stmtBuyer);
$buyer = mysqli_fetch_assoc(mysqli_stmt_get_result($stmtBuyer));

if (!$buyer) {
    respond('error', 'Buyer tidak ditemukan atau belum diverifikasi.');
}

// --- Hitung nilai ---
$tenorHari  = (int)$buyer['tenor_hari'];
$ppnIdr     = round($subtotal * ($ppnPct / 100), 2);
$totalIdr   = round($subtotal + $ppnIdr, 2);
$dueDate    = date('Y-m-d', strtotime($invoiceDate . ' +' . $tenorHari . ' days'));
$customerId = 'BYR-' . str_pad($idBuyer, 4, '0', STR_PAD_LEFT); // e.g. BYR-0001

// --- Generate nomor invoice: INV-YYYY-NNNNN ---
$invoiceId = generateDocNumber($conn, 'INV', 'invoices', 'invoice_id');

// --- Ambil email admin sebagai created_by ---
$sqlAdmin = "SELECT email FROM users WHERE id_user = ? LIMIT 1";
$stmtAdm  = mysqli_prepare($conn, $sqlAdmin);
mysqli_stmt_bind_param($stmtAdm, 'i', $adminUserId);
mysqli_stmt_execute($stmtAdm);
$adminRow  = mysqli_fetch_assoc(mysqli_stmt_get_result($stmtAdm));
$createdBy = $adminRow['email'] ?? 'Admin';

// --- Insert invoice ---
$sqlIns = "INSERT INTO invoices
             (invoice_id, invoice_date, customer_id, credit_days,
              due_date, subtotal_idr, ppn_pct, ppn_idr,
              total_idr, created_by, status)
           VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'ISSUED')";
$stmtIns = mysqli_prepare($conn, $sqlIns);
mysqli_stmt_bind_param(
    $stmtIns, 'sssisdddds',
    $invoiceId, $invoiceDate, $customerId, $tenorHari,
    $dueDate, $subtotal, $ppnPct, $ppnIdr,
    $totalIdr, $createdBy
);
mysqli_stmt_execute($stmtIns);

respond('success', 'Invoice berhasil dibuat.', [
    'invoice_id'   => $invoiceId,
    'due_date'     => $dueDate,
    'total_idr'    => $totalIdr,
    'ppn_idr'      => $ppnIdr,
    'tenor_hari'   => $tenorHari,
    'customer_id'  => $customerId,
]);