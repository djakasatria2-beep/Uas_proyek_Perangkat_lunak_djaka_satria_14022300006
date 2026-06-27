<?php
// ============================================================
//  ThreadB2B — assets/verifyBuyer.php
//  Admin: setujui registrasi buyer → status = approved.
//  Body (JSON):
//    id_buyer  INT    (wajib)
//    catatan   STRING (opsional) — dikirim via email notifikasi
// ============================================================

session_start();
include __DIR__ . '/config.php';
include __DIR__ . '/noSessionRedirect.php';
header('Content-Type: application/json; charset=utf-8');

requireMethod('POST');

if ($_SESSION['role'] !== 'admin') {
    respond('error', 'Akses ditolak. Hanya Admin.');
}

$idAdmin = (int)$_SESSION['user_id'];
$body    = getJsonBody();
$idBuyer = (int)($body['id_buyer'] ?? 0);
$catatan = trim($body['catatan'] ?? '');

if ($idBuyer === 0) respond('error', 'id_buyer wajib diisi.');

// Ambil buyer + email
$stmt = mysqli_prepare($conn,
    "SELECT bp.id_buyer, bp.status_verifikasi, bp.nama_perusahaan, bp.nama_pic,
            u.email
     FROM buyer_profile bp
     JOIN users u ON u.id_user = bp.id_user
     WHERE bp.id_buyer = ?");
mysqli_stmt_bind_param($stmt, 'i', $idBuyer);
mysqli_stmt_execute($stmt);
$buyer = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

if (!$buyer) respond('error', 'Buyer tidak ditemukan.');
if ($buyer['status_verifikasi'] === 'approved') {
    respond('error', 'Buyer sudah berstatus approved.');
}

// Update status
$upd = mysqli_prepare($conn,
    "UPDATE buyer_profile
     SET status_verifikasi = 'approved', diverifikasi_oleh = ?
     WHERE id_buyer = ?");
mysqli_stmt_bind_param($upd, 'ii', $idAdmin, $idBuyer);
if (!mysqli_stmt_execute($upd)) {
    respond('error', 'Gagal memperbarui status: ' . mysqli_error($conn));
}

// Kirim email notifikasi (opsional — butuh PHPMailer di-include)
// Uncomment blok di bawah jika PHPMailer sudah dikonfigurasi
/*
require_once __DIR__ . '/../phpmailer/src/PHPMailer.php';
require_once __DIR__ . '/../phpmailer/src/SMTP.php';
require_once __DIR__ . '/../phpmailer/src/Exception.php';
$mail = new PHPMailer\PHPMailer\PHPMailer();
$mail->isSMTP();
$mail->Host       = $_ENV['SMTP_HOST']     ?? 'smtp.gmail.com';
$mail->SMTPAuth   = true;
$mail->Username   = $_ENV['SMTP_USER']     ?? '';
$mail->Password   = $_ENV['SMTP_PASS']     ?? '';
$mail->SMTPSecure = 'tls';
$mail->Port       = 587;
$mail->setFrom($_ENV['SMTP_USER'] ?? '', 'ThreadB2B');
$mail->addAddress($buyer['email'], $buyer['nama_pic']);
$mail->Subject    = 'Akun Buyer Anda Telah Diverifikasi — ThreadB2B';
$mail->isHTML(true);
$mail->Body = "<p>Yth. {$buyer['nama_pic']},</p>
               <p>Akun perusahaan <strong>{$buyer['nama_perusahaan']}</strong> telah disetujui.
               Anda sekarang dapat login dan mulai membuat pesanan.</p>"
              . ($catatan ? "<p>Catatan dari Admin: $catatan</p>" : '');
@$mail->send();
*/

respond('success', 'Buyer berhasil diverifikasi.', [
    'id_buyer' => $idBuyer,
    'status'   => 'approved',
]);
