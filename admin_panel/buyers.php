<?php
// ============================================================
//  ThreadB2B — admin_panel/buyers.php
//  Manajemen buyer: list, filter, verifikasi, blokir, detail.
// ============================================================
$pageTitle  = 'Manajemen Buyer';
$activePage = 'buyers';

require_once __DIR__ . '/partials/config.php';
?>
<?php include __DIR__ . '/partials/_header.php'; ?>

<style>
/* ---- Buyers page styles ---- */
.tb-page-head{display:flex;align-items:center;justify-content:space-between;margin-bottom:22px;flex-wrap:wrap;gap:12px}
.tb-page-head h2{font-size:20px;font-weight:700;color:#1a1d29;margin:0}
.tb-page-head p{font-size:13.5px;color:#6b7280;margin:4px 0 0}

/* Filter bar */
.tb-filter-bar{display:flex;align-items:center;gap:10px;margin-bottom:18px;flex-wrap:wrap}
.tb-filter-bar input[type="search"]{flex:1;min-width:200px;max-width:320px;height:38px;
    padding:0 12px;border:1px solid #e0e1e6;border-radius:8px;font-size:13.5px;color:#1a1d29;
    outline:none;transition:.15s}
.tb-filter-bar input[type="search"]:focus{border-color:#4338ca;box-shadow:0 0 0 3px #eef2ff}
.tb-status-tabs{display:flex;gap:4px;background:#f3f4f6;border-radius:9px;padding:3px}
.tb-status-tabs button{height:30px;padding:0 14px;border:none;border-radius:7px;font-size:12.5px;
    font-weight:600;cursor:pointer;color:#6b7280;background:transparent;transition:.12s}
.tb-status-tabs button.active{background:#fff;color:#1a1d29;box-shadow:0 1px 3px rgba(0,0,0,.1)}
.tb-filter-btn{height:38px;padding:0 14px;border-radius:8px;border:1px solid #e0e1e6;font-size:13px;
    font-weight:600;cursor:pointer;background:#fff;color:#374151;display:flex;align-items:center;gap:6px;transition:.12s}
.tb-filter-btn:hover{border-color:#4338ca;color:#4338ca}

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

/* Status badges */
.tb-badge{display:inline-flex;align-items:center;gap:5px;padding:3px 9px;border-radius:20px;
    font-size:11.5px;font-weight:700;letter-spacing:.02em;white-space:nowrap}
.tb-badge::before{content:'';width:6px;height:6px;border-radius:50%;display:inline-block}
.tb-badge.pending  {background:#fffbeb;color:#b45309}.tb-badge.pending::before{background:#d97706}
.tb-badge.approved {background:#ecfdf5;color:#047857}.tb-badge.approved::before{background:#16a34a}
.tb-badge.rejected {background:#fef2f2;color:#b91c1c}.tb-badge.rejected::before{background:#dc2626}
.tb-badge.blocked  {background:#f3f4f6;color:#6b7280}.tb-badge.blocked::before{background:#9ca3af}

/* Action buttons in row */
.tb-row-actions{display:flex;gap:6px;align-items:center}
.tb-btn-icon{width:30px;height:30px;border-radius:7px;border:1px solid #e0e1e6;background:#fff;
    cursor:pointer;display:flex;align-items:center;justify-content:center;font-size:14px;
    color:#6b7280;transition:.12s;flex-shrink:0}
.tb-btn-icon:hover{border-color:#4338ca;color:#4338ca;background:#eef2ff}
.tb-btn-icon.is-danger:hover{border-color:#dc2626;color:#dc2626;background:#fef2f2}
.tb-btn-icon.is-success:hover{border-color:#16a34a;color:#16a34a;background:#ecfdf5}

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
.tb-modal{background:#fff;border-radius:16px;width:100%;max-width:680px;max-height:92vh;
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

/* Stats mini row */
.tb-stat-mini{display:grid;grid-template-columns:repeat(3,1fr);gap:10px;margin-bottom:4px}
.tb-stat-mini-item{background:#f8f9fb;border-radius:10px;padding:12px 14px;text-align:center}
.tb-stat-mini-item .val{font-size:18px;font-weight:700;color:#1a1d29;font-family:'DM Mono',monospace}
.tb-stat-mini-item .lbl{font-size:11px;color:#9ca3af;font-weight:600;margin-top:2px;text-transform:uppercase}

/* Mini invoice table */
.tb-mini-table{width:100%;border-collapse:collapse;font-size:12.5px;margin-top:6px}
.tb-mini-table th{font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.04em;
    color:#9ca3af;padding:6px 8px;text-align:left;border-bottom:1px solid #f1f2f5}
.tb-mini-table td{padding:8px 8px;border-bottom:1px solid #f8f9fb;color:#374151}
.tb-mini-table tr:last-child td{border-bottom:none}

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
.tb-btn-warning{background:#d97706;color:#fff;border-color:#d97706}
.tb-btn-warning:hover{background:#b45309}
.tb-btn:disabled{opacity:.5;cursor:default}

/* Alert inline */
.tb-inline-alert{display:none;align-items:center;gap:8px;padding:10px 14px;border-radius:8px;
    font-size:13px;margin-bottom:14px}
.tb-inline-alert.show{display:flex}
.tb-inline-alert.error{background:#fef2f2;color:#991b1b;border:1px solid #fecaca}
.tb-inline-alert.success{background:#ecfdf5;color:#065f46;border:1px solid #a7f3d0}

/* Invoice status badge reuse */
.inv-badge{display:inline-block;padding:2px 8px;border-radius:12px;font-size:11px;font-weight:700}
.inv-badge.PAID   {background:#ecfdf5;color:#047857}
.inv-badge.ISSUED {background:#eff6ff;color:#1d4ed8}
.inv-badge.OVERDUE{background:#fef2f2;color:#b91c1c}
.inv-badge.DRAFT  {background:#f3f4f6;color:#6b7280}

/* Overdue warning chip in table */
.tb-overdue-chip{display:inline-flex;align-items:center;gap:4px;background:#fef2f2;
    color:#b91c1c;border-radius:6px;padding:2px 7px;font-size:11px;font-weight:700}
</style>

<div class="tb-layout">
    <?php include __DIR__ . '/partials/_sidebar.php'; ?>

    <main class="tb-main">
        <?php include __DIR__ . '/partials/_navbar.php'; ?>

        <div style="padding:24px;">

            <div class="tb-page-head">
                <div>
                    <h2>Manajemen Buyer</h2>
                    <p>Verifikasi, blokir, dan pantau semua buyer yang terdaftar.</p>
                </div>
            </div>

            <!-- Alert global -->
            <div class="tb-inline-alert error" id="buyerGlobalError">
                <i class="bi bi-exclamation-triangle-fill"></i>
                <span id="buyerGlobalErrorText">Terjadi kesalahan.</span>
            </div>

            <!-- Filter bar -->
            <div class="tb-filter-bar">
                <input type="search" id="buyerSearch" placeholder="Cari perusahaan atau PIC…" autocomplete="off">
                <div class="tb-status-tabs" id="statusTabs">
                    <button class="active" data-status="all">Semua</button>
                    <button data-status="pending">Pending</button>
                    <button data-status="approved">Approved</button>
                    <button data-status="rejected">Ditolak</button>
                    <button data-status="blocked">Diblokir</button>
                </div>
                <button class="tb-filter-btn" id="btnRefreshBuyers">
                    <i class="bi bi-arrow-clockwise"></i> Refresh
                </button>
            </div>

            <!-- Table -->
            <div class="tb-table-wrap">
                <table class="tb-table">
                    <thead>
                        <tr>
                            <th>Customer ID</th>
                            <th>Perusahaan / PIC</th>
                            <th>Email</th>
                            <th>No. WA</th>
                            <th>Negara</th>
                            <th>Invoice</th>
                            <th>Status</th>
                            <th>Terdaftar</th>
                            <th style="text-align:right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="buyerTableBody">
                        <!-- skeleton -->
                        <?php for ($i = 0; $i < 6; $i++): ?>
                        <tr class="tb-skeleton-row">
                            <?php for ($j = 0; $j < 9; $j++): ?>
                            <td><div class="tb-skeleton" style="width:<?= [70,120,130,90,60,50,70,70,60][$j] ?>px"></div></td>
                            <?php endfor; ?>
                        </tr>
                        <?php endfor; ?>
                    </tbody>
                </table>
                <div class="tb-pagination" id="buyerPagination" style="display:none">
                    <span id="buyerPagInfo">Menampilkan data…</span>
                    <div class="tb-pag-btns" id="buyerPagBtns"></div>
                </div>
            </div>

        </div><!-- /padding -->

    </main>
</div>

<!-- ============================================================
     MODAL — Detail Buyer
     ============================================================ -->
<div class="tb-modal-overlay" id="modalBuyer" role="dialog" aria-modal="true" aria-labelledby="modalBuyerTitle">
    <div class="tb-modal">
        <div class="tb-modal__head">
            <h3 id="modalBuyerTitle">Detail Buyer</h3>
            <button class="tb-modal__close" id="btnCloseModal"><i class="bi bi-x"></i></button>
        </div>
        <div class="tb-modal__body" id="modalBuyerBody">
            <!-- Diisi dinamis via JS -->
            <div style="text-align:center;padding:40px 0;color:#9ca3af">
                <i class="bi bi-hourglass-split" style="font-size:28px"></i>
                <p style="margin-top:10px;font-size:14px">Memuat data…</p>
            </div>
        </div>
        <div class="tb-modal__foot" id="modalBuyerFoot">
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


<script>
(function () {
    'use strict';
    const ADMIN_URL = '<?= ADMIN_URL ?>';

    /* ── State ─────────────────────────────────────────────── */
    let state = {
        status : 'all',
        search : '',
        page   : 1,
        limit  : 20,
    };

    /* ── DOM refs ──────────────────────────────────────────── */
    const tbody         = document.getElementById('buyerTableBody');
    const pagination    = document.getElementById('buyerPagination');
    const pagInfo       = document.getElementById('buyerPagInfo');
    const pagBtns       = document.getElementById('buyerPagBtns');
    const searchInput   = document.getElementById('buyerSearch');
    const statusTabs    = document.getElementById('statusTabs');
    const btnRefresh    = document.getElementById('btnRefreshBuyers');
    const globalError   = document.getElementById('buyerGlobalError');
    const globalErrText = document.getElementById('buyerGlobalErrorText');

    /* Modal detail */
    const modalBuyer    = document.getElementById('modalBuyer');
    const modalBody     = document.getElementById('modalBuyerBody');
    const modalFoot     = document.getElementById('modalBuyerFoot');
    const modalTitle    = document.getElementById('modalBuyerTitle');
    const btnCloseModal = document.getElementById('btnCloseModal');
    const btnCloseModal2= document.getElementById('btnCloseModal2');

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

    /* ── Helpers ───────────────────────────────────────────── */
    function showGlobalError(msg) {
        globalErrText.textContent = msg;
        globalError.classList.add('show');
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

    const STATUS_LABEL = {
        pending : 'Pending',
        approved: 'Approved',
        rejected: 'Ditolak',
        blocked : 'Diblokir',
    };

    function badgeHtml(status) {
        return `<span class="tb-badge ${status}">${STATUS_LABEL[status] || status}</span>`;
    }

    /* ── Skeleton rows ──────────────────────────────────────── */
    function renderSkeleton() {
        const widths = [70,120,130,90,60,50,70,70,60];
        tbody.innerHTML = Array.from({ length: 6 }, () =>
            `<tr class="tb-skeleton-row">${widths.map(w =>
                `<td><div class="tb-skeleton" style="width:${w}px"></div></td>`
            ).join('')}</tr>`
        ).join('');
        pagination.style.display = 'none';
    }

    /* ── Fetch buyers ──────────────────────────────────────── */
    let searchTimer;
    async function loadBuyers() {
        hideGlobalError();
        renderSkeleton();

        const params = new URLSearchParams({
            status : state.status,
            search : state.search,
            page   : state.page,
            limit  : state.limit,
        });

        try {
            const res  = await fetch(`${ADMIN_URL}/fetch-data/fetchBuyers.php?${params}`, { credentials: 'same-origin' });
            const contentType = res.headers.get('content-type') || '';
            if (!contentType.includes('application/json')) {
                throw new Error('Sesi habis atau terjadi error server. Silakan refresh halaman.');
            }
            const json = await res.json();
            if (json.status === 'error') throw new Error(json.message);
            renderBuyers(json.buyers, json.pagination);
        } catch (err) {
            tbody.innerHTML = `<tr><td colspan="9" class="tb-table-empty">
                <i class="bi bi-exclamation-triangle"></i>
                <p>${err.message || 'Gagal memuat data buyer.'}</p></td></tr>`;
            showGlobalError(err.message || 'Gagal memuat data.');
            pagination.style.display = 'none';
        }
    }

    function renderBuyers(buyers, pag) {
        if (!buyers || buyers.length === 0) {
            tbody.innerHTML = `<tr><td colspan="9" class="tb-table-empty">
                <i class="bi bi-people"></i>
                <p>Tidak ada buyer yang ditemukan.</p></td></tr>`;
            pagination.style.display = 'none';
            return;
        }

        tbody.innerHTML = buyers.map(b => {
            const overduePill = b.invoice_overdue > 0
                ? `<span class="tb-overdue-chip"><i class="bi bi-exclamation-triangle-fill"></i>${b.invoice_overdue} overdue</span>`
                : '';

            return `<tr>
                <td class="is-mono">${b.customer_id}</td>
                <td>
                    <div class="company">${escHtml(b.nama_perusahaan)}</div>
                    <div class="pic">${escHtml(b.nama_pic)}</div>
                </td>
                <td>${escHtml(b.email)}</td>
                <td>${escHtml(b.no_whatsapp || '—')}</td>
                <td>${escHtml(b.negara || '—')}</td>
                <td>
                    <span style="font-size:13px;font-weight:600;color:#374151">${b.total_invoice}</span>
                    ${overduePill}
                </td>
                <td>${badgeHtml(b.status_verifikasi)}</td>
                <td style="white-space:nowrap;font-size:12.5px;color:#6b7280">${fmtDate(b.terdaftar_pada)}</td>
                <td>
                    <div class="tb-row-actions" style="justify-content:flex-end">
                        <button class="tb-btn-icon" title="Lihat detail"
                            onclick="openBuyerDetail(${b.id_buyer})">
                            <i class="bi bi-eye"></i>
                        </button>
                        ${b.status_verifikasi === 'pending' ? `
                        <button class="tb-btn-icon is-success" title="Setujui"
                            onclick="confirmVerify(${b.id_buyer},'approved','${escHtml(b.nama_perusahaan)}')">
                            <i class="bi bi-check-lg"></i>
                        </button>
                        <button class="tb-btn-icon is-danger" title="Tolak"
                            onclick="confirmVerify(${b.id_buyer},'rejected','${escHtml(b.nama_perusahaan)}')">
                            <i class="bi bi-x-lg"></i>
                        </button>` : ''}
                        ${b.status_verifikasi === 'approved' ? `
                        <button class="tb-btn-icon is-danger" title="Blokir buyer"
                            onclick="confirmBlock(${b.id_buyer},'${escHtml(b.nama_perusahaan)}')">
                            <i class="bi bi-slash-circle"></i>
                        </button>` : ''}
                    </div>
                </td>
            </tr>`;
        }).join('');

        // Pagination
        const total    = pag.total;
        const pages    = pag.total_pages;
        const from     = (pag.page - 1) * pag.limit + 1;
        const to       = Math.min(pag.page * pag.limit, total);
        pagInfo.textContent = `Menampilkan ${from}–${to} dari ${total} buyer`;

        pagBtns.innerHTML = '';
        const prevBtn = makePageBtn('‹', pag.page - 1, pag.page <= 1);
        pagBtns.appendChild(prevBtn);

        const range = pageRange(pag.page, pages);
        range.forEach(p => {
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
        if (!disabled) btn.addEventListener('click', () => { state.page = targetPage; loadBuyers(); });
        return btn;
    }

    function pageRange(cur, total) {
        if (total <= 7) return Array.from({ length: total }, (_, i) => i + 1);
        if (cur <= 4)   return [1,2,3,4,5,'…',total];
        if (cur >= total - 3) return [1,'…',total-4,total-3,total-2,total-1,total];
        return [1,'…',cur-1,cur,cur+1,'…',total];
    }

    function escHtml(str) {
        return String(str ?? '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
    }

    /* ── Modal Detail ──────────────────────────────────────── */
    window.openBuyerDetail = async function (idBuyer) {
        modalBody.innerHTML = `<div style="text-align:center;padding:40px 0;color:#9ca3af">
            <i class="bi bi-hourglass-split" style="font-size:28px"></i>
            <p style="margin-top:10px;font-size:14px">Memuat data…</p></div>`;
        modalFoot.innerHTML = `<button class="tb-btn tb-btn-ghost" id="btnCloseModal2">Tutup</button>`;
        document.getElementById('btnCloseModal2').addEventListener('click', closeModalBuyer);
        modalTitle.textContent = 'Detail Buyer';
        openModal(modalBuyer);

        try {
            const res  = await fetch(`${ADMIN_URL}/fetch-data/fetchBuyerDetail.php?id_buyer=${idBuyer}`, { credentials: 'same-origin' });
            const json = await res.json();
            if (json.status === 'error') throw new Error(json.message);
            renderBuyerDetail(json);
        } catch (err) {
            modalBody.innerHTML = `<div style="text-align:center;padding:32px;color:#b91c1c;font-size:14px">
                <i class="bi bi-exclamation-triangle-fill" style="font-size:28px;display:block;margin-bottom:8px"></i>
                ${escHtml(err.message || 'Gagal memuat detail buyer.')}
            </div>`;
        }
    };

    function renderBuyerDetail(d) {
        const b    = d.buyer;
        const ord  = d.order_stats;
        const inv  = d.invoice_stats;
        const rinv = d.recent_invoices || [];

        modalTitle.textContent = b.nama_perusahaan;

        modalBody.innerHTML = `
            <!-- Info utama -->
            <div class="tb-detail-grid">
                <div class="tb-detail-item"><label>Customer ID</label><span class="mono">${escHtml(b.customer_id)}</span></div>
                <div class="tb-detail-item"><label>Email</label><span>${escHtml(b.email)}</span></div>
                <div class="tb-detail-item"><label>PIC</label><span>${escHtml(b.nama_pic)}</span></div>
                <div class="tb-detail-item"><label>No. WhatsApp</label><span>${escHtml(b.no_whatsapp || '—')}</span></div>
                <div class="tb-detail-item"><label>Perusahaan</label><span>${escHtml(b.nama_perusahaan)}</span></div>
                <div class="tb-detail-item"><label>Negara</label><span>${escHtml(b.negara || '—')}</span></div>
                <div class="tb-detail-item"><label>Status Verifikasi</label><span>${badgeHtml(b.status_verifikasi)}</span></div>
                <div class="tb-detail-item"><label>Tenor</label><span>${b.tenor_hari ? b.tenor_hari + ' hari' : '—'}</span></div>
                <div class="tb-detail-item"><label>Terdaftar</label><span>${fmtDate(b.terdaftar_pada)}</span></div>
                ${b.tanggal_diblokir ? `<div class="tb-detail-item"><label>Tanggal Diblokir</label><span style="color:#b91c1c">${fmtDate(b.tanggal_diblokir)}</span></div>` : ''}
                ${b.diverifikasi_oleh_email ? `<div class="tb-detail-item"><label>Diverifikasi Oleh</label><span>${escHtml(b.diverifikasi_oleh_email)}</span></div>` : ''}
                ${b.alamat ? `<div class="tb-detail-item" style="grid-column:1/-1"><label>Alamat</label><span>${escHtml(b.alamat)}</span></div>` : ''}
            </div>

            <!-- Ringkasan Order -->
            <div class="tb-section-title">Ringkasan Order</div>
            <div class="tb-stat-mini">
                <div class="tb-stat-mini-item">
                    <div class="val">${ord.total_order ?? 0}</div><div class="lbl">Total Order</div>
                </div>
                <div class="tb-stat-mini-item">
                    <div class="val">${(+ord.pending + +ord.processing) || 0}</div><div class="lbl">Aktif</div>
                </div>
                <div class="tb-stat-mini-item">
                    <div class="val" style="font-size:14px">${formatRupiah(ord.total_nilai)}</div><div class="lbl">Total Nilai</div>
                </div>
            </div>

            <!-- Ringkasan Invoice -->
            <div class="tb-section-title">Ringkasan Invoice</div>
            <div class="tb-stat-mini" style="grid-template-columns:repeat(3,1fr)">
                <div class="tb-stat-mini-item">
                    <div class="val" style="color:#16a34a;font-size:13px">${formatRupiah(inv.total_paid)}</div><div class="lbl">Lunas</div>
                </div>
                <div class="tb-stat-mini-item">
                    <div class="val" style="color:#2563eb;font-size:13px">${formatRupiah(inv.total_outstanding)}</div><div class="lbl">Outstanding</div>
                </div>
                <div class="tb-stat-mini-item">
                    <div class="val" style="color:#dc2626;font-size:13px">${formatRupiah(inv.total_overdue)}</div><div class="lbl">Overdue</div>
                </div>
            </div>

            <!-- 5 Invoice Terbaru -->
            ${rinv.length > 0 ? `
            <div class="tb-section-title">5 Invoice Terbaru</div>
            <table class="tb-mini-table">
                <thead>
                    <tr>
                        <th>Invoice ID</th>
                        <th>Tgl Invoice</th>
                        <th>Jatuh Tempo</th>
                        <th>Total</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    ${rinv.map(inv => `<tr>
                        <td style="font-family:'DM Mono',monospace;font-size:12px;color:#4338ca">${escHtml(inv.invoice_id)}</td>
                        <td>${fmtDate(inv.invoice_date)}</td>
                        <td>${fmtDate(inv.due_date)}</td>
                        <td style="font-family:'DM Mono',monospace">${formatRupiah(inv.total_idr)}</td>
                        <td><span class="inv-badge ${inv.status}">${inv.status}</span></td>
                    </tr>`).join('')}
                </tbody>
            </table>` : ''}
        `;

        /* Tombol aksi di footer modal */
        const btns = [`<button class="tb-btn tb-btn-ghost" onclick="closeModalBuyer()">Tutup</button>`];
        if (b.status_verifikasi === 'pending') {
            btns.unshift(`<button class="tb-btn tb-btn-danger" onclick="closeModalBuyer();confirmVerify(${b.id_buyer},'rejected','${escHtml(b.nama_perusahaan)}')">Tolak</button>`);
            btns.unshift(`<button class="tb-btn tb-btn-success" onclick="closeModalBuyer();confirmVerify(${b.id_buyer},'approved','${escHtml(b.nama_perusahaan)}')">Setujui</button>`);
        }
        if (b.status_verifikasi === 'approved') {
            btns.unshift(`<button class="tb-btn tb-btn-danger" onclick="closeModalBuyer();confirmBlock(${b.id_buyer},'${escHtml(b.nama_perusahaan)}')">Blokir Buyer</button>`);
        }
        modalFoot.innerHTML = btns.join('');
    }

    window.closeModalBuyer = function () { closeModal(modalBuyer); };

    /* ── Modal Verifikasi ──────────────────────────────────── */
    window.confirmVerify = function (idBuyer, aksi, namaPerusahaan) {
        const isApprove = aksi === 'approved';
        confirmTitle.textContent    = isApprove ? 'Setujui Buyer' : 'Tolak Buyer';
        confirmMsg.textContent      = `Anda akan ${isApprove ? 'menyetujui' : 'menolak'} pendaftaran buyer "${namaPerusahaan}".`;
        confirmNote.textContent     = isApprove
            ? 'Buyer akan dapat mulai melakukan pemesanan setelah disetujui.'
            : 'Buyer akan mendapat status rejected dan tidak dapat login untuk memesan.';
        confirmError.classList.remove('show');

        btnDoConfirm.className      = `tb-btn ${isApprove ? 'tb-btn-success' : 'tb-btn-danger'}`;
        btnDoConfirm.textContent    = isApprove ? 'Ya, Setujui' : 'Ya, Tolak';

        btnDoConfirm.onclick = () => doVerify(idBuyer, aksi);
        openModal(modalConfirm);
    };

    async function doVerify(idBuyer, aksi) {
        setConfirmLoading(true);
        confirmError.classList.remove('show');
        try {
            const res  = await fetch(`${ADMIN_URL}/fetch-data/verifyBuyer.php`, {
                method     : 'POST',
                credentials: 'same-origin',
                headers    : { 'Content-Type': 'application/json' },
                body       : JSON.stringify({ id_buyer: idBuyer, aksi }),
            });
            const json = await res.json();
            if (json.status === 'error') throw new Error(json.message);
            closeModal(modalConfirm);
            loadBuyers();
        } catch (err) {
            confirmErrTxt.textContent = err.message || 'Gagal memproses verifikasi.';
            confirmError.classList.add('show');
        } finally {
            setConfirmLoading(false);
        }
    }

    /* ── Modal Blokir ──────────────────────────────────────── */
    window.confirmBlock = function (idBuyer, namaPerusahaan) {
        confirmTitle.textContent = 'Blokir Buyer';
        confirmMsg.textContent   = `Anda akan memblokir buyer "${namaPerusahaan}".`;
        confirmNote.textContent  = 'Buyer tidak akan dapat melakukan pemesanan sampai diaktifkan kembali secara manual.';
        confirmError.classList.remove('show');

        btnDoConfirm.className   = 'tb-btn tb-btn-danger';
        btnDoConfirm.textContent = 'Ya, Blokir';
        btnDoConfirm.onclick     = () => doBlock(idBuyer);
        openModal(modalConfirm);
    };

    async function doBlock(idBuyer) {
        setConfirmLoading(true);
        confirmError.classList.remove('show');
        try {
            const res  = await fetch(`${ADMIN_URL}/fetch-data/blockBuyer.php`, {
                method     : 'POST',
                credentials: 'same-origin',
                headers    : { 'Content-Type': 'application/json' },
                body       : JSON.stringify({ id_buyer: idBuyer }),
            });
            const json = await res.json();
            if (json.status === 'error') throw new Error(json.message);
            closeModal(modalConfirm);
            loadBuyers();
        } catch (err) {
            confirmErrTxt.textContent = err.message || 'Gagal memblokir buyer.';
            confirmError.classList.add('show');
        } finally {
            setConfirmLoading(false);
        }
    }

    function setConfirmLoading(loading) {
        btnDoConfirm.disabled       = loading;
        document.getElementById('btnCancelConfirm').disabled = loading;
        if (loading) {
            btnDoConfirm.innerHTML = '<i class="bi bi-hourglass-split"></i> Memproses…';
        }
    }

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
    btnCloseModal?.addEventListener('click', closeModalBuyer);
    [btnCancel, btnCloseConf].forEach(b => b?.addEventListener('click', () => closeModal(modalConfirm)));

    /* Close on overlay click */
    [modalBuyer, modalConfirm].forEach(m => {
        m.addEventListener('click', e => { if (e.target === m) closeModal(m); });
    });

    /* Status tabs */
    statusTabs.querySelectorAll('button').forEach(btn => {
        btn.addEventListener('click', () => {
            statusTabs.querySelectorAll('button').forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
            state.status = btn.dataset.status;
            state.page   = 1;
            loadBuyers();
        });
    });

    /* Search */
    searchInput.addEventListener('input', () => {
        clearTimeout(searchTimer);
        searchTimer = setTimeout(() => {
            state.search = searchInput.value.trim();
            state.page   = 1;
            loadBuyers();
        }, 380);
    });

    /* Refresh */
    btnRefresh.addEventListener('click', loadBuyers);

    /* ── Keyboard shortcuts ────────────────────────────────── */
    document.addEventListener('keydown', e => {
        if (e.key === 'Escape') {
            if (modalConfirm.classList.contains('open')) closeModal(modalConfirm);
            else if (modalBuyer.classList.contains('open')) closeModal(modalBuyer);
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
    loadBuyers();

})();
</script>

<?php
$extraJs = [];
include __DIR__ . '/partials/_footer.php';
?>