<?php
// ============================================================
//  ThreadB2B — admin_panel/fetch-data/fetchSamples.php
//  Ambil daftar permintaan sampel untuk Admin.
//  Query param opsional:
//    ?status=pending|waiting_result|result_ready|approved|rejected|revision|all
//    ?id_buyer=<int>
//    ?dari=YYYY-MM-DD   ?sampai=YYYY-MM-DD
//    ?page=<int>  ?limit=<int>
//  Dipanggil via AJAX GET dari halaman sampel Admin.
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

$allowedStatus = ['pending','waiting_result','result_ready','approved','rejected','revision','all'];
$status  = in_array($_GET['status'] ?? 'all', $allowedStatus)
    ? ($_GET['status'] ?? 'all') : 'all';
$idBuyer = (int)($_GET['id_buyer'] ?? 0);
$dari    = trim($_GET['dari']   ?? '');
$sampai  = trim($_GET['sampai'] ?? '');
$page    = max(1, (int)($_GET['page']  ?? 1));
$limit   = min(100, max(1, (int)($_GET['limit'] ?? 20)));
$offset  = ($page - 1) * $limit;

$conditions = [];
$params     = [];
$types      = '';

if ($status !== 'all') {
    $conditions[] = 'sr.status = ?';
    $params[]     = $status;
    $types       .= 's';
}
if ($idBuyer > 0) {
    $conditions[] = 'sr.id_buyer = ?';
    $params[]     = $idBuyer;
    $types       .= 'i';
}
if ($dari !== '' && preg_match('/^\d{4}-\d{2}-\d{2}$/', $dari)) {
    $conditions[] = 'sr.tanggal >= ?';
    $params[]     = $dari;
    $types       .= 's';
}
if ($sampai !== '' && preg_match('/^\d{4}-\d{2}-\d{2}$/', $sampai)) {
    $conditions[] = 'sr.tanggal <= ?';
    $params[]     = $sampai;
    $types       .= 's';
}

$where = $conditions ? 'WHERE ' . implode(' AND ', $conditions) : '';

// --- Count ---
$sqlCount = "SELECT COUNT(*) AS total FROM sample_requests sr $where";
$stmtCount = mysqli_prepare($conn, $sqlCount);
if ($params) {
    mysqli_stmt_bind_param($stmtCount, $types, ...$params);
}
mysqli_stmt_execute($stmtCount);
$total = (int) mysqli_fetch_assoc(mysqli_stmt_get_result($stmtCount))['total'];

// --- Data ---
$sql = "SELECT
            sr.id_request,
            sr.id_buyer,
            bp.nama_perusahaan,
            bp.nama_pic,
            sr.jenis_benang,
            sr.ukuran_benang,
            sr.kode_warna_target,
            sr.upload_sampel,
            sr.tanggal,
            sr.tanggal_dibutuhkan,
            sr.catatan,
            sr.status,
            sres.id_result,
            sres.kode_warna_hasil,
            sres.pilihan,
            sres.nilai_delta_e,
            sres.status_approval,
            sres.gambar AS gambar_hasil
        FROM sample_requests sr
        JOIN buyer_profile bp ON bp.id_buyer = sr.id_buyer
        LEFT JOIN sample_results sres ON sres.id_request = sr.id_request
        $where
        ORDER BY sr.tanggal DESC, sr.id_request DESC
        LIMIT ? OFFSET ?";

$allParams = array_merge($params, [$limit, $offset]);
$allTypes  = $types . 'ii';
$stmt      = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, $allTypes, ...$allParams);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

$samples = [];
while ($row = mysqli_fetch_assoc($result)) {
    $samples[] = $row;
}

respond('success', 'Daftar sampel berhasil diambil.', [
    'samples'    => $samples,
    'pagination' => [
        'total'       => $total,
        'page'        => $page,
        'limit'       => $limit,
        'total_pages' => (int) ceil($total / $limit),
    ],
]);