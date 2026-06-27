<?php
// ============================================================
//  ThreadB2B — assets/createReturn.php
//  Buyer mengajukan retur/komplain baru.
//  Auto-generate nomor RET-YYYY-NNNNN.
//  Upload foto bukti max 5 file, path disimpan sbg JSON array.
//  Method : POST (multipart/form-data)
//  Fields : id_order, alasan_kategori, alasan, catatan,
//           foto_1..foto_5 (file: JPG/PNG, max 5MB each)
// ============================================================

session_start();
include __DIR__ . '/config.php';
include __DIR__ . '/noSessionRedirect.php';
header('Content-Type: application/json; charset=utf-8');

requireMethod('POST');

if ($_SESSION['role'] !== 'buyer') {
    respond('error', 'Akses ditolak. Hanya Buyer yang dapat mengajukan retur.');
}

$idBuyer = (int)($_SESSION['id_buyer'] ?? 0);

$idOrder        = (int)trim($_POST['id_order']         ?? 0);
$alasanKategori = trim($_POST['alasan_kategori']       ?? '');
$alasan         = trim($_POST['alasan']                 ?? '');
$catatan        = trim($_POST['catatan']                ?? '');

// --- Validasi input ---
if ($idOrder === 0) {
    respond('error', 'id_order wajib diisi.');
}

$validKategori = ['deviasi_warna','kualitas','barang_rusak','spesifikasi_salah','lainnya'];
if (!in_array($alasanKategori, $validKategori)) {
    respond('error', 'alasan_kategori tidak valid. Pilih: ' . implode(', ', $validKategori));
}
if ($alasan === '') {
    respond('error', 'Deskripsi alasan wajib diisi.');
}

// --- Verifikasi order milik buyer & statusnya done/shipped ---
$sqlOrder = "SELECT id_order, no_order, status, id_buyer
             FROM orders WHERE id_order = ? LIMIT 1";
$stmtOrd = mysqli_prepare($conn, $sqlOrder);
mysqli_stmt_bind_param($stmtOrd, 'i', $idOrder);
mysqli_stmt_execute($stmtOrd);
$orderRow = mysqli_fetch_assoc(mysqli_stmt_get_result($stmtOrd));

if (!$orderRow) {
    respond('error', 'Pesanan tidak ditemukan.');
}
if ((int)$orderRow['id_buyer'] !== $idBuyer) {
    respond('error', 'Akses ditolak. Pesanan bukan milik Anda.');
}
if (!in_array($orderRow['status'], ['shipped', 'done'])) {
    respond('error', "Retur hanya dapat diajukan untuk pesanan berstatus 'shipped' atau 'done'.");
}

// --- Cek apakah sudah ada retur aktif untuk order ini ---
$sqlExist = "SELECT id_return FROM order_returns
             WHERE id_order = ? AND status NOT IN ('rejected','resolved')
             LIMIT 1";
$stmtEx = mysqli_prepare($conn, $sqlExist);
mysqli_stmt_bind_param($stmtEx, 'i', $idOrder);
mysqli_stmt_execute($stmtEx);
mysqli_stmt_store_result($stmtEx);
if (mysqli_stmt_num_rows($stmtEx) > 0) {
    respond('error', 'Sudah ada pengajuan retur aktif untuk pesanan ini.');
}

// --- Upload foto (max 5) ---
$uploadedPaths = [];
$destDir       = __DIR__ . '/../uploads/return-photos/';
if (!is_dir($destDir)) mkdir($destDir, 0755, true);

$maxSize     = 5 * 1024 * 1024; // 5 MB
$allowedMime = ['image/jpeg', 'image/png'];

for ($i = 1; $i <= 5; $i++) {
    $key = 'foto_' . $i;
    if (empty($_FILES[$key]['tmp_name'])) continue;

    $file = $_FILES[$key];
    if ($file['error'] !== UPLOAD_ERR_OK) continue;

    if ($file['size'] > $maxSize) {
        respond('error', "foto_$i melebihi batas 5 MB.");
    }

    $finfo    = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);

    if (!in_array($mimeType, $allowedMime)) {
        respond('error', "foto_$i harus JPG atau PNG.");
    }

    $ext     = pathinfo($file['name'], PATHINFO_EXTENSION);
    $newName = 'ret_' . $idBuyer . '_' . $idOrder . '_' . $i . '_' . time() . '.' . $ext;

    if (!move_uploaded_file($file['tmp_name'], $destDir . $newName)) {
        respond('error', "Gagal menyimpan foto_$i.");
    }

    $uploadedPaths[] = 'uploads/return-photos/' . $newName;
}

$fotoJson = !empty($uploadedPaths) ? json_encode($uploadedPaths) : null;

// --- Generate nomor retur: RET-YYYY-NNNNN ---
$noReturn = generateDocNumber($conn, 'RET', 'order_returns', 'no_return');

// --- Insert retur ---
$sqlIns = "INSERT INTO order_returns
             (id_order, no_return, alasan_kategori, alasan, foto, status)
           VALUES (?, ?, ?, ?, ?, 'submitted')";
$stmtIns = mysqli_prepare($conn, $sqlIns);
mysqli_stmt_bind_param(
    $stmtIns, 'issss',
    $idOrder, $noReturn, $alasanKategori, $alasan, $fotoJson
);
mysqli_stmt_execute($stmtIns);
$newReturnId = mysqli_insert_id($conn);

respond('success', 'Pengajuan retur berhasil dikirim.', [
    'id_return' => $newReturnId,
    'no_return' => $noReturn,
    'foto_count' => count($uploadedPaths),
]);