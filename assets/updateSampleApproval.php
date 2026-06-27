<?php
// ============================================================
//  ThreadB2B — assets/updateSampleApproval.php
//  Buyer menyimpan keputusan atas hasil sampel:
//    Opsi A (pilihan='A') → approved  → buat draft order
//    Opsi B (pilihan='B') → revision  → status kembali ke revision
//  Method : POST (JSON body)
//  Body   : { "id_request": INT, "pilihan": "A"|"B", "catatan": "..." }
// ============================================================

session_start();
include __DIR__ . '/config.php';
include __DIR__ . '/noSessionRedirect.php';
header('Content-Type: application/json; charset=utf-8');

requireMethod('POST');

if ($_SESSION['role'] !== 'buyer') {
    respond('error', 'Akses ditolak. Hanya Buyer yang dapat memberikan keputusan sampel.');
}

$idBuyer   = (int)($_SESSION['id_buyer'] ?? 0);
$data      = getJsonBody();
$idRequest = (int)($data['id_request'] ?? 0);
$pilihan   = strtoupper(trim($data['pilihan'] ?? ''));
$catatan   = trim($data['catatan'] ?? '');

if ($idRequest === 0) {
    respond('error', 'id_request wajib diisi.');
}
if (!in_array($pilihan, ['A', 'B'])) {
    respond('error', 'Pilihan harus A (Approved) atau B (Revision).');
}

// --- Ambil permintaan sampel + hasil ---
$sql = "SELECT sr.id_request, sr.id_buyer, sr.status, sr.jenis_benang,
               sr.ukuran_benang, sr.kode_warna_target,
               res.id_result, res.kode_warna_hasil
        FROM sample_requests sr
        JOIN sample_results res ON res.id_request = sr.id_request
        WHERE sr.id_request = ?
        LIMIT 1";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, 'i', $idRequest);
mysqli_stmt_execute($stmt);
$row = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

if (!$row) {
    respond('error', 'Permintaan sampel atau hasil sampel tidak ditemukan.');
}
if ((int)$row['id_buyer'] !== $idBuyer) {
    respond('error', 'Akses ditolak. Sampel bukan milik Anda.');
}
if ($row['status'] !== 'result_ready') {
    respond('error', "Keputusan hanya dapat diberikan saat status 'result_ready'. Status saat ini: {$row['status']}.");
}

mysqli_begin_transaction($conn);

try {
    if ($pilihan === 'A') {
        // Opsi A → Approved
        $sqlUpdResult = "UPDATE sample_results
                         SET pilihan = 'A', status_approval = 'approved'
                         WHERE id_result = ?";
        $stmtRes = mysqli_prepare($conn, $sqlUpdResult);
        mysqli_stmt_bind_param($stmtRes, 'i', $row['id_result']);
        mysqli_stmt_execute($stmtRes);

        $sqlUpdReq = "UPDATE sample_requests SET status = 'approved' WHERE id_request = ?";
        $stmtReq = mysqli_prepare($conn, $sqlUpdReq);
        mysqli_stmt_bind_param($stmtReq, 'i', $idRequest);
        mysqli_stmt_execute($stmtReq);

        // Buat draft order dari sampel yang disetujui
        $noOrder = generateDocNumber($conn, 'SO', 'orders', 'no_order');
        $today   = date('Y-m-d');
        $sqlOrder = "INSERT INTO orders
                       (id_buyer, no_order, kode_warna, nama_warna, jenis_benang,
                        ukuran_benang, qty, harga_benang, tanggal, catatan, status)
                     VALUES (?, ?, ?, ?, ?, ?, 0, 0, ?, ?, 'pending')";
        $stmtOrd = mysqli_prepare($conn, $sqlOrder);
        $catatanOrder = 'Draft dari sampel yang disetujui. ' . ($catatan ?: '');
        mysqli_stmt_bind_param(
            $stmtOrd, 'isssssss',
            $idBuyer, $noOrder, $row['kode_warna_hasil'], $row['kode_warna_target'],
            $row['jenis_benang'], $row['ukuran_benang'], $today, $catatanOrder
        );
        mysqli_stmt_execute($stmtOrd);
        $newOrderId = mysqli_insert_id($conn);

        mysqli_commit($conn);
        respond('success', 'Sampel disetujui (Opsi A). Draft pesanan telah dibuat.', [
            'pilihan'   => 'A',
            'id_order'  => $newOrderId,
            'no_order'  => $noOrder,
        ]);

    } else {
        // Opsi B → Revision
        $sqlUpdResult = "UPDATE sample_results
                         SET pilihan = 'B', status_approval = 'revision_requested'
                         WHERE id_result = ?";
        $stmtRes = mysqli_prepare($conn, $sqlUpdResult);
        mysqli_stmt_bind_param($stmtRes, 'i', $row['id_result']);
        mysqli_stmt_execute($stmtRes);

        $sqlUpdReq = "UPDATE sample_requests SET status = 'revision' WHERE id_request = ?";
        $stmtReq = mysqli_prepare($conn, $sqlUpdReq);
        mysqli_stmt_bind_param($stmtReq, 'i', $idRequest);
        mysqli_stmt_execute($stmtReq);

        mysqli_commit($conn);
        respond('success', 'Permintaan revisi sampel (Opsi B) berhasil disimpan.', [
            'pilihan' => 'B',
        ]);
    }

} catch (Exception $e) {
    mysqli_rollback($conn);
    respond('error', 'Terjadi kesalahan. Silakan coba lagi.');
}