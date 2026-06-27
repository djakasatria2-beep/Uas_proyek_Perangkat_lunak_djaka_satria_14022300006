<?php
// ============================================================
//  ThreadB2B — assets/createOrder.php
//  Buat pesanan baru. Hanya Buyer yang sudah approved.
//  Cek status blokir sebelum menyimpan.
//  Method : POST (JSON body)
//  Body   : { "kode_warna", "nama_warna", "jenis_benang",
//             "ukuran_benang", "qty", "harga_benang",
//             "tanggal", "catatan" }
// ============================================================

session_start();
include __DIR__ . '/config.php';
include __DIR__ . '/noSessionRedirect.php';
header('Content-Type: application/json; charset=utf-8');

requireMethod('POST');

// --- Hanya Buyer ---
if ($_SESSION['role'] !== 'buyer') {
    respond('error', 'Akses ditolak. Hanya Buyer yang dapat membuat pesanan.');
}

$idBuyer = (int)($_SESSION['id_buyer'] ?? 0);
if ($idBuyer === 0) {
    respond('error', 'Data buyer tidak ditemukan pada sesi.');
}

// --- Cek status verifikasi & blokir ---
$sqlStatus = "SELECT status_verifikasi FROM buyer_profile WHERE id_buyer = ? LIMIT 1";
$stmtStatus = mysqli_prepare($conn, $sqlStatus);
mysqli_stmt_bind_param($stmtStatus, 'i', $idBuyer);
mysqli_stmt_execute($stmtStatus);
$buyerRow = mysqli_fetch_assoc(mysqli_stmt_get_result($stmtStatus));

if (!$buyerRow) {
    respond('error', 'Profil buyer tidak ditemukan.');
}
if ($buyerRow['status_verifikasi'] === 'blocked') {
    respond('error', 'Akun Anda diblokir karena ada invoice overdue. Selesaikan pembayaran terlebih dahulu atau hubungi Admin.');
}
if ($buyerRow['status_verifikasi'] !== 'approved') {
    respond('error', 'Akun Anda belum diverifikasi oleh Admin.');
}

// --- Ambil & validasi body ---
$data          = getJsonBody();
$kodeWarna     = trim($data['kode_warna']     ?? '');
$namaWarna     = trim($data['nama_warna']     ?? '');
$jenisBenang   = trim($data['jenis_benang']   ?? '');
$ukuranBenang  = trim($data['ukuran_benang']  ?? '');
$qty           = (int)($data['qty']            ?? 0);
$hargaBenang   = (float)($data['harga_benang'] ?? 0);
$tanggal       = trim($data['tanggal']         ?? date('Y-m-d'));
$catatan       = trim($data['catatan']         ?? '');

if ($jenisBenang === '') {
    respond('error', 'Jenis benang wajib diisi.');
}
if ($qty <= 0) {
    respond('error', 'Quantity harus lebih dari 0.');
}
if ($hargaBenang <= 0) {
    respond('error', 'Harga benang harus lebih dari 0.');
}

// --- Generate nomor order: SO-YYYY-NNNNN ---
$noOrder = generateDocNumber($conn, 'SO', 'orders', 'no_order');

// --- Insert pesanan ---
$sqlInsert = "INSERT INTO orders
                (id_buyer, no_order, kode_warna, nama_warna, jenis_benang,
                 ukuran_benang, qty, harga_benang, tanggal, catatan, status)
              VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending')";
$stmtIns = mysqli_prepare($conn, $sqlInsert);
mysqli_stmt_bind_param(
    $stmtIns, 'isssssidss',
    $idBuyer, $noOrder, $kodeWarna, $namaWarna, $jenisBenang,
    $ukuranBenang, $qty, $hargaBenang, $tanggal, $catatan
);
mysqli_stmt_execute($stmtIns);
$newOrderId = mysqli_insert_id($conn);

respond('success', 'Pesanan berhasil dibuat.', [
    'id_order' => $newOrderId,
    'no_order' => $noOrder,
]);