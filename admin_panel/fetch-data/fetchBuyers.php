<?php
// ============================================================
//  ThreadB2B — admin_panel/fetch-data/fetchBuyers.php
//  Ambil daftar buyer dengan filter status verifikasi.
//  Query param opsional:
//    ?status=pending|approved|rejected|blocked|all  (default: all)
//    ?search=<nama_perusahaan|nama_pic>
//    ?page=<int>  ?limit=<int>
//  Dipanggil via AJAX GET dari halaman manajemen buyer Admin.
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

$allowedStatus = ['pending', 'approved', 'rejected', 'blocked', 'all'];
$status = in_array($_GET['status'] ?? 'all', $allowedStatus)
    ? ($_GET['status'] ?? 'all')
    : 'all';
$search = trim($_GET['search'] ?? '');
$page   = max(1, (int)($_GET['page']  ?? 1));
$limit  = min(100, max(1, (int)($_GET['limit'] ?? 20)));
$offset = ($page - 1) * $limit;

// --- Bangun WHERE ---
$conditions = [];
$params     = [];
$types      = '';

if ($status !== 'all') {
    $conditions[] = 'bp.status_verifikasi = ?';
    $params[]     = $status;
    $types       .= 's';
}
if ($search !== '') {
    $like         = "%{$search}%";
    $conditions[] = '(bp.nama_perusahaan LIKE ? OR bp.nama_pic LIKE ?)';
    $params[]     = $like;
    $params[]     = $like;
    $types       .= 'ss';
}
$where = $conditions ? 'WHERE ' . implode(' AND ', $conditions) : '';

// --- Total rows untuk pagination ---
$sqlCount = "SELECT COUNT(*) AS total
             FROM buyer_profile bp
             $where";
$stmtCount = mysqli_prepare($conn, $sqlCount);
if ($params) {
    mysqli_stmt_bind_param($stmtCount, $types, ...$params);
}
mysqli_stmt_execute($stmtCount);
$total = (int) mysqli_fetch_assoc(mysqli_stmt_get_result($stmtCount))['total'];

// --- Data utama ---
$sql = "SELECT
            bp.id_buyer,
            CONCAT('BYR-', LPAD(bp.id_buyer, 4, '0')) AS customer_id,
            bp.nama_perusahaan,
            bp.nama_pic,
            bp.no_whatsapp,
            bp.negara,
            bp.status_verifikasi,
            bp.tenor_hari,
            bp.tanggal_diblokir,
            u.email,
            u.created_at AS terdaftar_pada,
            COUNT(DISTINCT inv.invoice_id) AS total_invoice,
            SUM(CASE WHEN inv.status = 'OVERDUE' THEN 1 ELSE 0 END) AS invoice_overdue
        FROM buyer_profile bp
        JOIN users u ON u.id_user = bp.id_user
        LEFT JOIN invoices inv
            ON inv.customer_id = CONCAT('BYR-', LPAD(bp.id_buyer, 4, '0'))
        $where
        GROUP BY bp.id_buyer
        ORDER BY bp.id_buyer DESC
        LIMIT ? OFFSET ?";
$allParams   = array_merge($params, [$limit, $offset]);
$allTypes    = $types . 'ii';
$stmt        = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, $allTypes, ...$allParams);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

$buyers = [];
while ($row = mysqli_fetch_assoc($result)) {
    $row['invoice_overdue'] = (int) $row['invoice_overdue'];
    $row['total_invoice']   = (int) $row['total_invoice'];
    $buyers[] = $row;
}

respond('success', 'Daftar buyer berhasil diambil.', [
    'buyers'     => $buyers,
    'pagination' => [
        'total'       => $total,
        'page'        => $page,
        'limit'       => $limit,
        'total_pages' => (int) ceil($total / $limit),
    ],
]);