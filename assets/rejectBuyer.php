<?php
// ============================================================
//  ThreadB2B — assets/rejectBuyer.php
//  Admin: tolak registrasi buyer → status = rejected.
//  Body (JSON):
//    id_buyer INT    (wajib)
//    alasan   STRING (wajib) — dikirim via email ke buyer
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
$alasan  = trim($body['alasan'] ?? '');

if ($idBuyer === 0)  respond('error', 'id_buyer wajib diisi.');
if ($alasan === '')  respond('error', 'alasan penolakan wajib diisi.');

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
if (!in_array($buyer['status_verifikasi'], ['pending', 'approved'])) {
    respond('error', 'Buyer sudah berstatus ' . $buyer['status_verifikasi'] . '.');
}

$upd = mysqli_prepare($conn,
    "UPDATE buyer_profile
     SET status_verifikasi = 'rejected', diverifikasi_oleh = ?
     WHERE id_buyer = ?");
mysqli_stmt_bind_param($upd, 'ii', $idAdmin, $idBuyer);
if (!mysqli_stmt_execute($upd)) {
    respond('error', 'Gagal memperbarui status: ' . mysqli_error($conn));
}

// Kirim email alasan penolakan — uncomment jika PHPMailer siap
/*
require_once __DIR__ . '/../phpmailer/src/PHPMailer.php';
require_once __DIR__ . '/../phpmailer/src/SMTP.php';
require_once __DIR__ . '/../phpmailer/src/Exception.php';
$mail = new PHPMailer\PHPMailer\PHPMailer();
$mail->isSMTP();
$mail->Host       = $_ENV['SMTP_HOST'] ?? 'smtp.gmail.com';
$mail->SMTPAuth   = true;
$mail->Username   = $_ENV['SMTP_USER'] ?? '';
$mail->Password   = $_ENV['SMTP_PASS'] ?? '';
$mail->SMTPSecure = 'tls';
$mail->Port       = 587;
$mail->setFrom($_ENV['SMTP_USER'] ?? '', 'ThreadB2B');
$mail->addAddress($buyer['email'], $buyer['nama_pic']);
$mail->Subject    = 'Pendaftaran Buyer Tidak Disetujui — ThreadB2B';
$mail->isHTML(true);
$mail->Body = "<p>Yth. {$buyer['nama_pic']},</p>
               <p>Mohon maaf, pendaftaran akun buyer atas nama
               <strong>{$buyer['nama_perusahaan']}</strong> tidak dapat kami setujui
               karena alasan berikut:</p>
               <blockquote>$alasan</blockquote>
               <p>Silakan hubungi kami jika ada pertanyaan.</p>";
@$mail->send();
*/

respond('success', 'Buyer berhasil ditolak.', [
    'id_buyer' => $idBuyer,
    'status'   => 'rejected',
]);
