<?php
// ============================================================
//  ThreadB2B — assets/fetchSamples.php
//  Ambil daftar permintaan sampel.
//  - Buyer     : hanya miliknya sendiri
//  - Marketing/Admin : semua + info buyer
//  Query params (GET):
//    status   = pending|waiting_result|result_ready|approved|rejected|revision
//    id_buyer = INT  (Marketing/Admin)
//    keyword  = jenis_benang / nama_perusahaan
//    page, per_page
// ============================================================

session_start();
include __DIR__ . '/config.php';
include __DIR__ . '/noSessionRedirect.php';
header('Content-Type: application/json; charset=utf-8');

requireMethod('GET');

$role    = $_SESSION['role'];
$idBuyer = (int)($_SESSION['id_buyer'] ?? 0);

$status      = trim($_GET['status']   ?? '');
$filterBuyer = (int)($_GET['id_buyer'] ?? 0);
$keyword     = trim($_GET['keyword']   ?? '');
$page        = max(1, (int)($_GET['page']     ?? 1));
$perPage     = min(100, max(1, (int)($_GET['per_page'] ?? 20)));
$offset      = ($page - 1) * $perPage;

$conditions = [];
$params     = [];
$types      = '';

// Batasi berdasarkan role
if ($role === 'buyer') {
    $conditions[] = 'sr.id_buyer = ?';
    $params[]     = $idBuyer;
    $types       .= 'i';
} elseif (in_array($role, ['marketing','admin']) && $filterBuyer > 0) {
    $conditions[] = 'sr.id_buyer = ?';
    $params[]     = $filterBuyer;
    $types       .= 'i';
}

$validStatuses = ['pending','waiting_result','result_ready','approved','rejected','revision'];
if ($status !== '' && in_array($status, $validStatuses)) {
    $conditions[] = 'sr.status = ?';
    $params[]     = $status;
    $types       .= 's';
}
if ($keyword !== '') {
    $conditions[] = '(sr.jenis_benang LIKE ? OR bp.nama_perusahaan LIKE ?)';
    $kw = '%' . $keyword . '%';
    $params[] = $kw;
    $params[] = $kw;
    $types   .= 'ss';
}

$where = count($conditions) ? 'WHERE ' . implode(' AND ', $conditions) : '';

// Count
$sqlCount = "SELECT COUNT(*) AS total
             FROM sample_requests sr
             JOIN buyer_profile bp ON bp.id_buyer = sr.id_buyer
             $where";
$stmtCount = mysqli_prepare($conn, $sqlCount);
if ($params) mysqli_stmt_bind_param($stmtCount, $types, ...$params);
mysqli_stmt_execute($stmtCount);
$total = (int)mysqli_fetch_assoc(mysqli_stmt_get_result($stmtCount))['total'];

// Data
$sqlData = "SELECT sr.id_request, sr.jenis_benang, sr.ukuran_benang,
                   sr.kode_warna_target, sr.upload_sampel,
                   sr.tanggal, sr.tanggal_dibutuhkan, sr.catatan, sr.status,
                   bp.nama_perusahaan, bp.nama_pic, bp.id_buyer,
                   res.kode_warna_hasil, res.nilai_delta_e, res.status_approval
            FROM sample_requests sr
            JOIN buyer_profile bp ON bp.id_buyer = sr.id_buyer
            LEFT JOIN sample_results res ON res.id_request = sr.id_request
            $where
            ORDER BY sr.id_request DESC
            LIMIT ? OFFSET ?";

$allParams = array_merge($params, [$perPage, $offset]);
$stmtData  = mysqli_prepare($conn, $sqlData);
mysqli_stmt_bind_param($stmtData, $types . 'ii', ...$allParams);
mysqli_stmt_execute($stmtData);
$result  = mysqli_stmt_get_result($stmtData);

$samples = [];
while ($row = mysqli_fetch_assoc($result)) {
    $samples[] = $row;
}

respond('success', 'Data berhasil diambil.', [
    'samples'     => $samples,
    'total'       => $total,
    'page'        => $page,
    'per_page'    => $perPage,
    'total_pages' => (int)ceil($total / $perPage),
]);