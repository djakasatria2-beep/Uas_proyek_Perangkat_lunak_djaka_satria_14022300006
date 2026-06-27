<?php
// ============================================================
//  ThreadB2B — assets/changePassword.php
//  Ganti password dari halaman settings (user sudah login).
//  Method : POST (JSON body)
//  Body   : { "old_password": "...", "new_password": "...", "confirm_password": "..." }
// ============================================================

session_start();
include __DIR__ . '/config.php';
include __DIR__ . '/noSessionRedirect.php';
header('Content-Type: application/json; charset=utf-8');

requireMethod('POST');
$data           = getJsonBody();
$oldPassword    = trim($data['old_password']    ?? '');
$newPassword    = trim($data['new_password']    ?? '');
$confirmPassword = trim($data['confirm_password'] ?? '');

// --- Validasi input ---
if ($oldPassword === '' || $newPassword === '' || $confirmPassword === '') {
    respond('error', 'Semua field wajib diisi.');
}
if (strlen($newPassword) < 8) {
    respond('error', 'Password baru minimal 8 karakter.');
}
if ($newPassword !== $confirmPassword) {
    respond('error', 'Konfirmasi password baru tidak cocok.');
}
if ($oldPassword === $newPassword) {
    respond('error', 'Password baru tidak boleh sama dengan password lama.');
}

// --- Ambil password hash saat ini ---
$userId = (int)$_SESSION['user_id'];
$sqlGet = "SELECT password FROM users WHERE id_user = ? LIMIT 1";
$stmtGet = mysqli_prepare($conn, $sqlGet);
mysqli_stmt_bind_param($stmtGet, 'i', $userId);
mysqli_stmt_execute($stmtGet);
$userRow = mysqli_fetch_assoc(mysqli_stmt_get_result($stmtGet));

if (!$userRow) {
    respond('error', 'User tidak ditemukan.');
}

// --- Verifikasi password lama ---
if (!password_verify($oldPassword, $userRow['password'])) {
    respond('error', 'Password lama salah.');
}

// --- Update password baru ---
$newHash   = password_hash($newPassword, PASSWORD_DEFAULT);
$sqlUpdate = "UPDATE users SET password = ? WHERE id_user = ?";
$stmtUpd   = mysqli_prepare($conn, $sqlUpdate);
mysqli_stmt_bind_param($stmtUpd, 'si', $newHash, $userId);
mysqli_stmt_execute($stmtUpd);

respond('success', 'Password berhasil diperbarui.');