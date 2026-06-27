<?php
// ============================================================
//  assets/verifyRoleRedirect.php
//  Validasi awal: memastikan user sudah login & memiliki role
//  yang sesuai SEBELUM file partials/config.php tiap panel
//  (admin_panel / buyer_panel / marketing_panel) dijalankan.
//
//  Cara pakai (baris pertama setiap halaman panel):
//      define('REQUIRED_ROLE', 'buyer');   // atau 'admin' / 'marketing'
//      require_once __DIR__ . '/../assets/verifyRoleRedirect.php';
//      require_once __DIR__ . '/partials/config.php';
//
//  Jika REQUIRED_ROLE tidak didefinisikan, file ini hanya akan
//  memastikan user sudah login (role apa pun boleh lanjut).
// ============================================================

// Load koneksi DB & helper global (juga menangani session_start otomatis)
require_once dirname(__DIR__) . '/assets/config.php';

// Pastikan session sudah berjalan (fallback jika config.php belum start session)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Mapping role -> halaman dashboard masing-masing,
// dipakai untuk redirect otomatis jika role tidak sesuai.
const ROLE_DASHBOARD_MAP = [
    'admin'      => '/admin_panel/dashboard.php',
    'buyer'      => '/buyer_panel/dashboard.php',
    'marketing'  => '/marketing_panel/dashboard.php',
];

// ------------------------------------------------------------
// 1. Pastikan user sudah login
// ------------------------------------------------------------
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role'])) {
    header('Location: ' . SITE_URL . '/login.php');
    exit;
}

$userRole = $_SESSION['role'];

// ------------------------------------------------------------
// 2. Jika halaman mensyaratkan role tertentu, cek kecocokannya
// ------------------------------------------------------------
if (defined('REQUIRED_ROLE')) {
    if ($userRole !== REQUIRED_ROLE) {
        // Role tidak sesuai -> redirect ke dashboard sesuai role user saat ini,
        // atau ke login.php jika role tidak dikenali sama sekali.
        $redirectPath = ROLE_DASHBOARD_MAP[$userRole] ?? '/login.php';
        header('Location: ' . SITE_URL . $redirectPath);
        exit;
    }
}

// ------------------------------------------------------------
// 3. Validasi tambahan: pastikan role user masih dikenali sistem
// ------------------------------------------------------------
if (!array_key_exists($userRole, ROLE_DASHBOARD_MAP)) {
    session_destroy();
    header('Location: ' . SITE_URL . '/login.php');
    exit;
}