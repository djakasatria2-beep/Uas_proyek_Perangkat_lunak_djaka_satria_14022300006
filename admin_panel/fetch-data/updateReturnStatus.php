<?php
// ============================================================
//  ThreadB2B — admin_panel/fetch-data/updateReturnStatus.php
//  Update status retur dan simpan respons Admin.
//  POST body (JSON):
//    {
//      "id_return":    <int>,
//      "status":       "under_review"|"approved"|"resolved"|"rejected",
//      "respons_admin": "<teks opsional>"
//    }
//  Dipanggil via AJAX POST dari halaman retur Admin.
// ============================================================

session_start();
include __DIR__ . '/../../assets/config.php';
include __DIR__ . '/../../assets/noSessionRedirect.php';
header('Content-Type: application/json; charset=utf-8');

if ($_SESSION['role'] !== 'admin') {
    respond('error', 'Akses ditolak.');
}
requireMethod('GET');

$conn = getDB(); // ← tambahkan baris ini

$body      = json_decode(file_get_contents('php://input'), true);
$idReturn  = (int)($body['id_return']    ?? 0);
$status    = trim($body['status']        ?? '');
$respons   = trim($body['respons_admin'] ?? '');

$allowedStatus = ['under_review', 'approved', 'resolved', 'rejected'];

if ($idReturn <= 0) {
    respond('error', 'Parameter id_return tidak valid.');
}
if (!in_array($status, $allowedStatus)) {
    respond('error', 'Status tidak valid. Pilihan: ' . implode(', ', $allowedStatus));
}

// --- Cek retur ada ---
$sqlCek = "SELECT id_return, status FROM order_returns WHERE id_return = ? LIMIT 1";
$stmtCek = mysqli_prepare($conn, $sqlCek);
mysqli_stmt_bind_param($stmtCek, 'i', $idReturn);
mysqli_stmt_execute($stmtCek);
$retur = mysqli_fetch_assoc(mysqli_stmt_get_result($stmtCek));

if (!$retur) {
    respond('error', 'Data retur tidak ditemukan.');
}

// --- Update ---
$sqlUpd = "UPDATE order_returns
             SET status       = ?,
                 respons_admin = ?
             WHERE id_return  = ?";
$stmtUpd = mysqli_prepare($conn, $sqlUpd);
mysqli_stmt_bind_param($stmtUpd, 'ssi', $status, $respons, $idReturn);
mysqli_stmt_execute($stmtUpd);

if (mysqli_stmt_affected_rows($stmtUpd) === 0) {
    respond('error', 'Tidak ada perubahan atau retur tidak ditemukan.');
}

respond('success', 'Status retur berhasil diperbarui.', [
    'id_return'    => $idReturn,
    'status_baru'  => $status,
    'respons_admin'=> $respons,
]);