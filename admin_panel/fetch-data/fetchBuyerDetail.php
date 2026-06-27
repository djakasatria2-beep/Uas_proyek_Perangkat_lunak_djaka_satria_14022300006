<?php
// ============================================================
//  ThreadB2B — admin_panel/fetch-data/fetchBuyerDetail.php
//  Ambil detail lengkap satu buyer:
//    - Profil & dokumen
//    - Riwayat blokir (tanggal_diblokir)
//    - Ringkasan order & invoice
//  Query param wajib:
//    ?id_buyer=<int>
//  Dipanggil via AJAX GET dari modal detail buyer di Admin.
// ============================================================
session_start();
include __DIR__ . '/../../assets/config.php';
include __DIR__ . '/../../assets/noSessionRedirect.php';
header('Content-Type: application/json; charset=utf-8');

if ($_SESSION['role'] !== 'admin') {
    respond('error', 'Akses ditolak.');
}
requireMethod('GET');

// Ambil koneksi database lewat helper getDB() di config.php
$conn = getDB();

$idBuyer = (int)($_GET['id_buyer'] ?? 0);
if ($idBuyer <= 0) {
    respond('error', 'Parameter id_buyer tidak valid.');
}

// --- Profil buyer ---
$sqlProfile = "SELECT
                   bp.*,
                   CONCAT('BYR-', LPAD(bp.id_buyer, 4, '0')) AS customer_id,
                   u.email,
                   u.created_at AS terdaftar_pada,
                   adm.email    AS diverifikasi_oleh_email
               FROM buyer_profile bp
               JOIN users u ON u.id_user = bp.id_user
               LEFT JOIN users adm ON adm.id_user = bp.diverifikasi_oleh
               WHERE bp.id_buyer = ?
               LIMIT 1";
$stmt = mysqli_prepare($conn, $sqlProfile);
mysqli_stmt_bind_param($stmt, 'i', $idBuyer);
mysqli_stmt_execute($stmt);
$buyer = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

if (!$buyer) {
    respond('error', 'Buyer tidak ditemukan.');
}
$customerId = $buyer['customer_id'];

// --- Ringkasan order ---
$sqlOrders = "SELECT
                  COUNT(*)                                              AS total_order,
                  SUM(CASE WHEN status = 'pending'    THEN 1 ELSE 0 END) AS pending,
                  SUM(CASE WHEN status = 'processing' THEN 1 ELSE 0 END) AS processing,
                  SUM(CASE WHEN status = 'done'       THEN 1 ELSE 0 END) AS done,
                  SUM(CASE WHEN status = 'cancelled'  THEN 1 ELSE 0 END) AS cancelled,
                  SUM(qty * harga_benang)                               AS total_nilai
              FROM orders
              WHERE id_buyer = ?";
$stmtOrd = mysqli_prepare($conn, $sqlOrders);
mysqli_stmt_bind_param($stmtOrd, 'i', $idBuyer);
mysqli_stmt_execute($stmtOrd);
$orderStats = mysqli_fetch_assoc(mysqli_stmt_get_result($stmtOrd));

// --- Ringkasan invoice ---
$sqlInv = "SELECT
               COUNT(*)                                                AS total_invoice,
               SUM(CASE WHEN status = 'PAID'    THEN total_idr ELSE 0 END) AS total_paid,
               SUM(CASE WHEN status = 'OVERDUE' THEN total_idr ELSE 0 END) AS total_overdue,
               SUM(CASE WHEN status = 'ISSUED'  THEN total_idr ELSE 0 END) AS total_outstanding,
               MAX(due_date)                                           AS jatuh_tempo_terakhir
           FROM invoices
           WHERE customer_id = ?";
$stmtInv = mysqli_prepare($conn, $sqlInv);
mysqli_stmt_bind_param($stmtInv, 's', $customerId);
mysqli_stmt_execute($stmtInv);
$invoiceStats = mysqli_fetch_assoc(mysqli_stmt_get_result($stmtInv));

// --- 5 invoice terbaru ---
$sqlLatestInv = "SELECT invoice_id, invoice_date, due_date, total_idr, status
                 FROM invoices
                 WHERE customer_id = ?
                 ORDER BY invoice_date DESC
                 LIMIT 5";
$stmtLI = mysqli_prepare($conn, $sqlLatestInv);
mysqli_stmt_bind_param($stmtLI, 's', $customerId);
mysqli_stmt_execute($stmtLI);
$recentInvoices = [];
$resLI = mysqli_stmt_get_result($stmtLI);
while ($r = mysqli_fetch_assoc($resLI)) {
    $recentInvoices[] = $r;
}

respond('success', 'Detail buyer berhasil diambil.', [
    'buyer'           => $buyer,
    'order_stats'     => $orderStats,
    'invoice_stats'   => $invoiceStats,
    'recent_invoices' => $recentInvoices,
]);