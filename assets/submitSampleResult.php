<?php
// ============================================================
//  ThreadB2B — assets/submitSampleResult.php
//  Marketing input atau update hasil sampel.
//  Kode warna hasil: BN-YYYY-XXX-NNNNN
//  Method : POST (multipart/form-data — ada file upload foto)
//  Fields : id_request, kode_warna_hasil, nilai_delta_e,
//           catatan, gambar (file: JPG/PNG, max 10MB)
// ============================================================

session_start();
include __DIR__ . '/config.php';
include __DIR__ . '/noSessionRedirect.php';
header('Content-Type: application/json; charset=utf-8');

requireMethod('POST');

if (!in_array($_SESSION['role'], ['marketing', 'admin'])) {
    respond('error', 'Akses ditolak. Hanya Marketing dan Admin yang dapat menginput hasil sampel.');
}

$idRequest      = (int)($_POST['id_request']       ?? 0);
$kodeWarnaHasil = trim($_POST['kode_warna_hasil']  ?? '');
$nilaiDeltaE    = trim($_POST['nilai_delta_e']      ?? '');
$catatan        = trim($_POST['catatan']             ?? '');

if ($idRequest === 0) {
    respond('error', 'id_request wajib diisi.');
}
if ($kodeWarnaHasil === '') {
    respond('error', 'Kode warna hasil wajib diisi (format: BN-YYYY-XXX-NNNNN).');
}

// Validasi format kode warna BN-YYYY-XXX-NNNNN
if (!preg_match('/^BN-\d{4}-[A-Z0-9]{1,5}-\d{5}$/', $kodeWarnaHasil)) {
    respond('error', 'Format kode warna hasil tidak valid. Contoh: BN-2026-NSR-00001');
}

$deltaEVal = ($nilaiDeltaE !== '') ? (float)$nilaiDeltaE : null;

// --- Cek permintaan sampel ada & statusnya ---
$sqlGet = "SELECT id_request, status FROM sample_requests WHERE id_request = ? LIMIT 1";
$stmtGet = mysqli_prepare($conn, $sqlGet);
mysqli_stmt_bind_param($stmtGet, 'i', $idRequest);
mysqli_stmt_execute($stmtGet);
$requestRow = mysqli_fetch_assoc(mysqli_stmt_get_result($stmtGet));

if (!$requestRow) {
    respond('error', 'Permintaan sampel tidak ditemukan.');
}

// --- Handle upload foto hasil ---
$gambarPath = null;
if (!empty($_FILES['gambar']['tmp_name'])) {
    $file        = $_FILES['gambar'];
    $maxSize     = 10 * 1024 * 1024; // 10 MB
    $allowedMime = ['image/jpeg', 'image/png'];

    if ($file['size'] > $maxSize) {
        respond('error', 'Ukuran foto hasil maksimal 10 MB.');
    }
    $finfo    = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);

    if (!in_array($mimeType, $allowedMime)) {
        respond('error', 'Format foto harus JPG atau PNG.');
    }

    $ext     = pathinfo($file['name'], PATHINFO_EXTENSION);
    $newName = 'result_' . $idRequest . '_' . time() . '.' . $ext;
    $destDir = __DIR__ . '/../uploads/sample-results/';

    if (!is_dir($destDir)) mkdir($destDir, 0755, true);

    if (!move_uploaded_file($file['tmp_name'], $destDir . $newName)) {
        respond('error', 'Gagal menyimpan foto hasil.');
    }
    $gambarPath = 'uploads/sample-results/' . $newName;
}

// --- Cek apakah hasil sudah ada (INSERT or UPDATE) ---
$sqlCheck = "SELECT id_result, gambar FROM sample_results WHERE id_request = ? LIMIT 1";
$stmtCheck = mysqli_prepare($conn, $sqlCheck);
mysqli_stmt_bind_param($stmtCheck, 'i', $idRequest);
mysqli_stmt_execute($stmtCheck);
$existingResult = mysqli_fetch_assoc(mysqli_stmt_get_result($stmtCheck));

if ($existingResult) {
    // UPDATE
    // Pakai gambar lama jika tidak ada upload baru
    $finalGambar = $gambarPath ?? $existingResult['gambar'];

    $sqlUpd = "UPDATE sample_results
               SET kode_warna_hasil = ?, nilai_delta_e = ?,
                   catatan = ?, gambar = ?, status_approval = 'pending'
               WHERE id_request = ?";
    $stmtUpd = mysqli_prepare($conn, $sqlUpd);
    mysqli_stmt_bind_param($stmtUpd, 'sdssi',
        $kodeWarnaHasil, $deltaEVal, $catatan, $finalGambar, $idRequest);
    mysqli_stmt_execute($stmtUpd);
    $resultId = $existingResult['id_result'];

} else {
    // INSERT
    $sqlIns = "INSERT INTO sample_results
                 (id_request, kode_warna_hasil, nilai_delta_e, catatan, gambar, status_approval, pilihan)
               VALUES (?, ?, ?, ?, ?, 'pending', 'pending')";
    $stmtIns = mysqli_prepare($conn, $sqlIns);
    mysqli_stmt_bind_param($stmtIns, 'idsss',
        $idRequest, $kodeWarnaHasil, $deltaEVal, $catatan, $gambarPath);
    mysqli_stmt_execute($stmtIns);
    $resultId = mysqli_insert_id($conn);
}

// --- Update status sample_requests → result_ready ---
$sqlUpdReq = "UPDATE sample_requests SET status = 'result_ready' WHERE id_request = ?";
$stmtUpdReq = mysqli_prepare($conn, $sqlUpdReq);
mysqli_stmt_bind_param($stmtUpdReq, 'i', $idRequest);
mysqli_stmt_execute($stmtUpdReq);

respond('success', 'Hasil sampel berhasil disimpan.', [
    'id_result'  => $resultId,
    'id_request' => $idRequest,
]);