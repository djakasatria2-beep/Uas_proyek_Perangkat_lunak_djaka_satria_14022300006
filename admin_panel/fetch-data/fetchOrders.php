<?php
// ============================================================
//  ThreadB2B — admin_panel/fetch-data/fetchOrders.php
//  Ambil semua pesanan dengan filter lengkap untuk Admin.
//  Query param opsional:
//    ?status=pending|processing|shipped|done|cancelled|all
//    ?id_buyer=<int>
//    ?dari=YYYY-MM-DD   ?sampai=YYYY-MM-DD
//    ?search=<no_order|jenis_benang>
//    ?page=<int>  ?limit=<int>
//  Dipanggil via AJAX GET dari halaman orders Admin.
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

$allowedStatus = ['pending','processing','shipped','done','cancelled','all'];
$status   = in_array($_GET['status'] ?? 'all', $allowedStatus)
    ? ($_GET['status'] ?? 'all') : 'all';
$idBuyer  = (int)($_GET['id_buyer'] ?? 0);
$dari     = trim($_GET['dari']   ?? '');
$sampai   = trim($_GET['sampai'] ?? '');
$search   = trim($_GET['search'] ?? '');
$page     = max(1, (int)($_GET['page']  ?? 1));
$limit    = min(100, max(1, (int)($_GET['limit'] ?? 20)));
$offset   = ($page - 1) * $limit;

$conditions = [];
$params     = [];
$types      = '';

if ($status !== 'all') {
    $conditions[] = 'o.status = ?';
    $params[]     = $status;
    $types       .= 's';
}
if ($idBuyer > 0) {
    $conditions[] = 'o.id_buyer = ?';
    $params[]     = $idBuyer;
    $types       .= 'i';
}
if ($dari !== '' && preg_match('/^\d{4}-\d{2}-\d{2}$/', $dari)) {
    $conditions[] = 'o.tanggal >= ?';
    $params[]     = $dari;
    $types       .= 's';
}
if ($sampai !== '' && preg_match('/^\d{4}-\d{2}-\d{2}$/', $sampai)) {
    $conditions[] = 'o.tanggal <= ?';
    $params[]     = $sampai;
    $types       .= 's';
}
if ($search !== '') {
    $like         = "%{$search}%";
    $conditions[] = '(o.no_order LIKE ? OR o.jenis_benang LIKE ?)';
    $params[]     = $like;
    $params[]     = $like;
    $types       .= 'ss';
}

$where = $conditions ? 'WHERE ' . implode(' AND ', $conditions) : '';

// --- Count ---
$sqlCount = "SELECT COUNT(*) AS total FROM orders o $where";
$stmtCount = mysqli_prepare($conn, $sqlCount);
if ($params) {
    mysqli_stmt_bind_param($stmtCount, $types, ...$params);
}
mysqli_stmt_execute($stmtCount);
$total = (int) mysqli_fetch_assoc(mysqli_stmt_get_result($stmtCount))['total'];

// --- Data ---
$sql = "SELECT
            o.id_order,
            o.no_order,
            o.id_buyer,
            bp.nama_perusahaan,
            bp.nama_pic,
            o.jenis_benang,
            o.ukuran_benang,
            o.kode_warna,
            o.nama_warna,
            o.qty,
            o.harga_benang,
            (o.qty * o.harga_benang) AS total_nilai,
            o.tanggal,
            o.status,
            o.catatan,
            (SELECT t.status FROM tracking t
             WHERE t.id_order = o.id_order
             ORDER BY t.tanggal DESC LIMIT 1) AS tracking_terakhir
        FROM orders o
        JOIN buyer_profile bp ON bp.id_buyer = o.id_buyer
        $where
        ORDER BY o.tanggal DESC, o.id_order DESC
        LIMIT ? OFFSET ?";

$allParams = array_merge($params, [$limit, $offset]);
$allTypes  = $types . 'ii';
$stmt      = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, $allTypes, ...$allParams);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

$orders = [];
while ($row = mysqli_fetch_assoc($result)) {
    $row['total_nilai'] = (float) $row['total_nilai'];
    $orders[] = $row;
}

respond('success', 'Daftar pesanan berhasil diambil.', [
    'orders'     => $orders,
    'pagination' => [
        'total'       => $total,
        'page'        => $page,
        'limit'       => $limit,
        'total_pages' => (int) ceil($total / $limit),
    ],
]);