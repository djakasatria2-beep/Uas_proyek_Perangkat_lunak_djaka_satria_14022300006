<?php
// ============================================================
//  login-backend.php — Proses autentikasi ThreadB2B
//  Hanya menerima POST. Redirect balik ke login.php jika gagal,
//  redirect ke panel sesuai role jika berhasil.
// ============================================================
require_once __DIR__ . '/assets/config.php';

// session_start() TIDAK dipanggil lagi di sini,
// karena sudah ditangani otomatis oleh config.php

// Ambil koneksi database lewat helper getDB() di config.php
$conn = getDB();

// Hanya terima POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . SITE_URL . '/login.php');
    exit;
}

// ── Fungsi helper redirect dengan flash message ──────────────
function redirectLogin(string $error, string $email = ''): void
{
    $_SESSION['login_error']      = $error;
    $_SESSION['login_last_email'] = $email;
    header('Location: ' . SITE_URL . '/login.php');
    exit;
}

// ── 1. Validasi CSRF token ───────────────────────────────────
$csrfToken = $_POST['csrf_token'] ?? '';
if (empty($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $csrfToken)) {
    redirectLogin('Sesi tidak valid, silakan muat ulang halaman.');
}
// Regenerasi token setelah validasi
$_SESSION['csrf_token'] = bin2hex(random_bytes(32));

// ── 2. Ambil & bersihkan input ───────────────────────────────
$email    = trim($_POST['email']    ?? '');
$password =       $_POST['password'] ?? '';
$role     = trim($_POST['role']     ?? '');

// Validasi tidak kosong
if ($email === '' || $password === '' || $role === '') {
    redirectLogin('Email, password, dan role wajib diisi.', $email);
}

// Validasi format email
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    redirectLogin('Format email tidak valid.', $email);
}

// Validasi role
$allowedRoles = ['buyer', 'marketing', 'admin'];
if (!in_array($role, $allowedRoles, true)) {
    redirectLogin('Role tidak dikenali.', $email);
}

// ── 3. Rate limiting sederhana (max 5 percobaan per 10 menit) ─
$attemptKey = 'login_attempts_' . md5($email);
$attempts   = $_SESSION[$attemptKey] ?? ['count' => 0, 'first_at' => time()];

// Reset window jika sudah lebih dari 10 menit
if ((time() - $attempts['first_at']) > 600) {
    $attempts = ['count' => 0, 'first_at' => time()];
}

if ($attempts['count'] >= 5) {
    $wait = ceil((600 - (time() - $attempts['first_at'])) / 60);
    redirectLogin("Terlalu banyak percobaan login. Coba lagi dalam {$wait} menit.", $email);
}

// ── 4. Cari user di database ─────────────────────────────────
$stmt = $conn->prepare("
    SELECT id_user, email, password, role
    FROM users
    WHERE email = ?
    LIMIT 1
");
$stmt->bind_param('s', $email);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

// ── 5. Verifikasi password & role ────────────────────────────
$loginFailed = !$user
    || !password_verify($password, $user['password'])
    || $user['role'] !== $role;

if ($loginFailed) {
    // Catat percobaan gagal
    $attempts['count']++;
    $_SESSION[$attemptKey] = $attempts;

    // Pesan generik (tidak bocorkan apakah email ada atau tidak)
    redirectLogin('Email, password, atau role tidak cocok.', $email);
}

// ── 6. Cek status buyer (hanya untuk role buyer) ─────────────
if ($user['role'] === 'buyer') {
    $stmt2 = $conn->prepare("
        SELECT status_verifikasi
        FROM buyer_profile
        WHERE id_user = ?
        LIMIT 1
    ");
    $stmt2->bind_param('i', $user['id_user']);
    $stmt2->execute();
    $buyerProfile = $stmt2->get_result()->fetch_assoc();
    $stmt2->close();

    if (!$buyerProfile) {
        redirectLogin('Profil buyer tidak ditemukan. Hubungi admin.', $email);
    }

    $status = $buyerProfile['status_verifikasi'];

    if ($status === 'pending') {
        redirectLogin('Akun Anda sedang menunggu verifikasi oleh Admin.', $email);
    }

    if ($status === 'rejected') {
        redirectLogin('Pendaftaran Anda ditolak. Hubungi admin untuk informasi lebih lanjut.', $email);
    }

    if ($status === 'blocked') {
        redirectLogin('Akun Anda diblokir karena terdapat invoice yang melewati jatuh tempo. Hubungi admin.', $email);
    }
}

// ── 7. Login berhasil — buat session ─────────────────────────
// Reset percobaan gagal
unset($_SESSION[$attemptKey]);

// Regenerasi session ID untuk mencegah session fixation
session_regenerate_id(true);

$_SESSION['user_id'] = $user['id_user'];
$_SESSION['role']    = $user['role'];
$_SESSION['email']   = $user['email'];

// ── 8. Redirect ke panel sesuai role ─────────────────────────
$destinations = [
    'admin'     => SITE_URL . '/admin_panel/dashboard.php',
    'marketing' => SITE_URL . '/marketing_panel/dashboard.php',
    'buyer'     => SITE_URL . '/buyer_panel/dashboard.php',
];

header('Location: ' . $destinations[$user['role']]);
exit;