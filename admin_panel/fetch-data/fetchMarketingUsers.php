<?php
// ============================================================
//  ThreadB2B — admin_panel/fetch-data/fetchMarketingUsers.php
//  Ambil daftar semua akun Marketing beserta status aktif/nonaktif.
//  Query param opsional:
//    ?status=active|inactive|all  (default: all)
//  Dipanggil via AJAX GET dari halaman manajemen akun Admin.
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

$allowedStatus = ['active', 'inactive', 'all'];
$status = in_array($_GET['status'] ?? 'all', $allowedStatus)
    ? ($_GET['status'] ?? 'all')
    : 'all';

// Catatan: tabel users tidak punya kolom is_active di schema saat ini.
// Gunakan kolom tambahan jika sudah ditambahkan, atau tampilkan semua marketing.
$conditions = ["u.role = 'marketing'"];
$params     = [];
$types      = '';

$where = 'WHERE ' . implode(' AND ', $conditions);

$sql = "SELECT
            u.id_user,
            u.email,
            u.created_at,
            COUNT(DISTINCT o.id_order) AS total_order_dikelola
        FROM users u
        LEFT JOIN tracking t  ON t.updated_by = u.id_user
        LEFT JOIN orders   o  ON o.id_order   = t.id_order
        $where
        GROUP BY u.id_user
        ORDER BY u.created_at DESC";

$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

$list = [];
while ($row = mysqli_fetch_assoc($result)) {
    $row['total_order_dikelola'] = (int) $row['total_order_dikelola'];
    $list[] = $row;
}

respond('success', 'Daftar akun marketing berhasil diambil.', [
    'marketing_users' => $list,
    'total'           => count($list),
]);