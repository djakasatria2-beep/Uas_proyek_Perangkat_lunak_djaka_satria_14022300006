<?php
// ============================================================
//  ThreadB2B — assets/fetchBuyerDetail.php
//  Ambil detail lengkap satu buyer (Admin / Marketing).
//  Query params (GET):
//    id_buyer = INT
// ============================================================

session_start();
include __DIR__ . '/config.php';
include __DIR__ . '/noSessionRedirect.php';
header('Content-Type: application/json; charset=utf-8');

requireMethod('GET');

$role = $_SESSION['role'];
if (!in_array($role, ['marketing', 'admin'])) {
    respond('error', 'Akses ditolak.');
}

$idBuyer = (int)($_GET['id_buyer'] ?? 0);
if ($idBuyer === 0) respond('error', 'id_buyer wajib diisi.');

$stmt = mysqli_prepare($conn,
    "SELECT bp.id_buyer, bp.nama_perusahaan, bp.nama_pic, bp.no_whatsapp,
            bp.alamat, bp.negara, bp.contact_person, bp.no_telp,
            bp.npwp, bp.nib, bp.upload_dokumen,
            bp.status_verifikasi, bp.tanggal_diblokir, bp.tenor_hari,
            u.email, u.created_at,
            adm.email AS diverifikasi_oleh_email
     FROM buyer_profile bp
     JOIN users u ON u.id_user = bp.id_user
     LEFT JOIN users adm ON adm.id_user = bp.diverifikasi_oleh
     WHERE bp.id_buyer = ?");
mysqli_stmt_bind_param($stmt, 'i', $idBuyer);
mysqli_stmt_execute($stmt);
$buyer = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

if (!$buyer) respond('error', 'Buyer tidak ditemukan.');

// Ringkasan order (jumlah & nilai)
$stmtOrd = mysqli_prepare($conn,
    "SELECT COUNT(*) AS total_order,
            SUM(CASE WHEN status NOT IN ('cancelled') THEN qty * harga_benang ELSE 0 END) AS total_nilai
     FROM orders WHERE id_buyer = ?");
mysqli_stmt_bind_param($stmtOrd, 'i', $idBuyer);
mysqli_stmt_execute($stmtOrd);
$orderSummary = mysqli_fetch_assoc(mysqli_stmt_get_result($stmtOrd));

respond('success', 'Data berhasil diambil.', [
    'buyer'         => $buyer,
    'order_summary' => $orderSummary,
]);
