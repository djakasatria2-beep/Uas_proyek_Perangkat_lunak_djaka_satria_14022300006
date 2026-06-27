<?php
// ============================================================
//  buyer_panel/invoices-detail.php
//  Detail satu invoice: ringkasan, item baris, Surat Jalan,
//  resi pengiriman, dan tombol unduh dokumen
// ============================================================

require_once __DIR__ . '/partials/verifyRoleRedirect.php';
require_once __DIR__ . '/partials/config.php';

// Validasi parameter
$invoiceId = trim($_GET['id'] ?? '');
if (!$invoiceId || !preg_match('/^INV-\d{4}-\d{5}$/', $invoiceId)) {
    header('Location: ' . BUYER_URL . '/invoices.php');
    exit;
}

$pageTitle = 'Detail Invoice — ' . htmlspecialchars($invoiceId);
$extraJs   = ['invoices.js'];

require_once __DIR__ . '/partials/_header.php';
?>

<div id="appWrapper" class="d-flex">

    <?php require_once __DIR__ . '/partials/_sidebar.php'; ?>

    <div class="main-wrapper flex-grow-1 d-flex flex-column">

        <?php require_once __DIR__ . '/partials/_navbar.php'; ?>

        <?php if ($hasOverdue): ?>
            <?php require_once __DIR__ . '/partials/overdue-banner.php'; ?>
        <?php endif; ?>

        <div id="mainContent" class="p-4 flex-grow-1">

            <!-- Back -->
            <a href="<?= BUYER_URL ?>/invoices.php" class="btn btn-outline-primary btn-sm mb-4">
                <i class="bi bi-arrow-left"></i> Kembali ke Daftar Invoice
            </a>

            <!-- Skeleton / loading -->
            <div id="invoiceDetailWrap">
                <div class="text-center py-5">
                    <span class="spinner-border spinner-border-sm me-2"></span>
                    Memuat detail invoice…
                </div>
            </div>

        </div><!-- /#mainContent -->

    </div><!-- /.main-wrapper -->
</div><!-- /#appWrapper -->

<!-- Template: Detail Invoice (diisi oleh JS) -->
<template id="tplInvoiceDetail">

    <!-- Header Invoice -->
    <div class="d-flex flex-wrap align-items-start justify-content-between gap-3 mb-4">
        <div>
            <h1 class="page-header-title mb-1" data-field="invoice_id"></h1>
            <div class="d-flex align-items-center gap-2 flex-wrap">
                <span data-field="status_badge"></span>
                <span class="text-muted small">Diterbitkan <span data-field="invoice_date"></span></span>
                <span class="text-muted small">·</span>
                <span class="small" data-field="due_info"></span>
            </div>
        </div>
        <!-- Tombol unduh dokumen -->
        <div class="d-flex gap-2 flex-wrap" id="docButtons"></div>
    </div>

    <div class="row g-4">

        <!-- Kiri: Item Lines + Subtotal -->
        <div class="col-lg-8">

            <!-- Tabel Item -->
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="mb-0 fw-semibold">Rincian Item</h6>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table mb-0" id="tblItems">
                            <thead>
                                <tr>
                                    <th>SJ No.</th>
                                    <th>P.O. No.</th>
                                    <th>Kode Item</th>
                                    <th>Warna</th>
                                    <th class="text-end">Qty</th>
                                    <th class="text-end">Harga / kg</th>
                                    <th class="text-end">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody id="tbodyItems">
                                <tr>
                                    <td colspan="7" class="text-center py-4 text-muted">
                                        <span class="spinner-border spinner-border-sm me-2"></span>
                                        Memuat item…
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Surat Jalan terkait -->
            <div class="card">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <h6 class="mb-0 fw-semibold">Surat Jalan Terkait</h6>
                    <span class="badge badge-secondary" id="sjCount">—</span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table mb-0">
                            <thead>
                                <tr>
                                    <th>No. SJ</th>
                                    <th>Tanggal</th>
                                    <th>P.O. No.</th>
                                    <th>Gudang</th>
                                    <th class="text-end">Total Qty</th>
                                    <th class="text-end">Total Cones</th>
                                </tr>
                            </thead>
                            <tbody id="tbodySJ">
                                <tr>
                                    <td colspan="6" class="text-center py-4 text-muted small">Memuat…</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div><!-- /col-lg-8 -->

        <!-- Kanan: Ringkasan + Info Pengiriman -->
        <div class="col-lg-4">

            <!-- Ringkasan Tagihan -->
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="mb-0 fw-semibold">Ringkasan Tagihan</h6>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-2 small">
                        <span class="text-muted">Subtotal</span>
                        <span data-field="subtotal_idr" class="fw-semibold">—</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2 small">
                        <span class="text-muted">PPN (<span data-field="ppn_pct"></span>%)</span>
                        <span data-field="ppn_idr">—</span>
                    </div>
                    <div class="border-top pt-2 mt-2 d-flex justify-content-between">
                        <span class="fw-semibold" style="font-family:var(--font-display)">Total</span>
                        <span class="fw-semibold fs-6" data-field="total_idr" style="font-family:var(--font-display);color:var(--color-navy);">—</span>
                    </div>

                    <div class="mt-3 pt-3 border-top">
                        <div class="d-flex justify-content-between small mb-1">
                            <span class="text-muted">Dibuat oleh</span>
                            <span data-field="created_by">—</span>
                        </div>
                        <div class="d-flex justify-content-between small mb-1">
                            <span class="text-muted">Tenor</span>
                            <span data-field="credit_days">—</span>
                        </div>
                        <div class="d-flex justify-content-between small">
                            <span class="text-muted">Jatuh Tempo</span>
                            <span data-field="due_date_full" class="fw-semibold">—</span>
                        </div>
                    </div>

                    <!-- Overdue alert di ringkasan -->
                    <div id="overdueDetailAlert" class="alert alert-danger mt-3 py-2 px-3 d-none" style="font-size:12.5px;">
                        <i class="bi bi-exclamation-triangle-fill"></i>
                        <span>Tagihan ini melewati jatuh tempo. Segera lakukan pembayaran atau hubungi tim Admin.</span>
                    </div>
                </div>
            </div>

            <!-- Info Resi Pengiriman -->
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0 fw-semibold">Info Pengiriman</h6>
                </div>
                <div class="card-body p-0">
                    <div id="resiWrap">
                        <div class="text-center text-muted py-4 small">
                            <i class="bi bi-truck d-block fs-3 mb-1" style="opacity:.3;"></i>
                            Memuat info pengiriman…
                        </div>
                    </div>
                </div>
            </div>

        </div><!-- /col-lg-4 -->

    </div><!-- /row -->

</template>

<!-- Bootstrap JS sudah diload di _footer -->
<script>
    // Kirim invoice_id ke invoices.js lewat dataset
    document.getElementById('invoiceDetailWrap').dataset.invoiceId = <?= json_encode($invoiceId) ?>;
</script>

<?php
// Footer tidak pakai require_once di sini supaya template tidak terbungkus body
// _footer.php hanya menutup #mainContent dan </body></html>
?>
<script src="<?= BUYER_URL ?>/js/bootstrap.bundle.min.js"></script>
<script src="<?= BUYER_URL ?>/script.js"></script>
<script src="<?= BUYER_URL ?>/js/invoices.js"></script>
</body>
</html>