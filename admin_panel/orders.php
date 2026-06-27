<?php
// ============================================================
//  ThreadB2B — admin_panel/orders.php
//  Manajemen pesanan: list, filter, detail, update status.
// ============================================================
$pageTitle  = 'Manajemen Pesanan';
$activePage = 'orders';

require_once __DIR__ . '/partials/config.php';
?>
<?php include __DIR__ . '/partials/_header.php'; ?>

<style>
/* ---- Orders page styles ---- */
.tb-page-head{display:flex;align-items:center;justify-content:space-between;margin-bottom:22px;flex-wrap:wrap;gap:12px}
.tb-page-head h2{font-size:20px;font-weight:700;color:#1a1d29;margin:0}
.tb-page-head p{font-size:13.5px;color:#6b7280;margin:4px 0 0}

/* Filter bar */
.tb-filter-bar{display:flex;align-items:center;gap:10px;margin-bottom:18px;flex-wrap:wrap}
.tb-filter-bar input[type="search"],
.tb-filter-bar input[type="date"]{height:38px;padding:0 12px;border:1px solid #e0e1e6;border-radius:8px;
    font-size:13.5px;color:#1a1d29;outline:none;transition:.15s;background:#fff}
.tb-filter-bar input[type="search"]{flex:1;min-width:180px;max-width:260px}
.tb-filter-bar input[type="date"]{width:148px}
.tb-filter-bar input:focus{border-color:#4338ca;box-shadow:0 0 0 3px #eef2ff}
.tb-status-tabs{display:flex;gap:4px;background:#f3f4f6;border-radius:9px;padding:3px}
.tb-status-tabs button{height:30px;padding:0 12px;border:none;border-radius:7px;font-size:12.5px;
    font-weight:600;cursor:pointer;color:#6b7280;background:transparent;transition:.12s}
.tb-status-tabs button.active{background:#fff;color:#1a1d29;box-shadow:0 1px 3px rgba(0,0,0,.1)}
.tb-filter-btn{height:38px;padding:0 14px;border-radius:8px;border:1px solid #e0e1e6;font-size:13px;
    font-weight:600;cursor:pointer;background:#fff;color:#374151;display:flex;align-items:center;gap:6px;transition:.12s}
.tb-filter-btn:hover{border-color:#4338ca;color:#4338ca}
.tb-filter-sep{width:1px;height:28px;background:#e0e1e6;flex-shrink:0}

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
.tb-table td .sub{font-size:12px;color:#9ca3af;margin-top:2px}
.tb-table td .total-val{font-family:'DM Mono',monospace;font-size:13px;font-weight:700;color:#1a1d29}

/* Status badges */
.tb-badge{display:inline-flex;align-items:center;gap:5px;padding:3px 9px;border-radius:20px;
    font-size:11.5px;font-weight:700;letter-spacing:.02em;white-space:nowrap}
.tb-badge::before{content:'';width:6px;height:6px;border-radius:50%;display:inline-block}
.tb-badge.pending   {background:#fffbeb;color:#b45309}.tb-badge.pending::before{background:#d97706}
.tb-badge.processing{background:#eff6ff;color:#1d4ed8}.tb-badge.processing::before{background:#3b82f6}
.tb-badge.shipped   {background:#f0fdf4;color:#166534}.tb-badge.shipped::before{background:#22c55e}
.tb-badge.done      {background:#ecfdf5;color:#047857}.tb-badge.done::before{background:#16a34a}
.tb-badge.cancelled {background:#f3f4f6;color:#6b7280}.tb-badge.cancelled::before{background:#9ca3af}

/* Action buttons */
.tb-row-actions{display:flex;gap:6px;align-items:center;justify-content:flex-end}
.tb-btn-icon{width:30px;height:30px;border-radius:7px;border:1px solid #e0e1e6;background:#fff;
    cursor:pointer;display:flex;align-items:center;justify-content:center;font-size:14px;
    color:#6b7280;transition:.12s;flex-shrink:0}
.tb-btn-icon:hover{border-color:#4338ca;color:#4338ca;background:#eef2ff}

/* Pagination */
.tb-pagination{display:flex;align-items:center;justify-content:space-between;padding:14px 18px;
    border-top:1px solid #f1f2f5;font-size:13px;color:#6b7280;flex-wrap:wrap;gap:10px}
.tb-pag-btns{display:flex;gap:4px}
.tb-pag-btns button{height:32px;min-width:32px;padding:0 10px;border:1px solid #e0e1e6;border-radius:7px;
    background:#fff;font-size:12.5px;font-weight:600;color:#374151;cursor:pointer;transition:.12s}
.tb-pag-btns button:hover:not(:disabled){border-color:#4338ca;color:#4338ca}
.tb-pag-btns button.active{background:#4338ca;border-color:#4338ca;color:#fff}
.tb-pag-btns button:disabled{opacity:.4;cursor:default}

/* Empty / loading */
.tb-table-empty{text-align:center;padding:48px 20px;color:#9ca3af}
.tb-table-empty i{font-size:32px;display:block;margin-bottom:10px;opacity:.5}
.tb-table-empty p{margin:0;font-size:14px}
.tb-skeleton{height:13px;border-radius:5px;background:linear-gradient(90deg,#eee 25%,#f5f5f5 37%,#eee 63%);
    background-size:400% 100%;animation:tbShimmer 1.4s ease infinite}
@keyframes tbShimmer{0%{background-position:100% 50%}100%{background-position:0 50%}}

/* ---- Modal ---- */
.tb-modal-overlay{position:fixed;inset:0;background:rgba(17,24,39,.45);z-index:1000;
    display:none;align-items:center;justify-content:center;padding:20px;backdrop-filter:blur(2px)}
.tb-modal-overlay.open{display:flex}
.tb-modal{background:#fff;border-radius:16px;width:100%;max-width:700px;max-height:92vh;
    overflow:hidden;display:flex;flex-direction:column;box-shadow:0 20px 60px rgba(0,0,0,.18)}
.tb-modal.sm{max-width:440px}
.tb-modal__head{padding:20px 24px 16px;border-bottom:1px solid #f1f2f5;display:flex;
    align-items:center;justify-content:space-between;flex-shrink:0}
.tb-modal__head h3{font-size:16px;font-weight:700;color:#1a1d29;margin:0}
.tb-modal__close{width:30px;height:30px;border-radius:8px;border:1px solid #e0e1e6;background:transparent;
    cursor:pointer;font-size:16px;color:#6b7280;display:flex;align-items:center;justify-content:center}
.tb-modal__close:hover{background:#f3f4f6}
.tb-modal__body{padding:20px 24px;overflow-y:auto;flex:1}
.tb-modal__foot{padding:14px 24px;border-top:1px solid #f1f2f5;display:flex;gap:8px;
    justify-content:flex-end;flex-shrink:0;flex-wrap:wrap}

/* Detail layout */
.tb-detail-grid{display:grid;grid-template-columns:1fr 1fr;gap:14px;margin-bottom:16px}
@media(max-width:520px){.tb-detail-grid{grid-template-columns:1fr}}
.tb-detail-item label{display:block;font-size:11px;font-weight:700;text-transform:uppercase;
    letter-spacing:.06em;color:#9ca3af;margin-bottom:4px}
.tb-detail-item span{font-size:13.5px;color:#1a1d29;font-weight:500;word-break:break-word}
.tb-detail-item span.mono{font-family:'DM Mono',monospace;color:#4338ca}
.tb-detail-item.full{grid-column:1/-1}

.tb-section-title{font-size:11.5px;font-weight:700;text-transform:uppercase;letter-spacing:.05em;
    color:#9ca3af;margin:18px 0 10px;border-top:1px solid #f1f2f5;padding-top:14px}

/* Tracking timeline */
.tb-timeline{position:relative;padding-left:22px}
.tb-timeline::before{content:'';position:absolute;left:7px;top:8px;bottom:8px;width:2px;background:#e9eaee}
.tb-tl-item{position:relative;margin-bottom:16px}
.tb-tl-item:last-child{margin-bottom:0}
.tb-tl-dot{position:absolute;left:-19px;top:4px;width:12px;height:12px;border-radius:50%;
    border:2px solid #e9eaee;background:#fff;transition:.15s}
.tb-tl-item.done .tb-tl-dot{background:#16a34a;border-color:#16a34a}
.tb-tl-item.active .tb-tl-dot{background:#3b82f6;border-color:#3b82f6;box-shadow:0 0 0 3px #dbeafe}
.tb-tl-status{font-size:13px;font-weight:700;color:#1a1d29;text-transform:capitalize}
.tb-tl-note{font-size:12.5px;color:#6b7280;margin-top:2px}
.tb-tl-time{font-size:11.5px;color:#9ca3af;margin-top:2px}

/* Return card */
.tb-return-card{background:#fef2f2;border:1px solid #fecaca;border-radius:10px;padding:14px 16px;margin-bottom:10px}
.tb-return-card__head{display:flex;align-items:center;justify-content:space-between;margin-bottom:8px}
.tb-return-card__no{font-size:12.5px;font-weight:700;font-family:'DM Mono',monospace;color:#b91c1c}
.tb-return-card__reason{font-size:13px;color:#374151;margin-bottom:4px}
.tb-return-card__resp{font-size:12.5px;color:#6b7280;font-style:italic}

/* Resi card */
.tb-resi-row{display:grid;grid-template-columns:1fr 1fr;gap:8px;background:#f8f9fb;
    border-radius:9px;padding:12px 14px;margin-bottom:8px;font-size:13px}
.tb-resi-row:last-child{margin-bottom:0}
.tb-resi-row .resi-no{font-family:'DM Mono',monospace;font-weight:700;color:#4338ca;
    grid-column:1/-1;font-size:13.5px;margin-bottom:4px}
.tb-resi-row span{color:#6b7280}
.tb-resi-row strong{color:#1a1d29}

/* Update status form */
.tb-form-group{margin-bottom:16px}
.tb-form-group label{display:block;font-size:12.5px;font-weight:600;color:#374151;margin-bottom:6px}
.tb-form-group select,
.tb-form-group input[type="text"],
.tb-form-group textarea{width:100%;height:38px;padding:0 12px;border:1px solid #e0e1e6;border-radius:8px;
    font-size:13.5px;color:#1a1d29;outline:none;transition:.15s;font-family:inherit;background:#fff}
.tb-form-group textarea{height:auto;padding:10px 12px;resize:vertical;min-height:72px}
.tb-form-group select:focus,
.tb-form-group input:focus,
.tb-form-group textarea:focus{border-color:#4338ca;box-shadow:0 0 0 3px #eef2ff}
.tb-form-group .hint{font-size:12px;color:#9ca3af;margin-top:5px}

/* Buttons */
.tb-btn{height:36px;padding:0 16px;border-radius:8px;font-size:13px;font-weight:600;
    cursor:pointer;border:1px solid transparent;transition:.12s;display:inline-flex;align-items:center;gap:6px}
.tb-btn-primary{background:#4338ca;color:#fff;border-color:#4338ca}
.tb-btn-primary:hover{background:#3730a3}
.tb-btn-ghost{background:#fff;color:#374151;border-color:#e0e1e6}
.tb-btn-ghost:hover{border-color:#9ca3af}
.tb-btn-warning{background:#d97706;color:#fff;border-color:#d97706}
.tb-btn-warning:hover{background:#b45309}
.tb-btn:disabled{opacity:.5;cursor:default}

/* Alert */
.tb-inline-alert{display:none;align-items:center;gap:8px;padding:10px 14px;border-radius:8px;
    font-size:13px;margin-bottom:14px}
.tb-inline-alert.show{display:flex}
.tb-inline-alert.error{background:#fef2f2;color:#991b1b;border:1px solid #fecaca}
.tb-inline-alert.success{background:#ecfdf5;color:#065f46;border:1px solid #a7f3d0}

/* Resi badge inline */
.resi-badge{display:inline-block;background:#eff6ff;color:#1e40af;border-radius:6px;
    padding:2px 8px;font-family:'DM Mono',monospace;font-size:11.5px;font-weight:700}
</style>

<div class="tb-layout">
    <?php include __DIR__ . '/partials/_sidebar.php'; ?>

    <main class="tb-main">
        <?php include __DIR__ . '/partials/_navbar.php'; ?>

        <div style="padding:24px;">

            <div class="tb-page-head">
                <div>
                    <h2>Manajemen Pesanan</h2>
                    <p>Pantau semua pesanan masuk, update status, dan lacak pengiriman.</p>
                </div>
            </div>

            <!-- Alert global -->
            <div class="tb-inline-alert error" id="orderGlobalError">
                <i class="bi bi-exclamation-triangle-fill"></i>
                <span id="orderGlobalErrorText">Terjadi kesalahan.</span>
            </div>

            <!-- Filter bar -->
            <div class="tb-filter-bar">
                <input type="search" id="orderSearch" placeholder="Cari no. order / jenis benang…" autocomplete="off">
                <div class="tb-filter-sep"></div>
                <input type="date" id="filterDari" title="Dari tanggal">
                <input type="date" id="filterSampai" title="Sampai tanggal">
                <div class="tb-filter-sep"></div>
                <div class="tb-status-tabs" id="statusTabs">
                    <button class="active" data-status="all">Semua</button>
                    <button data-status="pending">Pending</button>
                    <button data-status="processing">Processing</button>
                    <button data-status="shipped">Shipped</button>
                    <button data-status="done">Done</button>
                    <button data-status="cancelled">Cancelled</button>
                </div>
                <button class="tb-filter-btn" id="btnRefreshOrders">
                    <i class="bi bi-arrow-clockwise"></i> Refresh
                </button>
            </div>

            <!-- Table -->
            <div class="tb-table-wrap">
                <table class="tb-table">
                    <thead>
                        <tr>
                            <th>No. Order</th>
                            <th>Buyer</th>
                            <th>Produk</th>
                            <th>Qty</th>
                            <th>Total Nilai</th>
                            <th>Tracking</th>
                            <th>Status</th>
                            <th>Tanggal</th>
                            <th style="text-align:right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="orderTableBody">
                        <?php for ($i = 0; $i < 7; $i++): ?>
                        <tr>
                            <?php foreach ([80,130,110,50,90,80,70,70,60] as $w): ?>
                            <td><div class="tb-skeleton" style="width:<?= $w ?>px"></div></td>
                            <?php endforeach; ?>
                        </tr>
                        <?php endfor; ?>
                    </tbody>
                </table>
                <div class="tb-pagination" id="orderPagination" style="display:none">
                    <span id="orderPagInfo">Menampilkan data…</span>
                    <div class="tb-pag-btns" id="orderPagBtns"></div>
                </div>
            </div>

        </div>
    </main>
</div>

<!-- ============================================================
     MODAL — Detail Order
     ============================================================ -->
<div class="tb-modal-overlay" id="modalOrder" role="dialog" aria-modal="true" aria-labelledby="modalOrderTitle">
    <div class="tb-modal">
        <div class="tb-modal__head">
            <h3 id="modalOrderTitle">Detail Pesanan</h3>
            <button class="tb-modal__close" id="btnCloseOrder"><i class="bi bi-x"></i></button>
        </div>
        <div class="tb-modal__body" id="modalOrderBody">
            <div style="text-align:center;padding:40px 0;color:#9ca3af">
                <i class="bi bi-hourglass-split" style="font-size:28px"></i>
                <p style="margin-top:10px;font-size:14px">Memuat data…</p>
            </div>
        </div>
        <div class="tb-modal__foot" id="modalOrderFoot">
            <button class="tb-btn tb-btn-ghost" onclick="closeOrderModal()">Tutup</button>
        </div>
    </div>
</div>

<!-- ============================================================
     MODAL — Update Status
     ============================================================ -->
<div class="tb-modal-overlay" id="modalStatus" role="dialog" aria-modal="true">
    <div class="tb-modal sm">
        <div class="tb-modal__head">
            <h3>Update Status Pesanan</h3>
            <button class="tb-modal__close" id="btnCloseStatus"><i class="bi bi-x"></i></button>
        </div>
        <div class="tb-modal__body">
            <div class="tb-inline-alert error" id="statusError">
                <i class="bi bi-exclamation-triangle-fill"></i>
                <span id="statusErrorText"></span>
            </div>
            <p id="statusTargetLabel" style="font-size:13px;color:#6b7280;margin:0 0 16px"></p>

            <div class="tb-form-group">
                <label>Status Baru</label>
                <select id="statusSelect">
                    <option value="pending">Pending</option>
                    <option value="processing">Processing</option>
                    <option value="shipped">Shipped</option>
                    <option value="done">Done</option>
                    <option value="cancelled">Cancelled</option>
                </select>
            </div>

            <div class="tb-form-group" id="resiGroup" style="display:none">
                <label>Nomor Resi <span style="color:#9ca3af;font-weight:400">(opsional)</span></label>
                <input type="text" id="resiInput" placeholder="Contoh: JNE-123456789">
                <div class="hint">Isi jika status Shipped dan ada nomor resi pengiriman.</div>
            </div>

            <div class="tb-form-group">
                <label>Keterangan <span style="color:#9ca3af;font-weight:400">(opsional)</span></label>
                <textarea id="keteranganInput" placeholder="Catatan untuk tracking…"></textarea>
            </div>
        </div>
        <div class="tb-modal__foot">
            <button class="tb-btn tb-btn-ghost" id="btnCancelStatus">Batal</button>
            <button class="tb-btn tb-btn-primary" id="btnDoStatus">
                <i class="bi bi-check-lg"></i> Simpan Status
            </button>
        </div>
    </div>
</div>


<script>
(function () {
    'use strict';
    const ADMIN_URL = '<?= ADMIN_URL ?>';

    /* ── State ─────────────────────────────────────────────── */
    let state = { status: 'all', search: '', dari: '', sampai: '', page: 1, limit: 20 };
    let pendingUpdate = null; // { id_order, currentStatus }

    /* ── DOM refs ──────────────────────────────────────────── */
    const tbody          = document.getElementById('orderTableBody');
    const pagination     = document.getElementById('orderPagination');
    const pagInfo        = document.getElementById('orderPagInfo');
    const pagBtns        = document.getElementById('orderPagBtns');
    const searchInput    = document.getElementById('orderSearch');
    const filterDari     = document.getElementById('filterDari');
    const filterSampai   = document.getElementById('filterSampai');
    const statusTabs     = document.getElementById('statusTabs');
    const btnRefresh     = document.getElementById('btnRefreshOrders');
    const globalError    = document.getElementById('orderGlobalError');
    const globalErrText  = document.getElementById('orderGlobalErrorText');

    const modalOrder     = document.getElementById('modalOrder');
    const modalOrderBody = document.getElementById('modalOrderBody');
    const modalOrderFoot = document.getElementById('modalOrderFoot');
    const modalOrderTitle= document.getElementById('modalOrderTitle');

    const modalStatus    = document.getElementById('modalStatus');
    const statusError    = document.getElementById('statusError');
    const statusErrTxt   = document.getElementById('statusErrorText');
    const statusLabel    = document.getElementById('statusTargetLabel');
    const statusSelect   = document.getElementById('statusSelect');
    const resiGroup      = document.getElementById('resiGroup');
    const resiInput      = document.getElementById('resiInput');
    const keteranganInput= document.getElementById('keteranganInput');
    const btnDoStatus    = document.getElementById('btnDoStatus');

    /* ── Helpers ───────────────────────────────────────────── */
    const escHtml = s => String(s ?? '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');

    function formatRupiah(n) {
        return 'Rp ' + Number(n || 0).toLocaleString('id-ID', { maximumFractionDigits: 0 });
    }
    function fmtDate(str) {
        if (!str) return '—';
        return new Date(str).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' });
    }
    function fmtDateTime(str) {
        if (!str) return '—';
        return new Date(str).toLocaleString('id-ID', { day: 'numeric', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' });
    }

    const STATUS_LABEL = {
        pending: 'Pending', processing: 'Processing',
        shipped: 'Shipped', done: 'Done', cancelled: 'Cancelled',
    };
    const TRACKING_STATUS_LABEL = {
        pending: 'Pesanan Masuk', processing: 'Sedang Diproses',
        shipped: 'Dikirim', done: 'Selesai', cancelled: 'Dibatalkan',
    };

    function badgeHtml(status) {
        return `<span class="tb-badge ${status}">${STATUS_LABEL[status] || status}</span>`;
    }

    function showGlobalError(msg) {
        globalErrText.textContent = msg;
        globalError.classList.add('show');
    }
    function hideGlobalError() { globalError.classList.remove('show'); }

    /* ── Skeleton ──────────────────────────────────────────── */
    function renderSkeleton() {
        const widths = [80,130,110,50,90,80,70,70,60];
        tbody.innerHTML = Array.from({ length: 7 }, () =>
            `<tr>${widths.map(w => `<td><div class="tb-skeleton" style="width:${w}px"></div></td>`).join('')}</tr>`
        ).join('');
        pagination.style.display = 'none';
    }

    /* ── Load orders ───────────────────────────────────────── */
    let searchTimer;
    async function loadOrders() {
        hideGlobalError();
        renderSkeleton();

        const params = new URLSearchParams({
            status : state.status,
            search : state.search,
            dari   : state.dari,
            sampai : state.sampai,
            page   : state.page,
            limit  : state.limit,
        });

        try {
            const res = await fetch(`${ADMIN_URL}/fetch-data/fetchOrders.php?${params}`, { credentials: 'same-origin' });
            const ct  = res.headers.get('content-type') || '';
            if (!ct.includes('application/json')) throw new Error('Sesi habis atau error server. Silakan refresh halaman.');
            const json = await res.json();
            if (json.status === 'error') throw new Error(json.message);
            renderOrders(json.orders, json.pagination);
        } catch (err) {
            tbody.innerHTML = `<tr><td colspan="9" class="tb-table-empty">
                <i class="bi bi-exclamation-triangle"></i>
                <p>${escHtml(err.message || 'Gagal memuat data pesanan.')}</p></td></tr>`;
            showGlobalError(err.message || 'Gagal memuat data.');
            pagination.style.display = 'none';
        }
    }

    function renderOrders(orders, pag) {
        if (!orders || orders.length === 0) {
            tbody.innerHTML = `<tr><td colspan="9" class="tb-table-empty">
                <i class="bi bi-bag-x"></i>
                <p>Tidak ada pesanan yang ditemukan.</p></td></tr>`;
            pagination.style.display = 'none';
            return;
        }

        tbody.innerHTML = orders.map(o => {
            const trackingPill = o.tracking_terakhir
                ? `<span style="font-size:11.5px;color:#6b7280;background:#f3f4f6;padding:2px 7px;border-radius:5px">${escHtml(o.tracking_terakhir)}</span>`
                : '<span style="color:#d1d5db;font-size:12px">—</span>';

            const canUpdate = !['done', 'cancelled'].includes(o.status);

            return `<tr>
                <td class="is-mono">${escHtml(o.no_order)}</td>
                <td>
                    <div class="company">${escHtml(o.nama_perusahaan)}</div>
                    <div class="sub">${escHtml(o.nama_pic)}</div>
                </td>
                <td>
                    <div style="font-weight:500;color:#1a1d29">${escHtml(o.jenis_benang)}</div>
                    <div class="sub">${escHtml(o.ukuran_benang || '')}${o.nama_warna ? ' · ' + escHtml(o.nama_warna) : ''}</div>
                </td>
                <td style="font-weight:600">${Number(o.qty).toLocaleString('id-ID')} kg</td>
                <td><span class="total-val">${formatRupiah(o.total_nilai)}</span></td>
                <td>${trackingPill}</td>
                <td>${badgeHtml(o.status)}</td>
                <td style="white-space:nowrap;font-size:12.5px;color:#6b7280">${fmtDate(o.tanggal)}</td>
                <td>
                    <div class="tb-row-actions">
                        <button class="tb-btn-icon" title="Lihat detail"
                            onclick="openOrderDetail(${o.id_order})">
                            <i class="bi bi-eye"></i>
                        </button>
                        ${canUpdate ? `
                        <button class="tb-btn-icon" title="Update status"
                            onclick="openStatusModal(${o.id_order},'${escHtml(o.no_order)}','${o.status}')">
                            <i class="bi bi-pencil-square"></i>
                        </button>` : ''}
                    </div>
                </td>
            </tr>`;
        }).join('');

        /* Pagination */
        const from = (pag.page - 1) * pag.limit + 1;
        const to   = Math.min(pag.page * pag.limit, pag.total);
        pagInfo.textContent = `Menampilkan ${from}–${to} dari ${pag.total} pesanan`;

        pagBtns.innerHTML = '';
        pagBtns.appendChild(makePageBtn('‹', pag.page - 1, pag.page <= 1));
        pageRange(pag.page, pag.total_pages).forEach(p => {
            if (p === '…') {
                const el = document.createElement('button');
                el.textContent = '…'; el.disabled = true;
                pagBtns.appendChild(el);
            } else {
                pagBtns.appendChild(makePageBtn(p, p, false, p === pag.page));
            }
        });
        pagBtns.appendChild(makePageBtn('›', pag.page + 1, pag.page >= pag.total_pages));
        pagination.style.display = 'flex';
    }

    function makePageBtn(label, targetPage, disabled, active = false) {
        const btn = document.createElement('button');
        btn.textContent = label; btn.disabled = disabled;
        if (active) btn.classList.add('active');
        if (!disabled) btn.addEventListener('click', () => { state.page = targetPage; loadOrders(); });
        return btn;
    }

    function pageRange(cur, total) {
        if (total <= 7) return Array.from({ length: total }, (_, i) => i + 1);
        if (cur <= 4)   return [1,2,3,4,5,'…',total];
        if (cur >= total - 3) return [1,'…',total-4,total-3,total-2,total-1,total];
        return [1,'…',cur-1,cur,cur+1,'…',total];
    }

    /* ── Modal Detail ──────────────────────────────────────── */
    window.openOrderDetail = async function (idOrder) {
        modalOrderBody.innerHTML = `<div style="text-align:center;padding:40px 0;color:#9ca3af">
            <i class="bi bi-hourglass-split" style="font-size:28px"></i>
            <p style="margin-top:10px;font-size:14px">Memuat data…</p></div>`;
        modalOrderFoot.innerHTML = `<button class="tb-btn tb-btn-ghost" onclick="closeOrderModal()">Tutup</button>`;
        modalOrderTitle.textContent = 'Detail Pesanan';
        openModal(modalOrder);

        try {
            const res  = await fetch(`${ADMIN_URL}/fetch-data/fetchOrderDetail.php?id_order=${idOrder}`, { credentials: 'same-origin' });
            const ct   = res.headers.get('content-type') || '';
            if (!ct.includes('application/json')) throw new Error('Sesi habis atau error server.');
            const json = await res.json();
            if (json.status === 'error') throw new Error(json.message);
            renderOrderDetail(json.order, json.tracking, json.returns, json.resi);
        } catch (err) {
            modalOrderBody.innerHTML = `<div style="text-align:center;padding:32px;color:#b91c1c;font-size:14px">
                <i class="bi bi-exclamation-triangle-fill" style="font-size:28px;display:block;margin-bottom:8px"></i>
                ${escHtml(err.message || 'Gagal memuat detail pesanan.')}
            </div>`;
        }
    };

    function renderOrderDetail(o, tracking, returns, resiList) {
        modalOrderTitle.textContent = `Pesanan ${o.no_order}`;

        /* ── Info Pesanan ── */
        let html = `
            <div class="tb-detail-grid">
                <div class="tb-detail-item"><label>No. Order</label><span class="mono">${escHtml(o.no_order)}</span></div>
                <div class="tb-detail-item"><label>Tanggal</label><span>${fmtDate(o.tanggal)}</span></div>
                <div class="tb-detail-item"><label>Buyer</label><span>${escHtml(o.nama_perusahaan)}</span></div>
                <div class="tb-detail-item"><label>PIC</label><span>${escHtml(o.nama_pic)}</span></div>
                <div class="tb-detail-item"><label>Customer ID</label><span class="mono">${escHtml(o.customer_id)}</span></div>
                <div class="tb-detail-item"><label>No. WhatsApp</label><span>${escHtml(o.no_whatsapp || '—')}</span></div>
                <div class="tb-detail-item"><label>Status</label><span>${badgeHtml(o.status)}</span></div>
                <div class="tb-detail-item"><label>Tenor</label><span>${o.tenor_hari ? o.tenor_hari + ' hari' : '—'}</span></div>
            </div>

            <div class="tb-section-title">Detail Produk</div>
            <div class="tb-detail-grid">
                <div class="tb-detail-item"><label>Jenis Benang</label><span>${escHtml(o.jenis_benang)}</span></div>
                <div class="tb-detail-item"><label>Ukuran</label><span>${escHtml(o.ukuran_benang || '—')}</span></div>
                <div class="tb-detail-item"><label>Kode Warna</label><span class="mono">${escHtml(o.kode_warna || '—')}</span></div>
                <div class="tb-detail-item"><label>Nama Warna</label><span>${escHtml(o.nama_warna || '—')}</span></div>
                <div class="tb-detail-item"><label>Qty</label><span>${Number(o.qty).toLocaleString('id-ID')} kg</span></div>
                <div class="tb-detail-item"><label>Harga / kg</label><span>${formatRupiah(o.harga_benang)}</span></div>
                <div class="tb-detail-item full" style="background:#f8f9fb;border-radius:9px;padding:12px 14px">
                    <label>Total Nilai</label>
                    <span style="font-size:18px;font-family:'DM Mono',monospace;font-weight:700;color:#1a1d29">
                        ${formatRupiah(o.qty * o.harga_benang)}
                    </span>
                </div>
                ${o.catatan ? `<div class="tb-detail-item full"><label>Catatan</label><span>${escHtml(o.catatan)}</span></div>` : ''}
            </div>`;

        /* ── Tracking Timeline ── */
        html += `<div class="tb-section-title">Riwayat Tracking</div>`;
        if (!tracking || tracking.length === 0) {
            html += `<p style="font-size:13px;color:#9ca3af;margin:0">Belum ada data tracking.</p>`;
        } else {
            const lastIdx = tracking.length - 1;
            html += `<div class="tb-timeline">`;
            tracking.forEach((t, i) => {
                const cls = i === lastIdx ? 'active' : 'done';
                html += `<div class="tb-tl-item ${cls}">
                    <div class="tb-tl-dot"></div>
                    <div class="tb-tl-status">${escHtml(TRACKING_STATUS_LABEL[t.status] || t.status)}</div>
                    ${t.keterangan ? `<div class="tb-tl-note">${escHtml(t.keterangan)}</div>` : ''}
                    <div class="tb-tl-time">${fmtDateTime(t.tanggal)} · ${escHtml(t.diupdate_oleh || 'sistem')}</div>
                </div>`;
            });
            html += `</div>`;
        }

        /* ── Resi Pengiriman ── */
        if (resiList && resiList.length > 0) {
            html += `<div class="tb-section-title">Resi Pengiriman</div>`;
            resiList.forEach(r => {
                html += `<div class="tb-resi-row">
                    <div class="resi-no"><i class="bi bi-truck"></i> ${escHtml(r.resi_no)}</div>
                    <div><span>Kurir</span><br><strong>${escHtml(r.kurir || '—')}</strong></div>
                    <div><span>Layanan</span><br><strong>${escHtml(r.service_type || '—')}</strong></div>
                    <div><span>Koli</span><br><strong>${r.koli || '—'}</strong></div>
                    <div><span>Berat</span><br><strong>${r.berat_kg ? r.berat_kg + ' kg' : '—'}</strong></div>
                    <div><span>Ongkir</span><br><strong>${formatRupiah(r.charge_idr)}</strong></div>
                </div>`;
            });
        }

        /* ── Retur ── */
        if (returns && returns.length > 0) {
            html += `<div class="tb-section-title">Retur Terkait</div>`;
            returns.forEach(ret => {
                const retStatusBadge = {
                    pending : '<span style="color:#b45309">Pending</span>',
                    approved: '<span style="color:#047857">Disetujui</span>',
                    rejected: '<span style="color:#b91c1c">Ditolak</span>',
                    revision: '<span style="color:#1d4ed8">Revisi</span>',
                }[ret.status] || ret.status;

                html += `<div class="tb-return-card">
                    <div class="tb-return-card__head">
                        <span class="tb-return-card__no">${escHtml(ret.no_return)}</span>
                        ${retStatusBadge}
                    </div>
                    <div class="tb-return-card__reason"><strong>${escHtml(ret.alasan_kategori || '—')}</strong> — ${escHtml(ret.alasan || '')}</div>
                    ${ret.respons_admin ? `<div class="tb-return-card__resp">Respons admin: ${escHtml(ret.respons_admin)}</div>` : ''}
                </div>`;
            });
        }

        modalOrderBody.innerHTML = html;

        /* Tombol footer */
        const canUpdate = !['done', 'cancelled'].includes(o.status);
        let footHtml = `<button class="tb-btn tb-btn-ghost" onclick="closeOrderModal()">Tutup</button>`;
        if (canUpdate) {
            footHtml = `<button class="tb-btn tb-btn-warning" onclick="closeOrderModal();openStatusModal(${o.id_order},'${escHtml(o.no_order)}','${o.status}')">
                <i class="bi bi-pencil-square"></i> Update Status
            </button>` + footHtml;
        }
        modalOrderFoot.innerHTML = footHtml;
    }

    window.closeOrderModal = () => closeModal(modalOrder);

    /* ── Modal Update Status ───────────────────────────────── */
    window.openStatusModal = function (idOrder, noOrder, currentStatus) {
        pendingUpdate = { idOrder, noOrder };
        statusLabel.textContent = `Pesanan: ${noOrder}`;
        statusError.classList.remove('show');
        resiInput.value = '';
        keteranganInput.value = '';

        /* Populate select, exclude current & done/cancelled dari pilihan regresif */
        const flowOrder = ['pending','processing','shipped','done','cancelled'];
        const curIdx    = flowOrder.indexOf(currentStatus);
        Array.from(statusSelect.options).forEach(opt => {
            const optIdx = flowOrder.indexOf(opt.value);
            /* Izinkan: ke depan dalam flow, atau cancelled kapan saja */
            opt.disabled = (optIdx < curIdx && opt.value !== 'cancelled') || opt.value === currentStatus;
        });
        statusSelect.value = flowOrder[Math.min(curIdx + 1, flowOrder.length - 1)];
        toggleResiField();

        openModal(modalStatus);
    };

    function toggleResiField() {
        resiGroup.style.display = statusSelect.value === 'shipped' ? '' : 'none';
    }
    statusSelect.addEventListener('change', toggleResiField);

    btnDoStatus.addEventListener('click', async () => {
        if (!pendingUpdate) return;
        statusError.classList.remove('show');
        btnDoStatus.disabled = true;
        btnDoStatus.innerHTML = '<i class="bi bi-hourglass-split"></i> Menyimpan…';

        try {
            const body = {
                id_order  : pendingUpdate.idOrder,
                status    : statusSelect.value,
                keterangan: keteranganInput.value.trim(),
                resi_no   : resiInput.value.trim(),
            };
            const res  = await fetch(`${ADMIN_URL}/fetch-data/updateOrderStatus.php`, {
                method: 'POST', credentials: 'same-origin',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(body),
            });
            const ct   = res.headers.get('content-type') || '';
            if (!ct.includes('application/json')) throw new Error('Sesi habis atau error server.');
            const json = await res.json();
            if (json.status === 'error') throw new Error(json.message);
            closeModal(modalStatus);
            loadOrders();
        } catch (err) {
            statusErrTxt.textContent = err.message || 'Gagal update status.';
            statusError.classList.add('show');
        } finally {
            btnDoStatus.disabled = false;
            btnDoStatus.innerHTML = '<i class="bi bi-check-lg"></i> Simpan Status';
        }
    });

    /* ── Modal helpers ─────────────────────────────────────── */
    function openModal(el)  { el.classList.add('open'); document.body.style.overflow = 'hidden'; }
    function closeModal(el) { el.classList.remove('open'); document.body.style.overflow = ''; }

    document.getElementById('btnCloseOrder').addEventListener('click', () => closeModal(modalOrder));
    document.getElementById('btnCloseStatus').addEventListener('click', () => closeModal(modalStatus));
    document.getElementById('btnCancelStatus').addEventListener('click', () => closeModal(modalStatus));
    [modalOrder, modalStatus].forEach(m => m.addEventListener('click', e => { if (e.target === m) closeModal(m); }));

    /* ── Event listeners ───────────────────────────────────── */
    statusTabs.querySelectorAll('button').forEach(btn => {
        btn.addEventListener('click', () => {
            statusTabs.querySelectorAll('button').forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
            state.status = btn.dataset.status;
            state.page   = 1;
            loadOrders();
        });
    });

    searchInput.addEventListener('input', () => {
        clearTimeout(searchTimer);
        searchTimer = setTimeout(() => {
            state.search = searchInput.value.trim();
            state.page   = 1;
            loadOrders();
        }, 380);
    });

    filterDari.addEventListener('change', () => { state.dari = filterDari.value; state.page = 1; loadOrders(); });
    filterSampai.addEventListener('change', () => { state.sampai = filterSampai.value; state.page = 1; loadOrders(); });
    btnRefresh.addEventListener('click', loadOrders);

    document.addEventListener('keydown', e => {
        if (e.key !== 'Escape') return;
        if (modalStatus.classList.contains('open')) closeModal(modalStatus);
        else if (modalOrder.classList.contains('open'))  closeModal(modalOrder);
    });

    /* Prefill status dari URL ?status= */
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
    loadOrders();

})();
</script>

<?php
$extraJs = [];
include __DIR__ . '/partials/_footer.php';
?>