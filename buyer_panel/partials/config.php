<?php
// ============================================================
//  buyer_panel/partials/config.php
//  Konfigurasi lokal panel Buyer.
//  Di-include di baris pertama setiap halaman Buyer
//  (setelah verifyRoleRedirect.php).
// ============================================================

// Load koneksi DB & helper global
require_once dirname(__DIR__, 2) . '/assets/config.php';

// session_start() TIDAK dipanggil lagi di sini,
// karena sudah ditangani otomatis oleh assets/config.php

// Pastikan hanya Buyer yang bisa akses
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'buyer') {
    header('Location: ' . SITE_URL . '/login.php');
    exit;
}

// Konstanta path panel Buyer
define('BUYER_URL',  SITE_URL . '/buyer_panel');      // URL publik (untuk href, src, dll)
define('BUYER_ROOT', dirname(__DIR__));                // Path filesystem ke folder buyer_panel (untuk require/include)

// Ambil koneksi database lewat helper getDB() di config.php
$conn = getDB();

// Ambil data user Buyer + profil perusahaan yang sedang login
$stmt = $conn->prepare("
    SELECT
        u.id_user,
        u.email,
        u.role,
        bp.id_buyer,
        bp.kode_pelanggan,
        bp.nama_perusahaan,
        bp.nama_pic,
        bp.no_whatsapp,
        bp.status_verifikasi,
        bp.tenor_hari,
        bp.upload_dokumen
    FROM users u
    INNER JOIN buyer_profile bp ON bp.id_user = u.id_user
    WHERE u.id_user = ?
    LIMIT 1
");
$stmt->bind_param('i', $_SESSION['user_id']);
$stmt->execute();
$currentBuyer = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Jika data tidak ditemukan, paksa logout
if (!$currentBuyer) {
    session_destroy();
    header('Location: ' . SITE_URL . '/login.php');
    exit;
}

// ------------------------------------------------------------
// Cek overdue — dipakai oleh overdue-banner.php
// Aktif jika buyer_profile.kode_pelanggan sudah terisi dan
// cocok dengan invoices.customer_id.
// ------------------------------------------------------------
$hasOverdue = false;

if (!empty($currentBuyer['kode_pelanggan'])) {
    $stmtOverdue = $conn->prepare("
        SELECT COUNT(*) AS jumlah_overdue
        FROM invoices
        WHERE customer_id = ? AND status = 'OVERDUE'
    ");
    $stmtOverdue->bind_param('s', $currentBuyer['kode_pelanggan']);
    $stmtOverdue->execute();
    $overdueRow = $stmtOverdue->get_result()->fetch_assoc();
    $stmtOverdue->close();
    $hasOverdue = ($overdueRow['jumlah_overdue'] ?? 0) > 0;
}