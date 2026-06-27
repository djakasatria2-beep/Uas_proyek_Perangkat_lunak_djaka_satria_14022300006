<?php
// ============================================================
//  ThreadB2B — assets/themeSet.php
//  Simpan preferensi tema (dark/light) ke session.
//  Body (JSON):
//    theme = 'dark' | 'light'
// ============================================================

session_start();
include __DIR__ . '/config.php';
include __DIR__ . '/noSessionRedirect.php';
header('Content-Type: application/json; charset=utf-8');

requireMethod('POST');

$body  = getJsonBody();
$theme = trim($body['theme'] ?? '');

if (!in_array($theme, ['dark', 'light'])) {
    respond('error', 'Nilai theme tidak valid. Gunakan dark atau light.');
}

$_SESSION['theme'] = $theme;

respond('success', 'Preferensi tema berhasil disimpan.', ['theme' => $theme]);
