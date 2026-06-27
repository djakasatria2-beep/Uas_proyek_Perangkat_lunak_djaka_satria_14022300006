<?php
// ============================================================
//  ThreadB2B — admin_panel/fetch-data/fetchDashboardStats.php
//  Ambil semua angka statistik dashboard Admin:
//    - Total revenue (invoice PAID bulan ini)
//    - Total orders aktif (pending + processing)
//    - Jumlah buyer overdue
//    - Jumlah sampel pending
//    - Ringkasan status invoice
//  Dipanggil via AJAX GET saat Admin membuka dashboard.
// ============================================================
session_start();
include __DIR__ . '/../../assets/config.php';
include __DIR__ . '/../../assets/noSessionRedirect.php';

// Endpoint ini HARUS selalu mengembalikan JSON murni.
// Matikan tampilan warning/notice PHP di sini (override DEBUG_MODE),
// supaya tidak ada HTML warning yang ikut tercampur ke response JSON
// — itulah sebab error "Unexpected token '<'... is not valid JSON" di browser.
// Tetap dicatat ke error log PHP (lihat log Apache/PHP) untuk debugging.
ini_set('display_errors', '0');
error_reporting(E_ALL); // tetap log, hanya tidak ditampilkan ke output

header('Content-Type: application/json; charset=utf-8');

if ($_SESSION['role'] !== 'admin') {
    respond('error', 'Akses ditolak.');
}
requireMethod('GET');

// Ambil koneksi database lewat helper getDB() di config.php
$conn = getDB();

// --- 1. Revenue bulan ini (invoice PAID) ---
$sqlRevenue = "SELECT COALESCE(SUM(total_idr), 0) AS revenue_bulan_ini
               FROM invoices
               WHERE status = 'PAID'
                 AND MONTH(invoice_date) = MONTH(CURDATE())
                 AND YEAR(invoice_date)  = YEAR(CURDATE())";
$revRow = mysqli_fetch_assoc(mysqli_query($conn, $sqlRevenue));

// --- 2. Total orders aktif ---
$sqlOrders = "SELECT COUNT(*) AS total_orders_aktif
              FROM orders
              WHERE status IN ('pending','processing')";
$ordRow = mysqli_fetch_assoc(mysqli_query($conn, $sqlOrders));

// --- 3. Buyer overdue (ada invoice OVERDUE & status blocked) ---
$sqlOverdue = "SELECT COUNT(DISTINCT bp.id_buyer) AS total_buyer_overdue
               FROM buyer_profile bp
               JOIN invoices inv ON inv.customer_id = CONCAT('BYR-', LPAD(bp.id_buyer, 4, '0'))
               WHERE inv.status = 'OVERDUE'
                 AND bp.status_verifikasi = 'blocked'";
$ovdRow = mysqli_fetch_assoc(mysqli_query($conn, $sqlOverdue));

// --- 4. Sampel pending ---
$sqlSamples = "SELECT COUNT(*) AS total_sampel_pending
               FROM sample_requests
               WHERE status = 'pending'";
$smpRow = mysqli_fetch_assoc(mysqli_query($conn, $sqlSamples));

// --- 5. Ringkasan status invoice ---
$sqlInvSummary = "SELECT status, COUNT(*) AS jumlah
                  FROM invoices
                  GROUP BY status";
$resInv = mysqli_query($conn, $sqlInvSummary);
$invoiceSummary = [];
while ($r = mysqli_fetch_assoc($resInv)) {
    $invoiceSummary[$r['status']] = (int) $r['jumlah'];
}

// --- 6. Orders baru hari ini ---
$sqlToday = "SELECT COUNT(*) AS order_hari_ini
             FROM orders
             WHERE DATE(tanggal) = CURDATE()";
$todayRow = mysqli_fetch_assoc(mysqli_query($conn, $sqlToday));

respond('success', 'Statistik dashboard berhasil diambil.', [
    'revenue_bulan_ini'    => (float) $revRow['revenue_bulan_ini'],
    'total_orders_aktif'   => (int)   $ordRow['total_orders_aktif'],
    'total_buyer_overdue'  => (int)   $ovdRow['total_buyer_overdue'],
    'total_sampel_pending' => (int)   $smpRow['total_sampel_pending'],
    'order_hari_ini'       => (int)   $todayRow['order_hari_ini'],
    'invoice_summary'      => $invoiceSummary,
]);