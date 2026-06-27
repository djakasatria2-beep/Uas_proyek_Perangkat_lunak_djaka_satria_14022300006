<?php
// ============================================================
//  ThreadB2B — assets/login.php
//  Proses login: validasi email + password, set session,
//  redirect ke panel sesuai role.
//  Method : POST (JSON body)
//  Body   : { "email": "...", "password": "..." }
//  Returns: { status, message } — redirect dilakukan di sisi
//           klien setelah menerima status 'success' + role.
// ============================================================

session_start();
include __DIR__ . '/config.php';
header('Content-Type: application/json; charset=utf-8');

requireMethod('POST');
$data = getJsonBody();

$email    = trim($data['email']    ?? '');
$password = trim($data['password'] ?? '');

// --- Validasi input dasar ---
if ($email === '' || $password === '') {
    respond('error', 'Email dan password wajib diisi.');
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    respond('error', 'Format email tidak valid.');
}

// --- Ambil user berdasarkan email ---
$sql  = "SELECT u.id_user, u.email, u.password, u.role,
                bp.status_verifikasi, bp.id_buyer
         FROM users u
         LEFT JOIN buyer_profile bp ON bp.id_user = u.id_user
         WHERE u.email = ?
         LIMIT 1";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, 's', $email);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$user   = mysqli_fetch_assoc($result);

if (!$user) {
    respond('error', 'Email atau password salah.');
}

// --- Verifikasi password ---
if (!password_verify($password, $user['password'])) {
    respond('error', 'Email atau password salah.');
}

// --- Cek status buyer ---
if ($user['role'] === 'buyer') {
    if ($user['status_verifikasi'] === 'pending') {
        respond('error', 'Akun Anda belum diverifikasi. Silakan tunggu konfirmasi Admin.');
    }
    if ($user['status_verifikasi'] === 'rejected') {
        respond('error', 'Pendaftaran Anda ditolak. Hubungi Admin untuk informasi lebih lanjut.');
    }
    if ($user['status_verifikasi'] === 'blocked') {
        respond('error', 'Akun Anda diblokir karena ada invoice overdue. Silakan hubungi Admin.');
    }
}

// --- Set session ---
$_SESSION['user_id'] = $user['id_user'];
$_SESSION['role']    = $user['role'];
$_SESSION['email']   = $user['email'];

if ($user['role'] === 'buyer' && $user['id_buyer']) {
    $_SESSION['id_buyer'] = $user['id_buyer'];
}

// --- Tentukan URL redirect berdasarkan role ---
$redirectMap = [
    'buyer'     => APP_URL . '/buyer_panel/dashboard.php',
    'marketing' => APP_URL . '/marketing_panel/dashboard.php',
    'admin'     => APP_URL . '/admin_panel/dashboard.php',
];
$redirectUrl = $redirectMap[$user['role']] ?? APP_URL . '/login.php';

respond('success', 'Login berhasil.', [
    'role'        => $user['role'],
    'redirect_url' => $redirectUrl,
]);