<?php
// ============================================================
//  ThreadB2B — admin_panel/invoices.php
//  Manajemen invoice: list, filter, detail item, tandai lunas.
// ============================================================
$pageTitle  = 'Manajemen Invoice';
$activePage = 'invoices';

require_once __DIR__ . '/partials/config.php';
?>
<?php include __DIR__ . '/partials/_header.php'; ?>

<style>
/* ---- Invoices page styles (reuses tb- conventions from buyers.php) ---- */
.tb-page-head{display:flex;align-items:center;justify-content:space-between;margin-bottom:22px;flex-wrap:wrap;gap:12px}
.tb-page-head h2{font-size:20px;font-weight:700;color:#1a1d29;margin:0}
.tb-page-head p{font-size:13.5px;color:#6b7280;margin:4px 0 0}

/* Summary cards */
.tb-summary-row{display:grid;grid-template-columns:repeat(4,1fr);gap:14px;margin-bottom:20px}
@media(max-width:900px){.tb-summary-row{grid-template-columns:repeat(2,1fr)}}
.tb-summary-card{background:#fff;border:1px solid #e9eaee;border-radius:14px;padding:16px 18px;display:flex;align-items:center;gap:12px}
.tb-summary-card .ico{width:40px;height:40px;border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:18px;flex-shrink:0}
.tb-summary-card .ico.blue{background:#eff6ff;color:#1d4ed8}
.tb-summary-card .ico.green{background:#ecfdf5;color:#047857}
.tb-summary-card .ico.red{background:#fef2f2;color:#b91c1c}
.tb-summary-card .ico.gray{background:#f3f4f6;color:#6b7280}
.tb-summary-card .val{font-size:17px;font-weight:700;color:#1a1d29;font-family:'DM Mono',monospace}
.tb-summary-card .lbl{font-size:12px;color:#9ca3af;font-weight:600;margin-top:1px}

/* Filter bar */
.tb-filter-bar{display:flex;align-items:center;gap:10px;margin-bottom:18px;flex-wrap:wrap}
.tb-filter-bar input[type="search"]{flex:1;min-width:200px;max-width:320px;height:38px;
    padding:0 12px;border:1px solid #e0e1e6;border-radius:8px;font-size:13.5px;color:#1a1d29;
    outline:none;transition:.15s}
.tb-filter-bar input[type="search"]:focus{border-color:#4338ca;box-shadow:0 0 0 3px #eef2ff}
.tb-filter-bar input[type="date"]{height:38px;padding:0 10px;border:1px solid #e0e1e6;border-radius:8px;
    font-size:13px;color:#1a1d29;outline:none}
.tb-status-tabs{display:flex;gap:4px;background:#f3f4f6;border-radius:9px;padding:3px}
.tb-status-tabs button{height:30px;padding:0 14px;border:none;border-radius:7px;font-size:12.5px;
    font-weight:600;cursor:pointer;color:#6b7280;background:transparent;transition:.12s}
.tb-status-tabs button.active{background:#fff;color:#1a1d29;box-shadow:0 1px 3px rgba(0,0,0,.1)}
.tb-filter-btn{height:38px;padding:0 14px;border-radius:8px;border:1px solid #e0e1e6;font-size:13px;
    font-weight:600;cursor:pointer;background:#fff;color:#374151;display:flex;align-items:center;gap:6px;transition:.12s}
.tb-filter-btn:hover{border-color:#4338ca;color:#4338ca}
.tb-filter-btn.primary{background:#4338ca;color:#fff;border-color:#4338ca}
.tb-filter-btn.primary:hover{background:#3730a3;color:#fff}

/* Table */
.tb-table-wrap{background:#fff;border:1px solid #e9eaee;border-radius:14px;overflow:hidden}
.tb-table{width:100%;border-collapse:collapse;font-size:13.5px}
.tb-table thead th{background:#f8f9fb;font-size:11.5px;font-weight:700;text-transform:uppercase;
    letter-spacing:.04em;color:#6b7280;padding:11px 14px;text-align:left;border-bottom:1px solid #e9eaee;
    white-space:nowrap}
.tb-table tbody tr{border-bottom:1px solid #f1f2f5;transition:.1s}
.tb-table tbody tr:last-child{border-bottom:none}
.tb-table tbody tr:hover{background:#fafbfc}
.tb-table td{padding:11px 14px;color:#374151;vertical-align:middle}
.tb-table td.is-mono{font-family:'DM Mono',monospace;font-size:12.5px;color:#4338ca;font-weight:600}
.tb-table td .company{font-weight:600;color:#1a1d29}
.tb-table td .pic{font-size:12px;color:#9ca3af;margin-top:2px}
.tb-table td.is-num{font-family:'DM Mono',monospace;font-size:13px;text-align:right}

/* Status badges (invoice) */
.tb-badge{display:inline-flex;align-items:center;gap:5px;padding:3px 9px;border-radius:20px;
    font-size:11.5px;font-weight:700;letter-spacing:.02em;white-space:nowrap}
.tb-badge::before{content:'';width:6px;height:6px;border-radius:50%;display:inline-block}
.tb-badge.DRAFT   {background:#f3f4f6;color:#6b7280}.tb-badge.DRAFT::before{background:#9ca3af}
.tb-badge.ISSUED  {background:#eff6ff;color:#1d4ed8}.tb-badge.ISSUED::before{background:#2563eb}
.tb-badge.PAID    {background:#ecfdf5;color:#047857}.tb-badge.PAID::before{background:#16a34a}
.tb-badge.OVERDUE {background:#fef2f2;color:#b91c1c}.tb-badge.OVERDUE::before{background:#dc2626}

.tb-overdue-chip{display:inline-flex;align-items:center;gap:4px;background:#fef2f2;
    color:#b91c1c;border-radius:6px;padding:2px 7px;font-size:11px;font-weight:700}

/* Action buttons in row */
.tb-row-actions{display:flex;gap:6px;align-items:center}
.tb-btn-icon{width:30px;height:30px;border-radius:7px;border:1px solid #e0e1e6;background:#fff;
    cursor:pointer;display:flex;align-items:center;justify-content:center;font-size:14px;
    color:#6b7280;transition:.12s;flex-shrink:0}
.tb-btn-icon:hover{border-color:#4338ca;color:#4338ca;background:#eef2ff}
.tb-btn-icon.is-success:hover{border-color:#16a34a;color:#16a34a;background:#ecfdf5}
.tb-btn-icon.is-danger:hover{border-color:#dc2626;color:#dc2626;background:#fef2f2}

/* Pagination */
.tb-pagination{display:flex;align-items:center;justify-content:space-between;padding:14px 18px;
    border-top:1px solid #f1f2f5;font-size:13px;color:#6b7280;flex-wrap:wrap;gap:10px}
.tb-pag-btns{display:flex;gap:4px}
.tb-pag-btns button{height:32px;min-width:32px;padding:0 10px;border:1px solid #e0e1e6;border-radius:7px;
    background:#fff;font-size:12.5px;font-weight:600;color:#374151;cursor:pointer;transition:.12s}
.tb-pag-btns button:hover:not(:disabled){border-color:#4338ca;color:#4338ca}
.tb-pag-btns button.active{background:#4338ca;border-color:#4338ca;color:#fff}
.tb-pag-btns button:disabled{opacity:.4;cursor:default}

/* Empty & loading states */
.tb-table-empty{text-align:center;padding:48px 20px;color:#9ca3af}
.tb-table-empty i{font-size:32px;display:block;margin-bottom:10px;opacity:.5}
.tb-table-empty p{margin:0;font-size:14px}
.tb-skeleton-row td .tb-skeleton{height:13px;border-radius:5px;background:linear-gradient(90deg,#eee 25%,#f5f5f5 37%,#eee 63%);
    background-size:400% 100%;animation:tbShimmer 1.4s ease infinite}
@keyframes tbShimmer{0%{background-position:100% 50%}100%{background-position:0 50%}}

/* ---- Modal ---- */
.tb-modal-overlay{position:fixed;inset:0;background:rgba(17,24,39,.45);z-index:1000;
    display:none;align-items:center;justify-content:center;padding:20px;backdrop-filter:blur(2px)}
.tb-modal-overlay.open{display:flex}
.tb-modal{background:#fff;border-radius:16px;width:100%;max-width:760px;max-height:92vh;
    overflow:hidden;display:flex;flex-direction:column;box-shadow:0 20px 60px rgba(0,0,0,.18)}
.tb-modal__head{padding:20px 24px 16px;border-bottom:1px solid #f1f2f5;display:flex;
    align-items:center;justify-content:space-between;flex-shrink:0}
.tb-modal__head h3{font-size:16px;font-weight:700;color:#1a1d29;margin:0}
.tb-modal__close{width:30px;height:30px;border-radius:8px;border:1px solid #e0e1e6;background:transparent;
    cursor:pointer;font-size:16px;color:#6b7280;display:flex;align-items:center;justify-content:center}
.tb-modal__close:hover{background:#f3f4f6}
.tb-modal__body{padding:20px 24px;overflow-y:auto;flex:1}
.tb-modal__foot{padding:14px 24px;border-top:1px solid #f1f2f5;display:flex;gap:8px;
    justify-content:flex-end;flex-shrink:0;flex-wrap:wrap}

/* Detail grid */
.tb-detail-grid{display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:20px}
@media(max-width:520px){.tb-detail-grid{grid-template-columns:1fr}}
.tb-detail-item label{display:block;font-size:11px;font-weight:700;text-transform:uppercase;
    letter-spacing:.06em;color:#9ca3af;margin-bottom:4px}
.tb-detail-item span{font-size:13.5px;color:#1a1d29;font-weight:500;word-break:break-word}
.tb-detail-item span.mono{font-family:'DM Mono',monospace;color:#4338ca}

.tb-section-title{font-size:12px;font-weight:700;text-transform:uppercase;letter-spacing:.05em;
    color:#9ca3af;margin:18px 0 10px;border-top:1px solid #f1f2f5;padding-top:14px}

/* Mini items table */
.tb-mini-table{width:100%;border-collapse:collapse;font-size:12.5px;margin-top:6px}
.tb-mini-table th{font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.04em;
    color:#9ca3af;padding:6px 8px;text-align:left;border-bottom:1px solid #f1f2f5}
.tb-mini-table td{padding:8px 8px;border-bottom:1px solid #f8f9fb;color:#374151}
.tb-mini-table td.is-num{font-family:'DM Mono',monospace;text-align:right}
.tb-mini-table tr:last-child td{border-bottom:none}
.tb-mini-table tfoot td{font-weight:700;color:#1a1d29;border-top:1px solid #e9eaee;border-bottom:none}

/* Buttons */
.tb-btn{height:36px;padding:0 16px;border-radius:8px;font-size:13px;font-weight:600;
    cursor:pointer;border:1px solid transparent;transition:.12s;display:inline-flex;align-items:center;gap:6px}
.tb-btn-primary{background:#4338ca;color:#fff;border-color:#4338ca}
.tb-btn-primary:hover{background:#3730a3}
.tb-btn-ghost{background:#fff;color:#374151;border-color:#e0e1e6}
.tb-btn-ghost:hover{border-color:#9ca3af}
.tb-btn-danger{background:#dc2626;color:#fff;border-color:#dc2626}
.tb-btn-danger:hover{background:#b91c1c}
.tb-btn-success{background:#16a34a;color:#fff;border-color:#16a34a}
.tb-btn-success:hover{background:#15803d}
.tb-btn:disabled{opacity:.5;cursor:default}

/* Alert inline */
.tb-inline-alert{display:none;align-items:center;gap:8px;padding:10px 14px;border-radius:8px;
    font-size:13px;margin-bottom:14px}
.tb-inline-alert.show{display:flex}
.tb-inline-alert.error{background:#fef2f2;color:#991b1b;border:1px solid #fecaca}
.tb-inline-alert.success{background:#ecfdf5;color:#065f46;border:1px solid #a7f3d0}
</style>

<div class="tb-layout">
    <?php include __DIR__ . '/partials/_sidebar.php'; ?>

    <main class="tb-main">
        <?php include __DIR__ . '/partials/_navbar.php'; ?>

        <div style="padding:24px;">

            <div class="tb-page-head">
                <div>
                    <h2>Manajemen Invoice</h2>
                    <p>Pantau status pembayaran, tagihan jatuh tempo, dan riwayat invoice buyer.</p>
                </div>
                <button class="tb-filter-btn primary" id="btnCreateInvoice">
                    <i class="bi bi-plus-lg"></i> Buat Invoice
                </button>
            </div>

            <!-- Alert global -->
            <div class="tb-inline-alert error" id="invGlobalError">
                <i class="bi bi-exclamation-triangle-fill"></i>
                <span id="invGlobalErrorText">Terjadi kesalahan.</span>
            </div>

            <!-- Summary cards -->
            <div class="tb-summary-row" id="invSummaryRow">
                <div class="tb-summary-card">
                    <div class="ico blue"><i class="bi bi-receipt"></i></div>
                    <div><div class="val" id="sumTotalInvoice">—</div><div class="lbl">Total Invoice</div></div>
                </div>
                <div class="tb-summary-card">
                    <div class="ico green"><i class="bi bi-check-circle"></i></div>
                    <div><div class="val" id="sumPaid">—</div><div class="lbl">Lunas (IDR)</div></div>
                </div>
                <div class="tb-summary-card">
                    <div class="ico gray"><i class="bi bi-hourglass-split"></i></div>
                    <div><div class="val" id="sumOutstanding">—</div><div class="lbl">Outstanding (IDR)</div></div>
                </div>
                <div class="tb-summary-card">
                    <div class="ico red"><i class="bi bi-exclamation-triangle"></i></div>
                    <div><div class="val" id="sumOverdue">—</div><div class="lbl">Overdue (IDR)</div></div>
                </div>
            </div>

            <!-- Filter bar -->
            <div class="tb-filter-bar">
                <input type="search" id="invSearch" placeholder="Cari invoice ID atau nama buyer…" autocomplete="off">
                <div class="tb-status-tabs" id="statusTabs">
                    <button class="active" data-status="all">Semua</button>
                    <button data-status="DRAFT">Draft</button>
                    <button data-status="ISSUED">Issued</button>
                    <button data-status="PAID">Lunas</button>
                    <button data-status="OVERDUE">Overdue</button>
                </div>
                <input type="date" id="invDateFrom" title="Dari tanggal">
                <input type="date" id="invDateTo" title="Sampai tanggal">
                <button class="tb-filter-btn" id="btnRefreshInvoices">
                    <i class="bi bi-arrow-clockwise"></i> Refresh
                </button>
            </div>

            <!-- Table -->
            <div class="tb-table-wrap">
                <table class="tb-table">
                    <thead>
                        <tr>
                            <th>Invoice ID</th>
                            <th>Buyer</th>
                            <th>Tgl Invoice</th>
                            <th>Jatuh Tempo</th>
                            <th style="text-align:right">Subtotal</th>
                            <th style="text-align:right">PPN</th>
                            <th style="text-align:right">Total</th>
                            <th>Status</th>
                            <th style="text-align:right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="invTableBody">
                        <!-- skeleton -->
                        <?php for ($i = 0; $i < 6; $i++): ?>
                        <tr class="tb-skeleton-row">
                            <?php for ($j = 0; $j < 9; $j++): ?>
                            <td><div class="tb-skeleton" style="width:<?= [90,120,80,80,70,60,70,70,60][$j] ?>px"></div></td>
                            <?php endfor; ?>
                        </tr>
                        <?php endfor; ?>
                    </tbody>
                </table>
                <div class="tb-pagination" id="invPagination" style="display:none">
                    <span id="invPagInfo">Menampilkan data…</span>
                    <div class="tb-pag-btns" id="invPagBtns"></div>
                </div>
            </div>

        </div><!-- /padding -->

    </main>
</div>

<!-- ============================================================
     MODAL — Detail Invoice
     ============================================================ -->
<div class="tb-modal-overlay" id="modalInvoice" role="dialog" aria-modal="true" aria-labelledby="modalInvoiceTitle">
    <div class="tb-modal">
        <div class="tb-modal__head">
            <h3 id="modalInvoiceTitle">Detail Invoice</h3>
            <button class="tb-modal__close" id="btnCloseModal"><i class="bi bi-x"></i></button>
        </div>
        <div class="tb-modal__body" id="modalInvoiceBody">
            <div style="text-align:center;padding:40px 0;color:#9ca3af">
                <i class="bi bi-hourglass-split" style="font-size:28px"></i>
                <p style="margin-top:10px;font-size:14px">Memuat data…</p>
            </div>
        </div>
        <div class="tb-modal__foot" id="modalInvoiceFoot">
            <button class="tb-btn tb-btn-ghost" id="btnCloseModal2">Tutup</button>
        </div>
    </div>
</div>

<!-- ============================================================
     MODAL — Konfirmasi Aksi
     ============================================================ -->
<div class="tb-modal-overlay" id="modalConfirm" role="dialog" aria-modal="true">
    <div class="tb-modal" style="max-width:420px">
        <div class="tb-modal__head">
            <h3 id="confirmTitle">Konfirmasi</h3>
            <button class="tb-modal__close" id="btnCloseConfirm"><i class="bi bi-x"></i></button>
        </div>
        <div class="tb-modal__body">
            <div class="tb-inline-alert error" id="confirmError">
                <i class="bi bi-exclamation-triangle-fill"></i>
                <span id="confirmErrorText"></span>
            </div>
            <p id="confirmMessage" style="font-size:14px;color:#374151;margin:0 0 6px"></p>
            <p id="confirmNote" style="font-size:12.5px;color:#9ca3af;margin:0"></p>
        </div>
        <div class="tb-modal__foot">
            <button class="tb-btn tb-btn-ghost" id="btnCancelConfirm">Batal</button>
            <button class="tb-btn" id="btnDoConfirm">Konfirmasi</button>
        </div>
    </div>
</div>

<!-- ============================================================
     MODAL — Buat Invoice (pilih buyer + SJ yang belum ditagih)
     ============================================================ -->
<div class="tb-modal-overlay" id="modalCreate" role="dialog" aria-modal="true">
    <div class="tb-modal" style="max-width:560px">
        <div class="tb-modal__head">
            <h3>Buat Invoice Baru</h3>
            <button class="tb-modal__close" id="btnCloseCreate"><i class="bi bi-x"></i></button>
        </div>
        <div class="tb-modal__body">
            <div class="tb-inline-alert error" id="createError">
                <i class="bi bi-exclamation-triangle-fill"></i>
                <span id="createErrorText"></span>
            </div>
            <div style="margin-bottom:14px">
                <label style="display:block;font-size:12px;font-weight:700;color:#6b7280;margin-bottom:6px">Pilih Buyer (Customer ID)</label>
                <select id="createCustomerId" style="width:100%;height:38px;border:1px solid #e0e1e6;border-radius:8px;padding:0 10px;font-size:13.5px;color:#1a1d29">
                    <option value="">— Memuat daftar buyer —</option>
                </select>
            </div>
            <div style="margin-bottom:14px">
                <label style="display:block;font-size:12px;font-weight:700;color:#6b7280;margin-bottom:6px">Surat Jalan Belum Ditagih</label>
                <div id="createSjList" style="border:1px solid #f1f2f5;border-radius:8px;max-height:220px;overflow-y:auto;padding:8px">
                    <p style="font-size:13px;color:#9ca3af;text-align:center;padding:16px 0;margin:0">Pilih buyer terlebih dahulu.</p>
                </div>
            </div>
            <div style="margin-bottom:4px">
                <label style="display:block;font-size:12px;font-weight:700;color:#6b7280;margin-bottom:6px">Tenor Pembayaran (hari)</label>
                <input type="number" id="createCreditDays" value="30" min="1" style="width:120px;height:36px;border:1px solid #e0e1e6;border-radius:8px;padding:0 10px;font-size:13.5px">
            </div>
        </div>
        <div class="tb-modal__foot">
            <button class="tb-btn tb-btn-ghost" id="btnCancelCreate">Batal</button>
            <button class="tb-btn tb-btn-primary" id="btnDoCreate">Buat Invoice</button>
        </div>
    </div>
</div>


<script>
(function () {
    'use strict';
    const ADMIN_URL = '<?= ADMIN_URL ?>';

    /* ── State ─────────────────────────────────────────────── */
    let state = {
        status   : 'all',
        search   : '',
        dateFrom : '',
        dateTo   : '',
        page     : 1,
        limit    : 20,
    };

    /* ── DOM refs ──────────────────────────────────────────── */
    const tbody         = document.getElementById('invTableBody');
    const pagination    = document.getElementById('invPagination');
    const pagInfo       = document.getElementById('invPagInfo');
    const pagBtns       = document.getElementById('invPagBtns');
    const searchInput   = document.getElementById('invSearch');
    const dateFromInput = document.getElementById('invDateFrom');
    const dateToInput   = document.getElementById('invDateTo');
    const statusTabs    = document.getElementById('statusTabs');
    const btnRefresh    = document.getElementById('btnRefreshInvoices');
    const globalError   = document.getElementById('invGlobalError');
    const globalErrText = document.getElementById('invGlobalErrorText');

    /* Summary */
    const sumTotalInvoice = document.getElementById('sumTotalInvoice');
    const sumPaid         = document.getElementById('sumPaid');
    const sumOutstanding  = document.getElementById('sumOutstanding');
    const sumOverdue      = document.getElementById('sumOverdue');

    /* Modal detail */
    const modalInvoice  = document.getElementById('modalInvoice');
    const modalBody     = document.getElementById('modalInvoiceBody');
    const modalFoot     = document.getElementById('modalInvoiceFoot');
    const modalTitle    = document.getElementById('modalInvoiceTitle');
    const btnCloseModal = document.getElementById('btnCloseModal');

    /* Modal konfirmasi */
    const modalConfirm  = document.getElementById('modalConfirm');
    const confirmTitle  = document.getElementById('confirmTitle');
    const confirmMsg    = document.getElementById('confirmMessage');
    const confirmNote   = document.getElementById('confirmNote');
    const confirmError  = document.getElementById('confirmError');
    const confirmErrTxt = document.getElementById('confirmErrorText');
    const btnDoConfirm  = document.getElementById('btnDoConfirm');
    const btnCancel     = document.getElementById('btnCancelConfirm');
    const btnCloseConf  = document.getElementById('btnCloseConfirm');

    /* Modal buat invoice */
    const modalCreate     = document.getElementById('modalCreate');
    const btnCreateInvoice= document.getElementById('btnCreateInvoice');
    const btnCloseCreate  = document.getElementById('btnCloseCreate');
    const btnCancelCreate = document.getElementById('btnCancelCreate');
    const btnDoCreate     = document.getElementById('btnDoCreate');
    const createError     = document.getElementById('createError');
    const createErrTxt    = document.getElementById('createErrorText');
    const createCustomerId= document.getElementById('createCustomerId');
    const createSjList    = document.getElementById('createSjList');
    const createCreditDays= document.getElementById('createCreditDays');

    /* ── Helpers ───────────────────────────────────────────── */
    function showGlobalError(msg) {
        globalErrText.textContent = msg;
        globalError.className = 'tb-inline-alert error show';
    }
    function hideGlobalError() { globalError.classList.remove('show'); }

    function formatRupiah(n) {
        return 'Rp ' + Number(n || 0).toLocaleString('id-ID', { maximumFractionDigits: 0 });
    }
    function fmtDate(str) {
        if (!str) return '—';
        return new Date(str).toLocaleDateString('id-ID', { day:'numeric', month:'short', year:'numeric' });
    }
    function escHtml(str) {
        return String(str ?? '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
    }
    function badgeHtml(status) {
        const label = { DRAFT:'Draft', ISSUED:'Issued', PAID:'Lunas', OVERDUE:'Overdue' }[status] || status;
        return `<span class="tb-badge ${status}">${label}</span>`;
    }
    function isPastDue(due, status) {
        if (!due || status === 'PAID') return false;
        return new Date(due) < new Date(new Date().toDateString());
    }

    /* ── Skeleton rows ──────────────────────────────────────── */
    function renderSkeleton() {
        const widths = [90,120,80,80,70,60,70,70,60];
        tbody.innerHTML = Array.from({ length: 6 }, () =>
            `<tr class="tb-skeleton-row">${widths.map(w =>
                `<td><div class="tb-skeleton" style="width:${w}px"></div></td>`
            ).join('')}</tr>`
        ).join('');
        pagination.style.display = 'none';
    }

    /* ── Fetch summary ─────────────────────────────────────── */
    async function loadSummary() {
        try {
            const res  = await fetch(`${ADMIN_URL}/fetch-data/fetchInvoiceSummary.php`, { credentials: 'same-origin' });
            const json = await res.json();
            if (json.status === 'error') throw new Error(json.message);
            sumTotalInvoice.textContent = json.total_invoice ?? 0;
            sumPaid.textContent         = formatRupiah(json.total_paid);
            sumOutstanding.textContent  = formatRupiah(json.total_outstanding);
            sumOverdue.textContent      = formatRupiah(json.total_overdue);
        } catch (err) {
            ['sumTotalInvoice','sumPaid','sumOutstanding','sumOverdue'].forEach(id => {
                document.getElementById(id).textContent = '—';
            });
        }
    }

    /* ── Fetch invoices ────────────────────────────────────── */
    let searchTimer;
    async function loadInvoices() {
        hideGlobalError();
        renderSkeleton();

        const params = new URLSearchParams({
            status    : state.status,
            search    : state.search,
            date_from : state.dateFrom,
            date_to   : state.dateTo,
            page      : state.page,
            limit     : state.limit,
        });

        try {
            const res  = await fetch(`${ADMIN_URL}/fetch-data/fetchInvoices.php?${params}`, { credentials: 'same-origin' });
            const contentType = res.headers.get('content-type') || '';
            if (!contentType.includes('application/json')) {
                throw new Error('Sesi habis atau terjadi error server. Silakan refresh halaman.');
            }
            const json = await res.json();
            if (json.status === 'error') throw new Error(json.message);
            renderInvoices(json.invoices, json.pagination);
        } catch (err) {
            tbody.innerHTML = `<tr><td colspan="9" class="tb-table-empty">
                <i class="bi bi-exclamation-triangle"></i>
                <p>${escHtml(err.message || 'Gagal memuat data invoice.')}</p></td></tr>`;
            showGlobalError(err.message || 'Gagal memuat data.');
            pagination.style.display = 'none';
        }
    }

    function renderInvoices(invoices, pag) {
        if (!invoices || invoices.length === 0) {
            tbody.innerHTML = `<tr><td colspan="9" class="tb-table-empty">
                <i class="bi bi-receipt"></i>
                <p>Tidak ada invoice yang ditemukan.</p></td></tr>`;
            pagination.style.display = 'none';
            return;
        }

        tbody.innerHTML = invoices.map(inv => {
            const overdue = isPastDue(inv.due_date, inv.status);
            const statusDisplay = overdue && inv.status !== 'PAID' ? 'OVERDUE' : inv.status;

            return `<tr>
                <td class="is-mono">${escHtml(inv.invoice_id)}</td>
                <td>
                    <div class="company">${escHtml(inv.nama_perusahaan || inv.customer_id)}</div>
                    <div class="pic">${escHtml(inv.customer_id)}</div>
                </td>
                <td style="white-space:nowrap;font-size:12.5px;color:#6b7280">${fmtDate(inv.invoice_date)}</td>
                <td style="white-space:nowrap;font-size:12.5px;color:${overdue ? '#b91c1c' : '#6b7280'};font-weight:${overdue ? '700' : '400'}">
                    ${fmtDate(inv.due_date)}
                    ${overdue ? '<i class="bi bi-exclamation-triangle-fill" style="margin-left:4px"></i>' : ''}
                </td>
                <td class="is-num">${formatRupiah(inv.subtotal_idr)}</td>
                <td class="is-num">${formatRupiah(inv.ppn_idr)}</td>
                <td class="is-num" style="font-weight:700;color:#1a1d29">${formatRupiah(inv.total_idr)}</td>
                <td>${badgeHtml(statusDisplay)}</td>
                <td>
                    <div class="tb-row-actions" style="justify-content:flex-end">
                        <button class="tb-btn-icon" title="Lihat detail"
                            onclick="openInvoiceDetail('${escHtml(inv.invoice_id)}')">
                            <i class="bi bi-eye"></i>
                        </button>
                        ${inv.status !== 'PAID' ? `
                        <button class="tb-btn-icon is-success" title="Tandai lunas"
                            onclick="confirmMarkPaid('${escHtml(inv.invoice_id)}','${escHtml(inv.nama_perusahaan || inv.customer_id)}')">
                            <i class="bi bi-check-lg"></i>
                        </button>` : ''}
                        <button class="tb-btn-icon" title="Cetak / unduh"
                            onclick="printInvoice('${escHtml(inv.invoice_id)}')">
                            <i class="bi bi-printer"></i>
                        </button>
                    </div>
                </td>
            </tr>`;
        }).join('');

        // Pagination
        const total = pag.total;
        const pages = pag.total_pages;
        const from  = (pag.page - 1) * pag.limit + 1;
        const to    = Math.min(pag.page * pag.limit, total);
        pagInfo.textContent = `Menampilkan ${from}–${to} dari ${total} invoice`;

        pagBtns.innerHTML = '';
        pagBtns.appendChild(makePageBtn('‹', pag.page - 1, pag.page <= 1));

        pageRange(pag.page, pages).forEach(p => {
            if (p === '…') {
                const el = document.createElement('button');
                el.textContent = '…';
                el.disabled = true;
                pagBtns.appendChild(el);
            } else {
                pagBtns.appendChild(makePageBtn(p, p, false, p === pag.page));
            }
        });

        pagBtns.appendChild(makePageBtn('›', pag.page + 1, pag.page >= pages));
        pagination.style.display = 'flex';
    }

    function makePageBtn(label, targetPage, disabled, active = false) {
        const btn = document.createElement('button');
        btn.textContent = label;
        btn.disabled = disabled;
        if (active) btn.classList.add('active');
        if (!disabled) btn.addEventListener('click', () => { state.page = targetPage; loadInvoices(); });
        return btn;
    }

    function pageRange(cur, total) {
        if (total <= 7) return Array.from({ length: total }, (_, i) => i + 1);
        if (cur <= 4)   return [1,2,3,4,5,'…',total];
        if (cur >= total - 3) return [1,'…',total-4,total-3,total-2,total-1,total];
        return [1,'…',cur-1,cur,cur+1,'…',total];
    }

    /* ── Modal Detail Invoice ──────────────────────────────── */
    window.openInvoiceDetail = async function (invoiceId) {
        modalBody.innerHTML = `<div style="text-align:center;padding:40px 0;color:#9ca3af">
            <i class="bi bi-hourglass-split" style="font-size:28px"></i>
            <p style="margin-top:10px;font-size:14px">Memuat data…</p></div>`;
        modalFoot.innerHTML = `<button class="tb-btn tb-btn-ghost" id="btnCloseModal2">Tutup</button>`;
        document.getElementById('btnCloseModal2').addEventListener('click', closeModalInvoice);
        modalTitle.textContent = 'Detail Invoice';
        openModal(modalInvoice);

        try {
            const res  = await fetch(`${ADMIN_URL}/fetch-data/fetchInvoiceDetail.php?invoice_id=${encodeURIComponent(invoiceId)}`, { credentials: 'same-origin' });
            const json = await res.json();
            if (json.status === 'error') throw new Error(json.message);
            renderInvoiceDetail(json);
        } catch (err) {
            modalBody.innerHTML = `<div style="text-align:center;padding:32px;color:#b91c1c;font-size:14px">
                <i class="bi bi-exclamation-triangle-fill" style="font-size:28px;display:block;margin-bottom:8px"></i>
                ${escHtml(err.message || 'Gagal memuat detail invoice.')}
            </div>`;
        }
    };

    function renderInvoiceDetail(d) {
        const inv   = d.invoice;
        const items = d.items || [];
        const overdue = isPastDue(inv.due_date, inv.status);
        const statusDisplay = overdue && inv.status !== 'PAID' ? 'OVERDUE' : inv.status;

        modalTitle.textContent = inv.invoice_id;

        modalBody.innerHTML = `
            <div class="tb-detail-grid">
                <div class="tb-detail-item"><label>Invoice ID</label><span class="mono">${escHtml(inv.invoice_id)}</span></div>
                <div class="tb-detail-item"><label>Status</label><span>${badgeHtml(statusDisplay)}</span></div>
                <div class="tb-detail-item"><label>Buyer</label><span>${escHtml(inv.nama_perusahaan || inv.customer_id)}</span></div>
                <div class="tb-detail-item"><label>Customer ID</label><span class="mono">${escHtml(inv.customer_id)}</span></div>
                <div class="tb-detail-item"><label>Tanggal Invoice</label><span>${fmtDate(inv.invoice_date)}</span></div>
                <div class="tb-detail-item"><label>Jatuh Tempo</label><span style="${overdue ? 'color:#b91c1c' : ''}">${fmtDate(inv.due_date)}</span></div>
                <div class="tb-detail-item"><label>Tenor</label><span>${inv.credit_days ? inv.credit_days + ' hari' : '—'}</span></div>
                <div class="tb-detail-item"><label>Dibuat Oleh</label><span>${escHtml(inv.created_by || '—')}</span></div>
            </div>

            <div class="tb-section-title">Rincian Item</div>
            <table class="tb-mini-table">
                <thead>
                    <tr>
                        <th>SJ No</th>
                        <th>Item</th>
                        <th>Warna</th>
                        <th style="text-align:right">Qty</th>
                        <th style="text-align:right">Harga</th>
                        <th style="text-align:right">Jumlah</th>
                    </tr>
                </thead>
                <tbody>
                    ${items.length > 0 ? items.map(it => `<tr>
                        <td style="font-family:'DM Mono',monospace;font-size:11.5px;color:#4338ca">${escHtml(it.sj_no || '—')}</td>
                        <td>${escHtml(it.item_no || '—')}</td>
                        <td>${escHtml(it.colour_no || '—')}</td>
                        <td class="is-num">${Number(it.qty || 0).toLocaleString('id-ID')} ${escHtml(it.unit || '')}</td>
                        <td class="is-num">${formatRupiah(it.price_idr)}</td>
                        <td class="is-num">${formatRupiah(it.amount_idr)}</td>
                    </tr>`).join('') : `<tr><td colspan="6" style="text-align:center;color:#9ca3af;padding:16px 0">Tidak ada item.</td></tr>`}
                </tbody>
                <tfoot>
                    <tr><td colspan="5" style="text-align:right">Subtotal</td><td class="is-num">${formatRupiah(inv.subtotal_idr)}</td></tr>
                    <tr><td colspan="5" style="text-align:right">PPN (${inv.ppn_pct ?? 11}%)</td><td class="is-num">${formatRupiah(inv.ppn_idr)}</td></tr>
                    <tr><td colspan="5" style="text-align:right;font-size:13.5px">Total</td><td class="is-num" style="font-size:13.5px;color:#4338ca">${formatRupiah(inv.total_idr)}</td></tr>
                </tfoot>
            </table>
        `;

        const btns = [`<button class="tb-btn tb-btn-ghost" onclick="closeModalInvoice()">Tutup</button>`];
        btns.unshift(`<button class="tb-btn tb-btn-ghost" onclick="printInvoice('${escHtml(inv.invoice_id)}')"><i class="bi bi-printer"></i> Cetak</button>`);
        if (inv.status !== 'PAID') {
            btns.unshift(`<button class="tb-btn tb-btn-success" onclick="closeModalInvoice();confirmMarkPaid('${escHtml(inv.invoice_id)}','${escHtml(inv.nama_perusahaan || inv.customer_id)}')">Tandai Lunas</button>`);
        }
        modalFoot.innerHTML = btns.join('');
    }

    window.closeModalInvoice = function () { closeModal(modalInvoice); };

    /* ── Modal Tandai Lunas ────────────────────────────────── */
    window.confirmMarkPaid = function (invoiceId, namaBuyer) {
        confirmTitle.textContent = 'Tandai Lunas';
        confirmMsg.textContent   = `Tandai invoice "${invoiceId}" milik "${namaBuyer}" sebagai lunas?`;
        confirmNote.textContent  = 'Tindakan ini akan mengubah status invoice menjadi PAID dan tidak dapat dibatalkan dari halaman ini.';
        confirmError.classList.remove('show');

        btnDoConfirm.className   = 'tb-btn tb-btn-success';
        btnDoConfirm.textContent = 'Ya, Tandai Lunas';
        btnDoConfirm.onclick     = () => doMarkPaid(invoiceId);
        openModal(modalConfirm);
    };

    async function doMarkPaid(invoiceId) {
        setConfirmLoading(true);
        confirmError.classList.remove('show');
        try {
            const res  = await fetch(`${ADMIN_URL}/fetch-data/markInvoicePaid.php`, {
                method     : 'POST',
                credentials: 'same-origin',
                headers    : { 'Content-Type': 'application/json' },
                body       : JSON.stringify({ invoice_id: invoiceId }),
            });
            const json = await res.json();
            if (json.status === 'error') throw new Error(json.message);
            closeModal(modalConfirm);
            loadInvoices();
            loadSummary();
        } catch (err) {
            confirmErrTxt.textContent = err.message || 'Gagal menandai invoice sebagai lunas.';
            confirmError.classList.add('show');
        } finally {
            setConfirmLoading(false);
        }
    }

    function setConfirmLoading(loading) {
        btnDoConfirm.disabled = loading;
        document.getElementById('btnCancelConfirm').disabled = loading;
        if (loading) btnDoConfirm.innerHTML = '<i class="bi bi-hourglass-split"></i> Memproses…';
    }

    /* ── Cetak Invoice ─────────────────────────────────────── */
    window.printInvoice = function (invoiceId) {
        window.open(`${ADMIN_URL}/print/invoice.php?invoice_id=${encodeURIComponent(invoiceId)}`, '_blank');
    };

    /* ── Modal Buat Invoice ────────────────────────────────── */
    btnCreateInvoice.addEventListener('click', openCreateModal);

    async function openCreateModal() {
        createError.classList.remove('show');
        createCustomerId.innerHTML = '<option value="">— Memuat daftar buyer —</option>';
        createSjList.innerHTML = `<p style="font-size:13px;color:#9ca3af;text-align:center;padding:16px 0;margin:0">Pilih buyer terlebih dahulu.</p>`;
        createCreditDays.value = 30;
        openModal(modalCreate);

        try {
            const res  = await fetch(`${ADMIN_URL}/fetch-data/fetchActiveBuyers.php`, { credentials: 'same-origin' });
            const json = await res.json();
            if (json.status === 'error') throw new Error(json.message);
            const buyers = json.buyers || [];
            createCustomerId.innerHTML = '<option value="">— Pilih buyer —</option>' +
                buyers.map(b => `<option value="${escHtml(b.customer_id)}">${escHtml(b.nama_perusahaan)} (${escHtml(b.customer_id)})</option>`).join('');
        } catch (err) {
            createCustomerId.innerHTML = '<option value="">Gagal memuat daftar buyer</option>';
        }
    }

    createCustomerId.addEventListener('change', async () => {
        const customerId = createCustomerId.value;
        if (!customerId) {
            createSjList.innerHTML = `<p style="font-size:13px;color:#9ca3af;text-align:center;padding:16px 0;margin:0">Pilih buyer terlebih dahulu.</p>`;
            return;
        }
        createSjList.innerHTML = `<p style="font-size:13px;color:#9ca3af;text-align:center;padding:16px 0;margin:0">Memuat surat jalan…</p>`;
        try {
            const res  = await fetch(`${ADMIN_URL}/fetch-data/fetchUninvoicedSJ.php?customer_id=${encodeURIComponent(customerId)}`, { credentials: 'same-origin' });
            const json = await res.json();
            if (json.status === 'error') throw new Error(json.message);
            const sjList = json.sj_list || [];
            if (sjList.length === 0) {
                createSjList.innerHTML = `<p style="font-size:13px;color:#9ca3af;text-align:center;padding:16px 0;margin:0">Tidak ada surat jalan yang belum ditagih.</p>`;
                return;
            }
            createSjList.innerHTML = sjList.map(sj => `
                <label style="display:flex;align-items:center;gap:8px;padding:8px 6px;border-bottom:1px solid #f8f9fb;font-size:13px;cursor:pointer">
                    <input type="checkbox" class="sj-checkbox" value="${escHtml(sj.sj_no)}">
                    <span style="font-family:'DM Mono',monospace;color:#4338ca;font-size:12px">${escHtml(sj.sj_no)}</span>
                    <span style="color:#6b7280;font-size:12px">${fmtDate(sj.sj_date)}</span>
                    <span style="margin-left:auto;font-weight:600">${formatRupiah(sj.total_qty)} ${escHtml(sj.unit || 'KG')}</span>
                </label>`).join('');
        } catch (err) {
            createSjList.innerHTML = `<p style="font-size:13px;color:#b91c1c;text-align:center;padding:16px 0;margin:0">${escHtml(err.message || 'Gagal memuat surat jalan.')}</p>`;
        }
    });

    btnDoCreate.addEventListener('click', async () => {
        createError.classList.remove('show');
        const customerId  = createCustomerId.value;
        const creditDays  = parseInt(createCreditDays.value, 10) || 30;
        const sjChecked   = Array.from(createSjList.querySelectorAll('.sj-checkbox:checked')).map(c => c.value);

        if (!customerId) {
            createErrTxt.textContent = 'Pilih buyer terlebih dahulu.';
            createError.classList.add('show');
            return;
        }
        if (sjChecked.length === 0) {
            createErrTxt.textContent = 'Pilih minimal satu surat jalan untuk ditagih.';
            createError.classList.add('show');
            return;
        }

        btnDoCreate.disabled = true;
        btnDoCreate.innerHTML = '<i class="bi bi-hourglass-split"></i> Memproses…';
        try {
            const res  = await fetch(`${ADMIN_URL}/fetch-data/createInvoice.php`, {
                method     : 'POST',
                credentials: 'same-origin',
                headers    : { 'Content-Type': 'application/json' },
                body       : JSON.stringify({ customer_id: customerId, credit_days: creditDays, sj_list: sjChecked }),
            });
            const json = await res.json();
            if (json.status === 'error') throw new Error(json.message);
            closeModal(modalCreate);
            loadInvoices();
            loadSummary();
        } catch (err) {
            createErrTxt.textContent = err.message || 'Gagal membuat invoice.';
            createError.classList.add('show');
        } finally {
            btnDoCreate.disabled = false;
            btnDoCreate.textContent = 'Buat Invoice';
        }
    });

    [btnCloseCreate, btnCancelCreate].forEach(b => b.addEventListener('click', () => closeModal(modalCreate)));

    /* ── Modal helpers ─────────────────────────────────────── */
    function openModal(el) {
        el.classList.add('open');
        document.body.style.overflow = 'hidden';
    }
    function closeModal(el) {
        el.classList.remove('open');
        document.body.style.overflow = '';
    }

    /* ── Event listeners ───────────────────────────────────── */
    btnCloseModal?.addEventListener('click', closeModalInvoice);
    [btnCancel, btnCloseConf].forEach(b => b?.addEventListener('click', () => closeModal(modalConfirm)));

    [modalInvoice, modalConfirm, modalCreate].forEach(m => {
        m.addEventListener('click', e => { if (e.target === m) closeModal(m); });
    });

    statusTabs.querySelectorAll('button').forEach(btn => {
        btn.addEventListener('click', () => {
            statusTabs.querySelectorAll('button').forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
            state.status = btn.dataset.status;
            state.page   = 1;
            loadInvoices();
        });
    });

    searchInput.addEventListener('input', () => {
        clearTimeout(searchTimer);
        searchTimer = setTimeout(() => {
            state.search = searchInput.value.trim();
            state.page   = 1;
            loadInvoices();
        }, 380);
    });

    [dateFromInput, dateToInput].forEach(inp => {
        inp.addEventListener('change', () => {
            state.dateFrom = dateFromInput.value;
            state.dateTo   = dateToInput.value;
            state.page     = 1;
            loadInvoices();
        });
    });

    btnRefresh.addEventListener('click', () => { loadInvoices(); loadSummary(); });

    document.addEventListener('keydown', e => {
        if (e.key === 'Escape') {
            if (modalConfirm.classList.contains('open')) closeModal(modalConfirm);
            else if (modalCreate.classList.contains('open')) closeModal(modalCreate);
            else if (modalInvoice.classList.contains('open')) closeModal(modalInvoice);
        }
    });

    /* Initial prefill status dari URL ?status= */
    const urlStatus = new URLSearchParams(location.search).get('status');
    if (urlStatus) {
        const matchBtn = statusTabs.querySelector(`[data-status="${urlStatus}"]`);
        if (matchBtn) {
            statusTabs.querySelectorAll('button').forEach(b => b.classList.remove('active'));
            matchBtn.classList.add('active');
            state.status = urlStatus;
        }
    }

    /* ── Init ──────────────────────────────────────────────── */
    loadInvoices();
    loadSummary();

})();
</script>

<?php
$extraJs = [];
include __DIR__ . '/partials/_footer.php';
?>