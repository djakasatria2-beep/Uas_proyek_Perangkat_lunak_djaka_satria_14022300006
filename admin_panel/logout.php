<?php
// ============================================================
//  admin_panel/logout.php
// ============================================================
session_start();
session_unset();
session_destroy();

// Hapus cookie remember-me jika ada
if (isset($_COOKIE['remember_token'])) {
    setcookie('remember_token', '', time() - 3600, '/', '', true, true);
}

header('Location: ' . (defined('BASE_URL') ? APP_URL : '') . 'threadb2b/login.php');
exit;