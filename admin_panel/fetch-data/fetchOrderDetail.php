<?php
// ============================================================
//  ThreadB2B — admin_panel/fetch-data/fetchOrderDetail.php
//  Ambil detail lengkap satu pesanan:
//    - Data order + profil buyer
//    - Riwayat tracking lengkap
//    - Retur terkait order ini (jika ada)
//  Query param wajib:
//    ?id_order=<int>  ATAU  ?no_order=<string>
//  Dipanggil via AJAX GET dari modal/halaman detail order Admin.
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

$idOrder  = (int)trim($_GET['id_order']  ?? 0);
$noOrder  = trim($_GET['no_order'] ?? '');

if ($idOrder <= 0 && $noOrder === '') {
    respond('error', 'Wajib menyertakan id_order atau no_order.');
}

// --- Ambil order ---
if ($idOrder > 0) {
    $sqlOrder = "SELECT o.*, bp.nama_perusahaan, bp.nama_pic, bp.no_whatsapp,
                        bp.negara, bp.tenor_hari, bp.status_verifikasi,
                        CONCAT('BYR-', LPAD(bp.id_buyer,4,'0')) AS customer_id
                 FROM orders o
                 JOIN buyer_profile bp ON bp.id_buyer = o.id_buyer
                 WHERE o.id_order = ? LIMIT 1";
    $stmt = mysqli_prepare($conn, $sqlOrder);
    mysqli_stmt_bind_param($stmt, 'i', $idOrder);
} else {
    $sqlOrder = "SELECT o.*, bp.nama_perusahaan, bp.nama_pic, bp.no_whatsapp,
                        bp.negara, bp.tenor_hari, bp.status_verifikasi,
                        CONCAT('BYR-', LPAD(bp.id_buyer,4,'0')) AS customer_id
                 FROM orders o
                 JOIN buyer_profile bp ON bp.id_buyer = o.id_buyer
                 WHERE o.no_order = ? LIMIT 1";
    $stmt = mysqli_prepare($conn, $sqlOrder);
    mysqli_stmt_bind_param($stmt, 's', $noOrder);
}
mysqli_stmt_execute($stmt);
$order = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

if (!$order) {
    respond('error', 'Pesanan tidak ditemukan.');
}

$orderId = (int)$order['id_order'];

// --- Riwayat tracking ---
$sqlTracking = "SELECT t.id_tracking, t.status, t.keterangan, t.tanggal,
                       u.email AS diupdate_oleh
                FROM tracking t
                JOIN users u ON u.id_user = t.updated_by
                WHERE t.id_order = ?
                ORDER BY t.tanggal ASC";
$stmtT = mysqli_prepare($conn, $sqlTracking);
mysqli_stmt_bind_param($stmtT, 'i', $orderId);
mysqli_stmt_execute($stmtT);
$resT = mysqli_stmt_get_result($stmtT);
$tracking = [];
while ($r = mysqli_fetch_assoc($resT)) {
    $tracking[] = $r;
}

// --- Retur terkait order ini ---
$sqlRetur = "SELECT id_return, no_return, alasan_kategori, alasan, foto, status, respons_admin
             FROM order_returns
             WHERE id_order = ?
             ORDER BY id_return DESC";
$stmtR = mysqli_prepare($conn, $sqlRetur);
mysqli_stmt_bind_param($stmtR, 'i', $orderId);
mysqli_stmt_execute($stmtR);
$resR = mysqli_stmt_get_result($stmtR);
$returns = [];
while ($r = mysqli_fetch_assoc($resR)) {
    // foto disimpan sebagai JSON array path
    $r['foto'] = $r['foto'] ? json_decode($r['foto'], true) : [];
    $returns[] = $r;
}

// --- Resi pengiriman terkait (via delivery_notes) ---
$sqlResi = "SELECT rp.resi_no, rp.resi_date, rp.shipper_name, rp.consignee,
                   rp.koli, rp.berat_kg, rp.service_type, rp.kurir, rp.charge_idr,
                   dn.sj_no, dn.sj_date
            FROM delivery_notes dn
            JOIN resi_pengiriman rp ON rp.sj_no = dn.sj_no
            WHERE dn.invoice_id IN (
                SELECT invoice_id FROM invoices
                WHERE customer_id = ?
            )
            ORDER BY rp.resi_date DESC
            LIMIT 5";
$stmtResi = mysqli_prepare($conn, $sqlResi);
mysqli_stmt_bind_param($stmtResi, 's', $order['customer_id']);
mysqli_stmt_execute($stmtResi);
$resResi = mysqli_stmt_get_result($stmtResi);
$resiList = [];
while ($r = mysqli_fetch_assoc($resResi)) {
    $resiList[] = $r;
}

respond('success', 'Detail pesanan berhasil diambil.', [
    'order'    => $order,
    'tracking' => $tracking,
    'returns'  => $returns,
    'resi'     => $resiList,
]);