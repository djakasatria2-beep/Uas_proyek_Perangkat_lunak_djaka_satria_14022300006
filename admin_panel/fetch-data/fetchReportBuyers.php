<?php
// ============================================================
//  ThreadB2B — admin_panel/fetch-data/fetchReportBuyers.php
//  Ambil data laporan top buyer berdasarkan nilai transaksi.
//  Query param opsional:
//    ?dari=YYYY-MM-DD   ?sampai=YYYY-MM-DD
//    ?limit=<int>  (default 10, max 50)
//  Dipanggil via AJAX GET dari halaman laporan Admin.
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

$dari   = $_GET['dari']   ?? date('Y-m-01');
$sampai = $_GET['sampai'] ?? date('Y-m-d');
$limit  = min(50, max(1, (int)($_GET['limit'] ?? 10)));

foreach (['dari' => $dari, 'sampai' => $sampai] as $key => $val) {
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $val)) {
        respond('error', "Format tanggal '$key' tidak valid (YYYY-MM-DD).");
    }
}

// --- Top buyer berdasarkan total nilai invoice PAID dalam rentang tanggal ---
$sql = "SELECT
            inv.customer_id,
            bp.nama_perusahaan,
            bp.nama_pic,
            bp.negara,
            bp.status_verifikasi,
            COUNT(DISTINCT inv.invoice_id) AS total_invoice,
            SUM(inv.total_idr)             AS total_transaksi,
            COUNT(DISTINCT o.id_order)     AS total_order
        FROM invoices inv
        JOIN buyer_profile bp
            ON bp.id_buyer = CAST(SUBSTRING(inv.customer_id, 5) AS UNSIGNED)
        LEFT JOIN orders o ON o.id_buyer = bp.id_buyer
            AND o.tanggal BETWEEN ? AND ?
        WHERE inv.status = 'PAID'
          AND inv.invoice_date BETWEEN ? AND ?
        GROUP BY inv.customer_id
        ORDER BY total_transaksi DESC
        LIMIT ?";

$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, 'ssssi', $dari, $sampai, $dari, $sampai, $limit);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

$buyers = [];
$rank   = 1;
while ($row = mysqli_fetch_assoc($result)) {
    $row['rank']            = $rank++;
    $row['total_invoice']   = (int)   $row['total_invoice'];
    $row['total_transaksi'] = (float) $row['total_transaksi'];
    $row['total_order']     = (int)   $row['total_order'];
    $buyers[] = $row;
}

respond('success', 'Laporan top buyer berhasil diambil.', [
    'filter' => compact('dari', 'sampai', 'limit'),
    'data'   => $buyers,
]);