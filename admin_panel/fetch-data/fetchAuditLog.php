<?php
// ============================================================
//  ThreadB2B — admin_panel/fetch-data/fetchAuditLog.php
//  Ambil log aktivitas sistem dari beberapa tabel:
//    - Perubahan status buyer  (buyer_profile)
//    - Perubahan status order  (orders + tracking)
//    - Perubahan status invoice (invoices)
//    - Perubahan status retur  (order_returns)
//  Query param opsional:
//    ?dari=YYYY-MM-DD   ?sampai=YYYY-MM-DD
//    ?tipe=buyer|order|invoice|return|all  (default: all)
//    ?page=<int>  ?limit=<int>
//  Catatan: Karena belum ada tabel audit_log tersendiri di schema,
//  log direkonstruksi dari tabel-tabel yang ada.
//  Jika tabel audit_log ditambahkan, ganti implementasi ini.
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

$dari   = $_GET['dari']   ?? date('Y-m-01');
$sampai = $_GET['sampai'] ?? date('Y-m-d');
$tipe   = $_GET['tipe']   ?? 'all';
$page   = max(1, (int)($_GET['page']  ?? 1));
$limit  = min(100, max(1, (int)($_GET['limit'] ?? 30)));
$offset = ($page - 1) * $limit;

$allowedTipe = ['buyer', 'order', 'invoice', 'return', 'all'];
if (!in_array($tipe, $allowedTipe)) {
    respond('error', 'Parameter tipe tidak valid.');
}

foreach (['dari' => $dari, 'sampai' => $sampai] as $key => $val) {
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $val)) {
        respond('error', "Format tanggal '$key' tidak valid (YYYY-MM-DD).");
    }
}

$logs = [];

// --- Log buyer: perubahan status verifikasi ---
if (in_array($tipe, ['buyer', 'all'])) {
    $sqlBuyer = "SELECT
                     bp.id_buyer              AS ref_id,
                     CONCAT('BYR-', LPAD(bp.id_buyer,4,'0')) AS ref_no,
                     bp.nama_perusahaan       AS subjek,
                     bp.status_verifikasi     AS status_baru,
                     u.email                  AS aktor,
                     COALESCE(bp.tanggal_diblokir, u.created_at) AS waktu,
                     'buyer'                  AS tipe
                 FROM buyer_profile bp
                 JOIN users u ON u.id_user = bp.id_user
                 WHERE DATE(COALESCE(bp.tanggal_diblokir, u.created_at)) BETWEEN ? AND ?";
    $stmtB = mysqli_prepare($conn, $sqlBuyer);
    mysqli_stmt_bind_param($stmtB, 'ss', $dari, $sampai);
    mysqli_stmt_execute($stmtB);
    $resB = mysqli_stmt_get_result($stmtB);
    while ($r = mysqli_fetch_assoc($resB)) {
        $logs[] = $r;
    }
}

// --- Log order: dari tabel tracking ---
if (in_array($tipe, ['order', 'all'])) {
    $sqlOrder = "SELECT
                     t.id_tracking            AS ref_id,
                     o.no_order               AS ref_no,
                     CONCAT('Order #', o.no_order) AS subjek,
                     t.status                 AS status_baru,
                     u.email                  AS aktor,
                     t.tanggal                AS waktu,
                     'order'                  AS tipe
                 FROM tracking t
                 JOIN orders o ON o.id_order = t.id_order
                 JOIN users  u ON u.id_user  = t.updated_by
                 WHERE DATE(t.tanggal) BETWEEN ? AND ?";
    $stmtO = mysqli_prepare($conn, $sqlOrder);
    mysqli_stmt_bind_param($stmtO, 'ss', $dari, $sampai);
    mysqli_stmt_execute($stmtO);
    $resO = mysqli_stmt_get_result($stmtO);
    while ($r = mysqli_fetch_assoc($resO)) {
        $logs[] = $r;
    }
}

// --- Log invoice: invoice yang dibuat/diubah dalam rentang ---
if (in_array($tipe, ['invoice', 'all'])) {
    $sqlInv = "SELECT
                   inv.invoice_id             AS ref_id,
                   inv.invoice_id             AS ref_no,
                   CONCAT('Invoice ', inv.invoice_id) AS subjek,
                   inv.status                 AS status_baru,
                   COALESCE(inv.created_by, 'system') AS aktor,
                   inv.created_at             AS waktu,
                   'invoice'                  AS tipe
               FROM invoices inv
               WHERE DATE(inv.created_at) BETWEEN ? AND ?";
    $stmtI = mysqli_prepare($conn, $sqlInv);
    mysqli_stmt_bind_param($stmtI, 'ss', $dari, $sampai);
    mysqli_stmt_execute($stmtI);
    $resI = mysqli_stmt_get_result($stmtI);
    while ($r = mysqli_fetch_assoc($resI)) {
        $logs[] = $r;
    }
}

// --- Log return: status retur ---
if (in_array($tipe, ['return', 'all'])) {
    $sqlRet = "SELECT
                   ret.id_return              AS ref_id,
                   ret.no_return              AS ref_no,
                   CONCAT('Retur ', ret.no_return) AS subjek,
                   ret.status                 AS status_baru,
                   'admin'                    AS aktor,
                   o.tanggal                  AS waktu,
                   'return'                   AS tipe
               FROM order_returns ret
               JOIN orders o ON o.id_order = ret.id_order
               WHERE DATE(o.tanggal) BETWEEN ? AND ?";
    $stmtR = mysqli_prepare($conn, $sqlRet);
    mysqli_stmt_bind_param($stmtR, 'ss', $dari, $sampai);
    mysqli_stmt_execute($stmtR);
    $resR = mysqli_stmt_get_result($stmtR);
    while ($r = mysqli_fetch_assoc($resR)) {
        $logs[] = $r;
    }
}

// --- Urutkan semua log berdasarkan waktu DESC ---
usort($logs, fn($a, $b) => strcmp($b['waktu'], $a['waktu']));

$total      = count($logs);
$paginasi   = array_slice($logs, $offset, $limit);

respond('success', 'Log aktivitas berhasil diambil.', [
    'filter'     => compact('dari', 'sampai', 'tipe'),
    'logs'       => $paginasi,
    'pagination' => [
        'total'       => $total,
        'page'        => $page,
        'limit'       => $limit,
        'total_pages' => (int) ceil($total / $limit),
    ],
]);