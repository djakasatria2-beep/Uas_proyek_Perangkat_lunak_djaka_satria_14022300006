<?php
// ============================================================
//  ThreadB2B — assets/noSessionRedirect.php
//  Middleware session — wajib di-include di tiap halaman
//  yang butuh login. Redirect ke login jika session kosong.
//
//  File ini juga menyediakan 2 helper yang dipakai di semua
//  endpoint fetch-data/*.php:
//    - respond(status, message, data)  → kirim JSON & exit
//    - requireMethod(method)           → batasi HTTP method
// ============================================================

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (empty($_SESSION['user_id']) || empty($_SESSION['role'])) {
    // Jika request AJAX, kembalikan JSON; jika halaman biasa, redirect
    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
        strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['status' => 'error', 'message' => 'Sesi habis. Silakan login kembali.']);
        exit;
    }
    header('Location: ' . (defined('SITE_URL') ? SITE_URL : '') . '/login.php');
    exit;
}

// ── Helper: kirim response JSON lalu hentikan eksekusi ───────
//  Dipanggil di semua endpoint fetch-data/*.php, misalnya:
//    respond('error', 'Pesan error');
//    respond('success', 'Berhasil', ['id_buyer' => 5]);
if (!function_exists('respond')) {
    function respond(string $status, string $message, array $data = []): void
    {
        // Hindari "headers already sent" jika header Content-Type
        // sudah pernah dikirim sebelumnya di file pemanggil.
        if (!headers_sent()) {
            header('Content-Type: application/json; charset=utf-8');
        }

        echo json_encode(array_merge(
            [
                'status'  => $status,                  // 'success' | 'error'
                'success' => $status === 'success',    // alias boolean, untuk kompatibilitas
                'message' => $message,
            ],
            $data
        ));
        exit;
    }
}

// ── Helper: batasi endpoint hanya boleh diakses dengan method tertentu ─
//  Dipanggil di semua endpoint fetch-data/*.php, misalnya:
//    requireMethod('POST');
//    requireMethod('GET');
if (!function_exists('requireMethod')) {
    function requireMethod(string $method): void
    {
        $current = $_SERVER['REQUEST_METHOD'] ?? '';
        if (strtoupper($current) !== strtoupper($method)) {
            respond('error', "Method tidak diizinkan. Endpoint ini hanya menerima $method.");
        }
    }
}