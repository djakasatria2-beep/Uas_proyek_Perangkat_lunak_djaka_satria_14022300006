<?php
// ============================================================
//  ThreadB2B — assets/fetchReportBuyers.php
//  Laporan top buyer berdasarkan nilai transaksi (Admin only).
//  Query params (GET):
//    date_from = YYYY-MM-DD
//    date_to   = YYYY-MM-DD
//    limit     = INT (default 10, max 50)
// ============================================================

session_start();
include __DIR__ . '/config.php';
include __DIR__ . '/noSessionRedirect.php';
header('Content-Type: application/json; charset=utf-8');

requireMethod('GET');

if ($_SESSION['role'] !== 'admin') {
    respond('error', 'Akses ditolak. Hanya Admin.');
}

$dateFrom = trim($_GET['date_from'] ?? '');
$dateTo   = trim($_GET['date_to']   ?? '');
$limit    = min(50, max(1, (int)($_GET['limit'] ?? 10)));

$conditions = ["o.status NOT IN ('cancelled')"];
$params     = [];
$types      = '';

if ($dateFrom !== '') {
    $conditions[] = 'o.tanggal >= ?';
    $params[]     = $dateFrom;
    $types       .= 's';
}
if ($dateTo !== '') {
    $conditions[] = 'o.tanggal <= ?';
    $params[]     = $dateTo;
    $types       .= 's';
}

$where = 'WHERE ' . implode(' AND ', $conditions);

$sql = "SELECT bp.id_buyer, bp.nama_perusahaan, bp.nama_pic, bp.negara,
               COUNT(DISTINCT o.id_order) AS jumlah_order,
               SUM(o.qty)                AS total_qty,
               SUM(o.qty * o.harga_benang) AS total_nilai
        FROM orders o
        JOIN buyer_profile bp ON bp.id_buyer = o.id_buyer
        $where
        GROUP BY bp.id_buyer
        ORDER BY total_nilai DESC
        LIMIT ?";

$params[] = $limit;
$types   .= 'i';

$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, $types, ...$params);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

$buyers = [];
while ($r = mysqli_fetch_assoc($result)) {
    $buyers[] = $r;
}

respond('success', 'Laporan top buyer berhasil diambil.', [
    'date_from' => $dateFrom ?: null,
    'date_to'   => $dateTo   ?: null,
    'limit'     => $limit,
    'buyers'    => $buyers,
]);
