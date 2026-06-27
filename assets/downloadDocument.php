<?php
// ============================================================
//  ThreadB2B — assets/downloadDocument.php
//  Validasi signed token dari getSignedUrl.php, lalu stream
//  file dokumen ke browser buyer dengan header yang benar.
//  Method : GET
//  Params : ?token=BASE64_SIGNED_TOKEN
// ============================================================

// Tidak perlu session di sini — token sudah membawa identitas
include __DIR__ . '/config.php';

$tokenRaw = trim($_GET['token'] ?? '');

if ($tokenRaw === '') {
    http_response_code(400);
    die('Token tidak valid.');
}

// --- Decode & validasi token ---
$decoded = base64_decode($tokenRaw, true);
if ($decoded === false) {
    http_response_code(403);
    die('Token tidak valid.');
}

$parts = explode('|', $decoded);
if (count($parts) !== 4) {
    http_response_code(403);
    die('Token tidak valid.');
}

[$idDoc, $userId, $expiresAt, $receivedSig] = $parts;
$idDoc     = (int)$idDoc;
$expiresAt = (int)$expiresAt;

// Cek expiry
if (time() > $expiresAt) {
    http_response_code(403);
    die('Link telah kedaluwarsa. Minta link baru.');
}

// Verifikasi signature
$secret   = getenv('APP_SECRET') ?: 'threadb2b_secret_change_me';
$payload  = $idDoc . '|' . $userId . '|' . $expiresAt;
$expected = hash_hmac('sha256', $payload, $secret);
if (!hash_equals($expected, $receivedSig)) {
    http_response_code(403);
    die('Signature tidak valid.');
}

// --- Ambil path file dari DB ---
$sqlDoc = "SELECT path_file, nama_file, is_aktif
           FROM payment_documents WHERE id_doc = ? LIMIT 1";
$stmtDoc = mysqli_prepare($conn, $sqlDoc);
mysqli_stmt_bind_param($stmtDoc, 'i', $idDoc);
mysqli_stmt_execute($stmtDoc);
$doc = mysqli_fetch_assoc(mysqli_stmt_get_result($stmtDoc));

if (!$doc || $doc['is_aktif'] == 0) {
    http_response_code(404);
    die('Dokumen tidak ditemukan.');
}

$filePath = __DIR__ . '/../' . $doc['path_file'];

if (!file_exists($filePath)) {
    http_response_code(404);
    die('File tidak ditemukan di server.');
}

// --- Stream file ---
$ext      = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
$mimeMap  = [
    'pdf'  => 'application/pdf',
    'jpg'  => 'image/jpeg',
    'jpeg' => 'image/jpeg',
    'png'  => 'image/png',
];
$mimeType = $mimeMap[$ext] ?? 'application/octet-stream';

header('Content-Type: '        . $mimeType);
header('Content-Disposition: inline; filename="' . $doc['nama_file'] . '"');
header('Content-Length: '      . filesize($filePath));
header('Cache-Control: private, no-store');
header('X-Content-Type-Options: nosniff');

readfile($filePath);
exit;