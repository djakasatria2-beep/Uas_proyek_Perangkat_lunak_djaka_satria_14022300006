<?php
// ============================================================
//  admin_panel/partials/config.php
//  Konfigurasi lokal panel Admin.
//  Di-include di baris pertama setiap halaman Admin (setelah verifyRoleRedirect.php).
// ============================================================

// Load koneksi DB & helper global
require_once dirname(__DIR__, 2) . '/assets/config.php';

// session_start() TIDAK dipanggil lagi di sini,
// karena sudah ditangani otomatis oleh assets/config.php

// Pastikan hanya Admin yang bisa akses
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ' . SITE_URL . '/login.php');
    exit;
}

// Konstanta path panel Admin
define('ADMIN_URL',  SITE_URL . '/admin_panel');     // URL publik (untuk href, src, dll)
define('ADMIN_ROOT', dirname(__DIR__));               // Path filesystem ke folder admin_panel (untuk require/include)

// Ambil koneksi database lewat helper getDB() di config.php
$conn = getDB();

// Ambil data user Admin yang sedang login (untuk navbar/profil)
$stmt = $conn->prepare("
    SELECT u.id_user, u.email, u.role
    FROM users u
    WHERE u.id_user = ?
    LIMIT 1
");
$stmt->bind_param('i', $_SESSION['user_id']);
$stmt->execute();
$currentAdmin = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$currentAdmin) {
    session_destroy();
    header('Location: ' . SITE_URL . '/login.php');
    exit;
}