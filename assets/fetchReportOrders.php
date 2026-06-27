<?php
// ============================================================
//  ThreadB2B — assets/fetchReportOrders.php
//  Data laporan pesanan per jenis benang (Admin only).
//  Query params (GET):
//    date_from = YYYY-MM-DD
//    date_to   = YYYY-MM-DD
//    group_by  = jenis_benang | bulan | status  (default: jenis_benang)
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
$groupBy  = trim($_GET['group_by']  ?? 'jenis_benang');

$validGroup = ['jenis_benang', 'bulan', 'status'];
if (!in_array($groupBy, $validGroup)) $groupBy = 'jenis_benang';

$conditions = ["status NOT IN ('cancelled')"];
$params     = [];
$types      = '';

if ($dateFrom !== '') {
    $conditions[] = 'tanggal >= ?';
    $params[]     = $dateFrom;
    $types       .= 's';
}
if ($dateTo !== '') {
    $conditions[] = 'tanggal <= ?';
    $params[]     = $dateTo;
    $types       .= 's';
}

$where = 'WHERE ' . implode(' AND ', $conditions);

$selectGroup = match ($groupBy) {
    'bulan'  => "DATE_FORMAT(tanggal, '%Y-%m') AS group_label",
    'status' => "status AS group_label",
    default  => "jenis_benang AS group_label",
};
$groupExpr = match ($groupBy) {
    'bulan'  => "DATE_FORMAT(tanggal, '%Y-%m')",
    'status' => "status",
    default  => "jenis_benang",
};

$sql = "SELECT $selectGroup,
               COUNT(*) AS jumlah_order,
               SUM(qty) AS total_qty,
               SUM(qty * harga_benang) AS total_nilai
        FROM orders
        $where
        GROUP BY $groupExpr
        ORDER BY total_nilai DESC";

$stmt = mysqli_prepare($conn, $sql);
if ($params) mysqli_stmt_bind_param($stmt, $types, ...$params);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

$rows = [];
while ($r = mysqli_fetch_assoc($result)) {
    $rows[] = $r;
}

// Grand total
$sqlTotal = "SELECT COUNT(*) AS total_order, SUM(qty) AS total_qty,
                    SUM(qty * harga_benang) AS total_nilai
             FROM orders $where";
$stmtT = mysqli_prepare($conn, $sqlTotal);
if ($params) mysqli_stmt_bind_param($stmtT, $types, ...$params);
mysqli_stmt_execute($stmtT);
$grand = mysqli_fetch_assoc(mysqli_stmt_get_result($stmtT));

respond('success', 'Data laporan berhasil diambil.', [
    'group_by'    => $groupBy,
    'date_from'   => $dateFrom ?: null,
    'date_to'     => $dateTo   ?: null,
    'data'        => $rows,
    'grand_total' => $grand,
]);
