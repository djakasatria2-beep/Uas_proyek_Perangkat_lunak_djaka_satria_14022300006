<?php
// ============================================================
//  ThreadB2B — assets/fetchSampleDetail.php
//  Ambil detail satu permintaan sampel + hasil sampel.
//  Method : GET
//  Params : ?id_request=INT
// ============================================================

session_start();
include __DIR__ . '/config.php';
include __DIR__ . '/noSessionRedirect.php';
header('Content-Type: application/json; charset=utf-8');

requireMethod('GET');

$role      = $_SESSION['role'];
$idBuyer   = (int)($_SESSION['id_buyer'] ?? 0);
$idRequest = (int)($_GET['id_request']   ?? 0);

if ($idRequest === 0) {
    respond('error', 'Parameter id_request diperlukan.');
}

// --- Ambil data permintaan ---
$sql = "SELECT sr.id_request, sr.id_buyer, sr.jenis_benang, sr.ukuran_benang,
               sr.kode_warna_target, sr.upload_sampel,
               sr.tanggal, sr.tanggal_dibutuhkan, sr.catatan, sr.status,
               bp.nama_perusahaan, bp.nama_pic, bp.no_whatsapp
        FROM sample_requests sr
        JOIN buyer_profile bp ON bp.id_buyer = sr.id_buyer
        WHERE sr.id_request = ?
        LIMIT 1";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, 'i', $idRequest);
mysqli_stmt_execute($stmt);
$sample = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

if (!$sample) {
    respond('error', 'Permintaan sampel tidak ditemukan.');
}

// --- Otorisasi buyer ---
if ($role === 'buyer' && (int)$sample['id_buyer'] !== $idBuyer) {
    respond('error', 'Akses ditolak.');
}

// --- Ambil hasil sampel (jika ada) ---
$sqlResult = "SELECT id_result, kode_warna_hasil, pilihan, gambar,
                     nilai_delta_e, catatan, status_approval
              FROM sample_results
              WHERE id_request = ?
              LIMIT 1";
$stmtRes = mysqli_prepare($conn, $sqlResult);
mysqli_stmt_bind_param($stmtRes, 'i', $idRequest);
mysqli_stmt_execute($stmtRes);
$sampleResult = mysqli_fetch_assoc(mysqli_stmt_get_result($stmtRes));

respond('success', 'Data berhasil diambil.', [
    'sample' => $sample,
    'result' => $sampleResult ?: null,
]);