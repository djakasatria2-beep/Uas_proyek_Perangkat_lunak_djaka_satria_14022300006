<?php
// ============================================================
//  ThreadB2B — assets/fetchOrderDetail.php
//  Ambil detail satu pesanan beserta milestone tracking.
//  Method : GET
//  Params : ?id_order=INT  ATAU  ?no_order=SO-YYYY-NNNNN
// ============================================================

session_start();
include __DIR__ . '/config.php';
include __DIR__ . '/noSessionRedirect.php';
header('Content-Type: application/json; charset=utf-8');

requireMethod('GET');

$role    = $_SESSION['role'];
$idBuyer = (int)($_SESSION['id_buyer'] ?? 0);

$idOrder  = (int)($_GET['id_order']  ?? 0);
$noOrder  = trim($_GET['no_order']   ?? '');

if ($idOrder === 0 && $noOrder === '') {
    respond('error', 'Parameter id_order atau no_order diperlukan.');
}

// --- Ambil data order ---
if ($idOrder > 0) {
    $wherePk = 'o.id_order = ?';
    $pkVal   = $idOrder;
    $pkType  = 'i';
} else {
    $wherePk = 'o.no_order = ?';
    $pkVal   = $noOrder;
    $pkType  = 's';
}

$sql = "SELECT o.id_order, o.no_order, o.kode_warna, o.nama_warna,
               o.jenis_benang, o.ukuran_benang, o.qty, o.harga_benang,
               o.tanggal, o.status, o.catatan,
               bp.id_buyer, bp.nama_perusahaan, bp.nama_pic,
               bp.no_whatsapp, bp.alamat
        FROM orders o
        JOIN buyer_profile bp ON bp.id_buyer = o.id_buyer
        WHERE $wherePk
        LIMIT 1";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, $pkType, $pkVal);
mysqli_stmt_execute($stmt);
$order = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

if (!$order) {
    respond('error', 'Pesanan tidak ditemukan.');
}

// --- Otorisasi: Buyer hanya boleh lihat pesanan sendiri ---
if ($role === 'buyer' && (int)$order['id_buyer'] !== $idBuyer) {
    respond('error', 'Akses ditolak.');
}

// --- Ambil milestone tracking ---
$sqlTracking = "SELECT t.id_tracking, t.status, t.keterangan, t.tanggal,
                       u.email AS updated_by_email
                FROM tracking t
                JOIN users u ON u.id_user = t.updated_by
                WHERE t.id_order = ?
                ORDER BY t.tanggal ASC";
$stmtTr = mysqli_prepare($conn, $sqlTracking);
mysqli_stmt_bind_param($stmtTr, 'i', $order['id_order']);
mysqli_stmt_execute($stmtTr);
$resultTr  = mysqli_stmt_get_result($stmtTr);
$milestones = [];
while ($row = mysqli_fetch_assoc($resultTr)) {
    $milestones[] = $row;
}

respond('success', 'Data berhasil diambil.', [
    'order'      => $order,
    'milestones' => $milestones,
]);