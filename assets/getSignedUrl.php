<?php
// ============================================================
//  ThreadB2B — assets/getSignedUrl.php
//  Generate URL sementara (signed) untuk akses file dokumen
//  sensitif (surat invoice, SJ, nota). Buyer hanya bisa
//  download setelah mendapat signed URL valid.
//  Method : GET
//  Params : ?id_doc=INT
//  Returns: { signed_url, expires_at }
// ============================================================

session_start();
include __DIR__ . '/config.php';
include __DIR__ . '/noSessionRedirect.php';
header('Content-Type: application/json; charset=utf-8');

requireMethod('GET');

$role    = $_SESSION['role'];
$userId  = (int)$_SESSION['user_id'];
$idBuyer = (int)($_SESSION['id_buyer'] ?? 0);
$idDoc   = (int)($_GET['id_doc'] ?? 0);

if ($idDoc === 0) {
    respond('error', 'Parameter id_doc diperlukan.');
}

// --- Ambil metadata dokumen ---
$sqlDoc = "SELECT pd.id_doc, pd.invoice_id, pd.jenis,
                  pd.nama_file, pd.path_file, pd.is_aktif,
                  inv.customer_id
           FROM payment_documents pd
           JOIN invoices inv ON inv.invoice_id = pd.invoice_id
           WHERE pd.id_doc = ? AND pd.is_aktif = 1
           LIMIT 1";
$stmtDoc = mysqli_prepare($conn, $sqlDoc);
mysqli_stmt_bind_param($stmtDoc, 'i', $idDoc);
mysqli_stmt_execute($stmtDoc);
$doc = mysqli_fetch_assoc(mysqli_stmt_get_result($stmtDoc));

if (!$doc) {
    respond('error', 'Dokumen tidak ditemukan atau sudah dihapus.');
}

// --- Otorisasi buyer ---
if ($role === 'buyer') {
    $buyerCustomerId = 'BYR-' . str_pad($idBuyer, 4, '0', STR_PAD_LEFT);
    if ($doc['customer_id'] !== $buyerCustomerId) {
        respond('error', 'Akses ditolak.');
    }
}

// --- Generate signed token ---
$secret    = getenv('APP_SECRET') ?: 'threadb2b_secret_change_me';
$expiresAt = time() + 300; // 5 menit
$payload   = $idDoc . '|' . $userId . '|' . $expiresAt;
$signature = hash_hmac('sha256', $payload, $secret);
$token     = base64_encode($payload . '|' . $signature);

// --- URL download ---
$signedUrl = APP_URL . '/assets/downloadDocument.php'
             . '?token=' . urlencode($token);

respond('success', 'Signed URL berhasil dibuat.', [
    'signed_url' => $signedUrl,
    'expires_at' => date('Y-m-d H:i:s', $expiresAt),
    'expires_in' => 300,
]);