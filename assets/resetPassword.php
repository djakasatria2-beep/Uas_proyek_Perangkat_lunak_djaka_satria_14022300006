<?php
// ============================================================
//  ThreadB2B — assets/resetPassword.php
//  Validasi token reset password, update password baru,
//  invalidasi token setelah dipakai.
//  Method : POST (JSON body)
//  Body   : { "token": "...", "password": "...", "confirm_password": "..." }
// ============================================================

session_start();
include __DIR__ . '/config.php';
header('Content-Type: application/json; charset=utf-8');

requireMethod('POST');
$data            = getJsonBody();
$token           = trim($data['token']            ?? '');
$password        = trim($data['password']         ?? '');
$confirmPassword = trim($data['confirm_password'] ?? '');

// --- Validasi input ---
if ($token === '') {
    respond('error', 'Token tidak valid.');
}
if (strlen($password) < 8) {
    respond('error', 'Password baru minimal 8 karakter.');
}
if ($password !== $confirmPassword) {
    respond('error', 'Konfirmasi password tidak cocok.');
}

// --- Cari token yang valid (belum dipakai, belum expired) ---
$sqlFind = "SELECT id, email FROM password_resets
            WHERE token = ?
              AND used = 0
              AND expires_at > NOW()
            LIMIT 1";
$stmtFind = mysqli_prepare($conn, $sqlFind);
mysqli_stmt_bind_param($stmtFind, 's', $token);
mysqli_stmt_execute($stmtFind);
$row = mysqli_fetch_assoc(mysqli_stmt_get_result($stmtFind));

if (!$row) {
    respond('error', 'Token tidak valid atau sudah kedaluwarsa. Minta link reset baru.');
}

$email    = $row['email'];
$resetId  = $row['id'];

// --- Update password di tabel users ---
$passwordHash = password_hash($password, PASSWORD_DEFAULT);
$sqlUpdate = "UPDATE users SET password = ? WHERE email = ?";
$stmtUpd   = mysqli_prepare($conn, $sqlUpdate);
mysqli_stmt_bind_param($stmtUpd, 'ss', $passwordHash, $email);
mysqli_stmt_execute($stmtUpd);

// --- Invalidasi token (tandai sudah dipakai) ---
$sqlUsed = "UPDATE password_resets SET used = 1 WHERE id = ?";
$stmtUsed = mysqli_prepare($conn, $sqlUsed);
mysqli_stmt_bind_param($stmtUsed, 'i', $resetId);
mysqli_stmt_execute($stmtUsed);

respond('success', 'Password berhasil diperbarui. Silakan login dengan password baru Anda.', [
    'redirect_url' => APP_URL . '/login.php'
]);