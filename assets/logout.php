<?php
// ============================================================
//  ThreadB2B — assets/logout.php
//  Destroy session & cookie, redirect ke halaman login.
//  Dapat dipanggil langsung (redirect) atau via AJAX (JSON).
// ============================================================

session_start();
include __DIR__ . '/config.php';

session_unset();
session_destroy();

// Hapus cookie session jika ada
if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(), '', time() - 42000,
        $params['path'], $params['domain'],
        $params['secure'], $params['httponly']
    );
}

// AJAX request → JSON, halaman biasa → redirect
if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
    strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'status'       => 'success',
        'message'      => 'Logout berhasil.',
        'redirect_url' => APP_URL . '/login.php'
    ]);
    exit;
}

header('Location: ' . APP_URL . '/login.php');
exit;