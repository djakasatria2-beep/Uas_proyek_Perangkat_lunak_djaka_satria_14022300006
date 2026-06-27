<?php
// ============================================================
//  ThreadB2B — assets/fetchInvoices.php
//  Ambil daftar invoice.
//  - Buyer     : hanya invoice miliknya (match customer_id)
//  - Admin     : semua invoice
//  - Marketing : tidak punya akses invoice (403)
//  Query params (GET):
//    status      = DRAFT|ISSUED|PAID|OVERDUE
//    customer_id = STRING  (Admin saja)
//    dari        = YYYY-MM-DD  (invoice_date)
//    sampai      = YYYY-MM-DD
//    keyword     = invoice_id / customer_id
//    page, per_page
// ============================================================

session_start();
include __DIR__ . '/config.php';
include __DIR__ . '/noSessionRedirect.php';
header('Content-Type: application/json; charset=utf-8');

requireMethod('GET');

$role    = $_SESSION['role'];
$idBuyer = (int)($_SESSION['id_buyer'] ?? 0);

if ($role === 'marketing') {
    respond('error', 'Akses ditolak. Marketing tidak memiliki akses ke data invoice.');
}

// --- Bangun customer_id buyer yang login ---
$buyerCustomerId = 'BYR-' . str_pad($idBuyer, 4, '0', STR_PAD_LEFT);

// --- Params ---
$status     = strtoupper(trim($_GET['status']      ?? ''));
$filterCust = trim($_GET['customer_id']            ?? '');
$dari       = trim($_GET['dari']                   ?? '');
$sampai     = trim($_GET['sampai']                 ?? '');
$keyword    = trim($_GET['keyword']                ?? '');
$page       = max(1, (int)($_GET['page']           ?? 1));
$perPage    = min(100, max(1, (int)($_GET['per_page'] ?? 20)));
$offset     = ($page - 1) * $perPage;

$conditions = [];
$params     = [];
$types      = '';

// Buyer hanya lihat miliknya
if ($role === 'buyer') {
    $conditions[] = 'i.customer_id = ?';
    $params[]     = $buyerCustomerId;
    $types       .= 's';
} elseif ($role === 'admin' && $filterCust !== '') {
    $conditions[] = 'i.customer_id = ?';
    $params[]     = $filterCust;
    $types       .= 's';
}

$validStatuses = ['DRAFT', 'ISSUED', 'PAID', 'OVERDUE'];
if ($status !== '' && in_array($status, $validStatuses)) {
    $conditions[] = 'i.status = ?';
    $params[]     = $status;
    $types       .= 's';
}
if ($dari !== '') {
    $conditions[] = 'i.invoice_date >= ?';
    $params[]     = $dari;
    $types       .= 's';
}
if ($sampai !== '') {
    $conditions[] = 'i.invoice_date <= ?';
    $params[]     = $sampai;
    $types       .= 's';
}
if ($keyword !== '') {
    $conditions[] = '(i.invoice_id LIKE ? OR i.customer_id LIKE ?)';
    $kw = '%' . $keyword . '%';
    $params[] = $kw;
    $params[] = $kw;
    $types   .= 'ss';
}

$where = count($conditions) ? 'WHERE ' . implode(' AND ', $conditions) : '';

// --- Count total ---
$sqlCount  = "SELECT COUNT(*) AS total FROM invoices i $where";
$stmtCount = mysqli_prepare($conn, $sqlCount);
if ($params) mysqli_stmt_bind_param($stmtCount, $types, ...$params);
mysqli_stmt_execute($stmtCount);
$total = (int)mysqli_fetch_assoc(mysqli_stmt_get_result($stmtCount))['total'];

// --- Data ---
$sqlData = "SELECT i.invoice_id, i.invoice_date, i.customer_id,
                   i.credit_days, i.due_date,
                   i.subtotal_idr, i.ppn_pct, i.ppn_idr, i.total_idr,
                   i.created_by, i.status, i.created_at,
                   -- Flag overdue untuk tampilan
                   CASE
                     WHEN i.status NOT IN ('PAID') AND i.due_date < CURDATE()
                     THEN 1 ELSE 0
                   END AS is_overdue,
                   -- Hitung sisa hari jatuh tempo
                   DATEDIFF(i.due_date, CURDATE()) AS sisa_hari
            FROM invoices i
            $where
            ORDER BY i.invoice_date DESC
            LIMIT ? OFFSET ?";

$allParams = array_merge($params, [$perPage, $offset]);
$stmtData  = mysqli_prepare($conn, $sqlData);
mysqli_stmt_bind_param($stmtData, $types . 'ii', ...$allParams);
mysqli_stmt_execute($stmtData);
$result   = mysqli_stmt_get_result($stmtData);

$invoices = [];
while ($row = mysqli_fetch_assoc($result)) {
    $invoices[] = $row;
}

respond('success', 'Data berhasil diambil.', [
    'invoices'    => $invoices,
    'total'       => $total,
    'page'        => $page,
    'per_page'    => $perPage,
    'total_pages' => (int)ceil($total / $perPage),
]);