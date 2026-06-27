<?php
// ============================================================
// config.php — Konfigurasi Database THREADB2B
// Disesuaikan dari: threadb2b.sql (MariaDB 10.4 / XAMPP lokal)
// ============================================================

define('DB_HOST',     '127.0.0.1');      // Sesuai SQL dump: Host 127.0.0.1
define('DB_USER',     'root');            // Default XAMPP
define('DB_PASS',     '');               // Default XAMPP (kosong)
define('DB_NAME',     'threadb2b');      // Nama database dari SQL dump
define('DB_PORT',     3306);             // Port MySQL default
define('DB_CHARSET',  'utf8mb4');        // Sesuai COLLATE utf8mb4_general_ci di SQL

// ── Site Config ─────────────────────────────────────────────
define('SITE_NAME',  'THREADB2B');
define('SITE_URL',   'http://localhost/threadb2b');  // Sesuaikan path folder XAMPP

// ── Daftar tabel yang ada di database (referensi) ───────────
// buyer_profile, certificates, company_profile
// delivery_items, delivery_notes, gallery
// invoices, invoice_items, orders, order_returns
// products, resi_pengiriman, sample_requests, sample_results
// tracking, users

// ── Akun default (password: "password") ─────────────────────
// admin@threadb2b.id      → role: admin
// marketing@threadb2b.id  → role: marketing
// buyer1@example.com s/d buyer5@example.com → role: buyer

// ── Error Reporting (set false di production) ───────────────
define('DEBUG_MODE', true);

if (DEBUG_MODE) {
    ini_set('display_errors', 1);
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', 0);
    error_reporting(0);
}

// ── Koneksi Database (MySQLi) ────────────────────────────────
function getDB(): mysqli {
    static $conn = null;

    if ($conn === null) {
        mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
        try {
            $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT);
            $conn->set_charset(DB_CHARSET);
        } catch (mysqli_sql_exception $e) {
            if (DEBUG_MODE) {
                die('<div style="font-family:monospace;padding:20px;background:#fee;border:1px solid #f00;margin:20px;">'
                    . '<strong>Database Error:</strong> ' . htmlspecialchars($e->getMessage())
                    . '<br><br><strong>Pastikan:</strong>'
                    . '<ol style="margin-top:8px;line-height:1.8">'
                    . '<li>XAMPP sudah dijalankan (Apache + MySQL aktif)</li>'
                    . '<li>Database <code>threadb2b</code> sudah diimport dari <code>threadb2b.sql</code></li>'
                    . '<li>Username & password di config.php sudah benar</li>'
                    . '</ol>'
                    . '</div>');
            } else {
                die('Koneksi database gagal. Silakan coba beberapa saat lagi.');
            }
        }
    }

    return $conn;
}

// ── Session ──────────────────────────────────────────────────
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ── Helper: sanitasi input ────────────────────────────────────
function clean(string $input): string {
    return htmlspecialchars(strip_tags(trim($input)), ENT_QUOTES, 'UTF-8');
}

// ── Helper: JSON response ─────────────────────────────────────
function jsonResponse(bool $success, string $message, array $data = []): void {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(array_merge(['success' => $success, 'message' => $message], $data));
    exit;
}

// ── Helper: cek role login (gunakan di halaman yang butuh auth) ─
function requireLogin(string ...$allowed_roles): void {
    if (empty($_SESSION['user'])) {
        header('Location: ' . rtrim(SITE_URL, '/') . '/login.php');
        exit;
    }
    if (!empty($allowed_roles) && !in_array($_SESSION['user']['role'], $allowed_roles, true)) {
        http_response_code(403);
        die('<div style="font-family:sans-serif;padding:40px;text-align:center">'
            . '<h2>403 — Akses Ditolak</h2>'
            . '<p>Anda tidak memiliki izin untuk mengakses halaman ini.</p>'
            . '<a href="javascript:history.back()">Kembali</a></div>');
    }
}