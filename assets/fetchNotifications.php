<?php
// ============================================================
//  ThreadB2B — assets/fetchNotifications.php
//  Ambil notifikasi real-time dari kondisi tabel yang ada.
//  Tidak pakai tabel notifikasi tersendiri — generate on-the-fly
//  berdasarkan role:
//
//  Buyer     : invoice overdue, status order berubah terbaru,
//              hasil sampel siap
//  Marketing : order pending baru, retur baru submitted
//  Admin     : buyer pending verifikasi, invoice jatuh tempo hari ini
// ============================================================

session_start();
include __DIR__ . '/config.php';
include __DIR__ . '/noSessionRedirect.php';
header('Content-Type: application/json; charset=utf-8');

requireMethod('GET');

$role    = $_SESSION['role'];
$idBuyer = (int)($_SESSION['id_buyer'] ?? 0);
$notifs  = [];

if ($role === 'buyer') {
    // Invoice overdue
    $stmt = mysqli_prepare($conn,
        "SELECT invoice_id, total_idr, due_date
         FROM invoices
         WHERE customer_id = (SELECT id_user FROM users WHERE id_user = ?)
           AND status = 'OVERDUE'
         ORDER BY due_date ASC LIMIT 5");
    mysqli_stmt_bind_param($stmt, 'i', $_SESSION['user_id']);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    while ($r = mysqli_fetch_assoc($res)) {
        $notifs[] = [
            'type'    => 'overdue',
            'icon'    => 'alert',
            'message' => "Invoice {$r['invoice_id']} telah jatuh tempo sejak {$r['due_date']}.",
            'data'    => $r,
        ];
    }

    // Hasil sampel siap (result_ready)
    $stmt2 = mysqli_prepare($conn,
        "SELECT id_request, jenis_benang, tanggal
         FROM sample_requests
         WHERE id_buyer = ? AND status = 'result_ready'
         ORDER BY id_request DESC LIMIT 5");
    mysqli_stmt_bind_param($stmt2, 'i', $idBuyer);
    mysqli_stmt_execute($stmt2);
    $res2 = mysqli_stmt_get_result($stmt2);
    while ($r = mysqli_fetch_assoc($res2)) {
        $notifs[] = [
            'type'    => 'sample_ready',
            'icon'    => 'info',
            'message' => "Hasil sampel {$r['jenis_benang']} sudah siap ditinjau.",
            'data'    => $r,
        ];
    }
}

if ($role === 'marketing') {
    // Order pending baru (7 hari terakhir)
    $stmt = mysqli_prepare($conn,
        "SELECT id_order, no_order, tanggal,
                bp.nama_perusahaan
         FROM orders o
         JOIN buyer_profile bp ON bp.id_buyer = o.id_buyer
         WHERE o.status = 'pending'
           AND o.tanggal >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
         ORDER BY o.id_order DESC LIMIT 10");
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    while ($r = mysqli_fetch_assoc($res)) {
        $notifs[] = [
            'type'    => 'new_order',
            'icon'    => 'package',
            'message' => "Order baru {$r['no_order']} dari {$r['nama_perusahaan']}.",
            'data'    => $r,
        ];
    }

    // Retur baru submitted
    $stmt2 = mysqli_prepare($conn,
        "SELECT r.id_return, r.no_return, bp.nama_perusahaan
         FROM order_returns r
         JOIN orders o ON o.id_order = r.id_order
         JOIN buyer_profile bp ON bp.id_buyer = o.id_buyer
         WHERE r.status = 'submitted'
         ORDER BY r.id_return DESC LIMIT 5");
    mysqli_stmt_execute($stmt2);
    $res2 = mysqli_stmt_get_result($stmt2);
    while ($r = mysqli_fetch_assoc($res2)) {
        $notifs[] = [
            'type'    => 'new_return',
            'icon'    => 'alert',
            'message' => "Retur baru {$r['no_return']} dari {$r['nama_perusahaan']}.",
            'data'    => $r,
        ];
    }
}

if ($role === 'admin') {
    // Buyer pending verifikasi
    $stmt = mysqli_prepare($conn,
        "SELECT id_buyer, nama_perusahaan, nama_pic
         FROM buyer_profile WHERE status_verifikasi = 'pending'
         ORDER BY id_buyer DESC LIMIT 10");
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    while ($r = mysqli_fetch_assoc($res)) {
        $notifs[] = [
            'type'    => 'buyer_pending',
            'icon'    => 'user',
            'message' => "{$r['nama_perusahaan']} menunggu verifikasi.",
            'data'    => $r,
        ];
    }

    // Invoice jatuh tempo hari ini atau sudah overdue
    $stmt2 = mysqli_prepare($conn,
        "SELECT invoice_id, customer_id, total_idr, due_date
         FROM invoices
         WHERE status IN ('ISSUED','OVERDUE') AND due_date <= CURDATE()
         ORDER BY due_date ASC LIMIT 10");
    mysqli_stmt_execute($stmt2);
    $res2 = mysqli_stmt_get_result($stmt2);
    while ($r = mysqli_fetch_assoc($res2)) {
        $notifs[] = [
            'type'    => 'invoice_due',
            'icon'    => 'receipt',
            'message' => "Invoice {$r['invoice_id']} jatuh tempo {$r['due_date']}.",
            'data'    => $r,
        ];
    }
}

respond('success', 'Notifikasi berhasil diambil.', [
    'notifications' => $notifs,
    'total'         => count($notifs),
]);
