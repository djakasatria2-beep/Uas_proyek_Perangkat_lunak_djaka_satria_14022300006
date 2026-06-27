<?php
// ============================================================
//  ThreadB2B — assets/fetchInvoiceDetail.php
//  Ambil detail satu invoice + item-item + dokumen terlampir.
//  Method : GET
//  Params : ?invoice_id=INV-YYYY-NNNNN
// ============================================================

session_start();
include __DIR__ . '/config.php';
include __DIR__ . '/noSessionRedirect.php';
header('Content-Type: application/json; charset=utf-8');

requireMethod('GET');
$conn = getDB(); // ← tambahkan baris ini


$role      = $_SESSION['role'];
$idBuyer   = (int)($_SESSION['id_buyer'] ?? 0);
$invoiceId = trim($_GET['invoice_id'] ?? '');

if ($role === 'marketing') {
    respond('error', 'Akses ditolak.');
}
if ($invoiceId === '') {
    respond('error', 'Parameter invoice_id diperlukan.');
}

// --- Ambil invoice ---
$sql = "SELECT invoice_id, invoice_date, customer_id, credit_days,
               due_date, subtotal_idr, ppn_pct, ppn_idr, total_idr,
               created_by, status, created_at,
               DATEDIFF(due_date, CURDATE()) AS sisa_hari,
               CASE WHEN status NOT IN ('PAID') AND due_date < CURDATE()
                    THEN 1 ELSE 0 END AS is_overdue
        FROM invoices
        WHERE invoice_id = ?
        LIMIT 1";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, 's', $invoiceId);
mysqli_stmt_execute($stmt);
$invoice = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

if (!$invoice) {
    respond('error', 'Invoice tidak ditemukan.');
}

// --- Otorisasi buyer ---
if ($role === 'buyer') {
    $buyerCustomerId = 'BYR-' . str_pad($idBuyer, 4, '0', STR_PAD_LEFT);
    if ($invoice['customer_id'] !== $buyerCustomerId) {
        respond('error', 'Akses ditolak.');
    }
}

// --- Ambil invoice items ---
$sqlItems = "SELECT ii.id, ii.slip_date, ii.sj_no, ii.po_no,
                    ii.item_no, ii.colour_no, ii.qty, ii.unit,
                    ii.price_idr, ii.amount_idr,
                    p.item_name, p.colour_name, p.material_type
             FROM invoice_items ii
             LEFT JOIN products p ON p.item_no = ii.item_no
             WHERE ii.invoice_id = ?
             ORDER BY ii.id ASC";
$stmtItems = mysqli_prepare($conn, $sqlItems);
mysqli_stmt_bind_param($stmtItems, 's', $invoiceId);
mysqli_stmt_execute($stmtItems);
$resultItems = mysqli_stmt_get_result($stmtItems);
$items = [];
while ($row = mysqli_fetch_assoc($resultItems)) {
    $items[] = $row;
}

// --- Ambil dokumen terlampir (Surat Invoice, Surat Jalan, Nota) ---
// Tabel payment_documents (dibuat jika belum ada)
mysqli_query($conn, "CREATE TABLE IF NOT EXISTS `payment_documents` (
    `id_doc`      INT           NOT NULL AUTO_INCREMENT,
    `invoice_id`  VARCHAR(30)   NOT NULL,
    `jenis`       ENUM('surat_invoice','surat_jalan','nota') NOT NULL,
    `nama_file`   VARCHAR(255)  NOT NULL,
    `path_file`   VARCHAR(255)  NOT NULL,
    `ukuran_byte` INT           DEFAULT NULL,
    `uploaded_by` INT           NOT NULL,
    `uploaded_at` DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `is_aktif`    TINYINT(1)    NOT NULL DEFAULT 1,
    PRIMARY KEY (`id_doc`),
    KEY `fk_pd_invoice_id` (`invoice_id`),
    KEY `fk_pd_uploaded_by` (`uploaded_by`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

$sqlDocs = "SELECT id_doc, jenis, nama_file, path_file,
                   ukuran_byte, uploaded_at
            FROM payment_documents
            WHERE invoice_id = ? AND is_aktif = 1
            ORDER BY uploaded_at DESC";
$stmtDocs = mysqli_prepare($conn, $sqlDocs);
mysqli_stmt_bind_param($stmtDocs, 's', $invoiceId);
mysqli_stmt_execute($stmtDocs);
$resultDocs = mysqli_stmt_get_result($stmtDocs);
$documents  = [];
while ($row = mysqli_fetch_assoc($resultDocs)) {
    $documents[] = $row;
}

respond('success', 'Data berhasil diambil.', [
    'invoice'   => $invoice,
    'items'     => $items,
    'documents' => $documents,
]);