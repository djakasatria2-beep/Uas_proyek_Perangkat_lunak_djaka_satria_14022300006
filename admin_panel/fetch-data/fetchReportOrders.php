<?php
// ============================================================
//  ThreadB2B — admin_panel/fetch-data/fetchReportOrders.php
//  Ambil data laporan pesanan per jenis benang.
//  Query param opsional:
//    ?dari=YYYY-MM-DD   ?sampai=YYYY-MM-DD
//    ?status=pending|processing|shipped|done|cancelled|all
//    ?group_by=jenis_benang|bulan|status  (default: jenis_benang)
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

$dari     = $_GET['dari']    ?? date('Y-m-01');          // awal bulan ini
$sampai   = $_GET['sampai']  ?? date('Y-m-d');           // hari ini
$status   = $_GET['status']  ?? 'all';
$groupBy  = $_GET['group_by'] ?? 'jenis_benang';

$allowedStatus  = ['pending','processing','shipped','done','cancelled','all'];
$allowedGroupBy = ['jenis_benang','bulan','status'];

if (!in_array($status, $allowedStatus)) {
    respond('error', 'Parameter status tidak valid.');
}
if (!in_array($groupBy, $allowedGroupBy)) {
    respond('error', 'Parameter group_by tidak valid.');
}

// --- Validasi format tanggal ---
foreach (['dari' => $dari, 'sampai' => $sampai] as $key => $val) {
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $val)) {
        respond('error', "Format tanggal '$key' tidak valid (YYYY-MM-DD).");
    }
}

$conditions = ['o.tanggal BETWEEN ? AND ?'];
$params     = [$dari, $sampai];
$types      = 'ss';

if ($status !== 'all') {
    $conditions[] = 'o.status = ?';
    $params[]     = $status;
    $types       .= 's';
}

$where = 'WHERE ' . implode(' AND ', $conditions);

// --- Tentukan SELECT & GROUP BY dinamis ---
$selectGroup = match ($groupBy) {
    'bulan'       => "DATE_FORMAT(o.tanggal, '%Y-%m') AS periode",
    'status'      => "o.status AS periode",
    default       => "o.jenis_benang AS periode",
};

$sqlGroup = match ($groupBy) {
    'bulan'  => "DATE_FORMAT(o.tanggal, '%Y-%m')",
    'status' => "o.status",
    default  => "o.jenis_benang",
};

$sql = "SELECT
            $selectGroup,
            COUNT(*)              AS total_order,
            SUM(o.qty)            AS total_qty,
            SUM(o.qty * o.harga_benang) AS total_nilai,
            AVG(o.harga_benang)   AS rata_harga
        FROM orders o
        $where
        GROUP BY $sqlGroup
        ORDER BY total_nilai DESC";

$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, $types, ...$params);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

$rows = [];
while ($row = mysqli_fetch_assoc($result)) {
    $row['total_order'] = (int)   $row['total_order'];
    $row['total_qty']   = (int)   $row['total_qty'];
    $row['total_nilai'] = (float) $row['total_nilai'];
    $row['rata_harga']  = (float) $row['rata_harga'];
    $rows[] = $row;
}

// --- Agregat total keseluruhan ---
$sqlTotal = "SELECT COUNT(*) AS total_order, SUM(qty) AS total_qty,
                    SUM(qty * harga_benang) AS total_nilai
             FROM orders o $where";
$stmtT = mysqli_prepare($conn, $sqlTotal);
mysqli_stmt_bind_param($stmtT, $types, ...$params);
mysqli_stmt_execute($stmtT);
$totals = mysqli_fetch_assoc(mysqli_stmt_get_result($stmtT));

respond('success', 'Laporan order berhasil diambil.', [
    'filter'   => compact('dari', 'sampai', 'status', 'groupBy'),
    'data'     => $rows,
    'totals'   => [
        'total_order' => (int)   $totals['total_order'],
        'total_qty'   => (int)   $totals['total_qty'],
        'total_nilai' => (float) $totals['total_nilai'],
    ],
]);