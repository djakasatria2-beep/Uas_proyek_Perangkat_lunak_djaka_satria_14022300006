<?php
// ============================================================
//  ThreadB2B — admin_panel/fetch-data/fetchInvoices.php
//  Ambil daftar invoice untuk Admin.
//  Query param opsional:
//    ?status=DRAFT|ISSUED|PAID|OVERDUE|all
//    ?search=<invoice_id / nama perusahaan / customer_id>
//    ?dari=YYYY-MM-DD   ?sampai=YYYY-MM-DD
//    ?page=<int>  ?limit=<int>
//  Dipanggil via AJAX GET dari halaman invoice Admin.
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

$allowedStatus = ['DRAFT', 'ISSUED', 'PAID', 'OVERDUE', 'all'];
$status  = in_array($_GET['status'] ?? 'all', $allowedStatus)
    ? ($_GET['status'] ?? 'all') : 'all';
$search  = trim($_GET['search'] ?? '');
$dari    = trim($_GET['dari']   ?? '');
$sampai  = trim($_GET['sampai'] ?? '');
$page    = max(1, (int)($_GET['page']  ?? 1));
$limit   = min(100, max(1, (int)($_GET['limit'] ?? 20)));
$offset  = ($page - 1) * $limit;

$conditions = [];
$params     = [];
$types      = '';

// Status OVERDUE & ISSUED dihitung berdasarkan due_date, bukan murni kolom status,
// supaya tab "Issued" tidak tumpang tindih dengan tab "Overdue".
if ($status === 'OVERDUE') {
    $conditions[] = "i.status <> 'PAID' AND i.due_date IS NOT NULL AND i.due_date < CURDATE()";
} elseif ($status === 'ISSUED') {
    $conditions[] = "i.status = 'ISSUED' AND (i.due_date IS NULL OR i.due_date >= CURDATE())";
} elseif ($status !== 'all') {
    $conditions[] = 'i.status = ?';
    $params[]     = $status;
    $types       .= 's';
}

if ($search !== '') {
    $conditions[] = '(i.invoice_id LIKE ? OR bp.nama_perusahaan LIKE ? OR i.customer_id LIKE ?)';
    $like = '%' . $search . '%';
    $params[] = $like;
    $params[] = $like;
    $params[] = $like;
    $types   .= 'sss';
}

if ($dari !== '' && preg_match('/^\d{4}-\d{2}-\d{2}$/', $dari)) {
    $conditions[] = 'i.invoice_date >= ?';
    $params[]     = $dari;
    $types       .= 's';
}
if ($sampai !== '' && preg_match('/^\d{4}-\d{2}-\d{2}$/', $sampai)) {
    $conditions[] = 'i.invoice_date <= ?';
    $params[]     = $sampai;
    $types       .= 's';
}

$where = $conditions ? 'WHERE ' . implode(' AND ', $conditions) : '';

// --- Count ---
$sqlCount = "
    SELECT COUNT(*) AS total
    FROM invoices i
    LEFT JOIN buyer_profile bp ON bp.id_buyer = i.customer_id
    $where";
$stmtCount = mysqli_prepare($conn, $sqlCount);
if ($params) {
    mysqli_stmt_bind_param($stmtCount, $types, ...$params);
}
mysqli_stmt_execute($stmtCount);
$total = (int) mysqli_fetch_assoc(mysqli_stmt_get_result($stmtCount))['total'];

// --- Data ---
$sql = "SELECT
            i.invoice_id,
            i.invoice_date,
            i.customer_id,
            bp.nama_perusahaan,
            i.credit_days,
            i.due_date,
            i.subtotal_idr,
            i.ppn_pct,
            i.ppn_idr,
            i.total_idr,
            i.created_by,
            i.status,
            i.created_at,
            IF(i.status <> 'PAID' AND i.due_date IS NOT NULL AND i.due_date < CURDATE(), 'OVERDUE', i.status) AS status_display
        FROM invoices i
        LEFT JOIN buyer_profile bp ON bp.id_buyer = i.customer_id
        $where
        ORDER BY i.invoice_date DESC, i.invoice_id DESC
        LIMIT ? OFFSET ?";

$allParams = array_merge($params, [$limit, $offset]);
$allTypes  = $types . 'ii';
$stmt      = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, $allTypes, ...$allParams);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

$invoices = [];
while ($row = mysqli_fetch_assoc($result)) {
    $row['subtotal_idr'] = (float) $row['subtotal_idr'];
    $row['ppn_idr']      = (float) $row['ppn_idr'];
    $row['total_idr']    = (float) $row['total_idr'];
    $invoices[] = $row;
}

respond('success', 'Daftar invoice berhasil diambil.', [
    'invoices'   => $invoices,
    'pagination' => [
        'total'       => $total,
        'page'        => $page,
        'limit'       => $limit,
        'total_pages' => (int) ceil($total / $limit),
    ],
]);