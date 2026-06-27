<?php
// ============================================================
//  ThreadB2B — assets/setLanguage.php
//  Simpan preferensi bahasa (id/en) ke session.
//  Body (JSON):
//    lang = 'id' | 'en'
// ============================================================

session_start();
include __DIR__ . '/config.php';
include __DIR__ . '/noSessionRedirect.php';
header('Content-Type: application/json; charset=utf-8');

requireMethod('POST');

$body = getJsonBody();
$lang = trim($body['lang'] ?? '');

if (!in_array($lang, ['id', 'en'])) {
    respond('error', 'Nilai lang tidak valid. Gunakan id atau en.');
}

$_SESSION['lang'] = $lang;

respond('success', 'Preferensi bahasa berhasil disimpan.', ['lang' => $lang]);
