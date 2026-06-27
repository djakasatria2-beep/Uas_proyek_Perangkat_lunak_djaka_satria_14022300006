<?php
// ============================================================
//  ThreadB2B — assets/forgotPassword.php
//  Generate token reset password, simpan ke DB (tabel
//  password_resets), kirim link via PHPMailer.
//  Method : POST (JSON body)
//  Body   : { "email": "..." }
// ============================================================

session_start();
include __DIR__ . '/config.php';
header('Content-Type: application/json; charset=utf-8');

requireMethod('POST');
$data  = getJsonBody();
$email = trim($data['email'] ?? '');

if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    respond('error', 'Masukkan email yang valid.');
}

// --- Cek apakah email terdaftar ---
$sqlCheck = "SELECT id_user FROM users WHERE email = ? LIMIT 1";
$stmtCheck = mysqli_prepare($conn, $sqlCheck);
mysqli_stmt_bind_param($stmtCheck, 's', $email);
mysqli_stmt_execute($stmtCheck);
mysqli_stmt_store_result($stmtCheck);

// Selalu kirim pesan sama (jangan bocorkan info email terdaftar/tidak)
if (mysqli_stmt_num_rows($stmtCheck) === 0) {
    respond('success', 'Jika email terdaftar, link reset telah dikirim.');
}

// --- Generate token aman ---
$token     = bin2hex(random_bytes(32)); // 64 char hex
$expiresAt = date('Y-m-d H:i:s', strtotime('+1 hour'));

// --- Pastikan tabel password_resets ada ---
mysqli_query($conn, "CREATE TABLE IF NOT EXISTS `password_resets` (
    `id`         INT          NOT NULL AUTO_INCREMENT,
    `email`      VARCHAR(100) NOT NULL,
    `token`      VARCHAR(64)  NOT NULL,
    `expires_at` DATETIME     NOT NULL,
    `used`       TINYINT(1)   NOT NULL DEFAULT 0,
    `created_at` DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_token` (`token`),
    KEY `idx_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

// --- Hapus token lama milik email ini ---
$sqlDelete = "DELETE FROM password_resets WHERE email = ?";
$stmtDel   = mysqli_prepare($conn, $sqlDelete);
mysqli_stmt_bind_param($stmtDel, 's', $email);
mysqli_stmt_execute($stmtDel);

// --- Simpan token baru ---
$sqlInsert = "INSERT INTO password_resets (email, token, expires_at) VALUES (?, ?, ?)";
$stmtIns   = mysqli_prepare($conn, $sqlInsert);
mysqli_stmt_bind_param($stmtIns, 'sss', $email, $token, $expiresAt);
mysqli_stmt_execute($stmtIns);

// --- Kirim email via PHPMailer ---
$resetUrl = APP_URL . '/reset-password.php?token=' . $token;

try {
    require_once __DIR__ . '/../phpmailer/src/PHPMailer.php';
    require_once __DIR__ . '/../phpmailer/src/SMTP.php';
    require_once __DIR__ . '/../phpmailer/src/Exception.php';

    $mail = new PHPMailer\PHPMailer\PHPMailer(true);
    $mail->isSMTP();
    $mail->Host       = getenv('SMTP_HOST') ?: 'smtp.gmail.com';
    $mail->SMTPAuth   = true;
    $mail->Username   = getenv('SMTP_USER') ?: '';
    $mail->Password   = getenv('SMTP_PASS') ?: '';
    $mail->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port       = 587;
    $mail->CharSet    = 'UTF-8';

    $mail->setFrom(getenv('SMTP_USER') ?: 'no-reply@threadb2b.id', 'ThreadB2B');
    $mail->addAddress($email);
    $mail->isHTML(true);
    $mail->Subject = 'Reset Password ThreadB2B';
    $mail->Body    = "
        <p>Kami menerima permintaan reset password untuk akun Anda.</p>
        <p>Klik link berikut untuk membuat password baru (berlaku 1 jam):</p>
        <p><a href='{$resetUrl}'>{$resetUrl}</a></p>
        <p>Abaikan email ini jika Anda tidak meminta reset password.</p>
        <br><p>— Tim ThreadB2B</p>
    ";
    $mail->send();

} catch (Exception $e) {
    // Jangan gagalkan proses, token tetap tersimpan
    // Log error jika perlu: error_log($e->getMessage());
}

respond('success', 'Jika email terdaftar, link reset telah dikirim.');