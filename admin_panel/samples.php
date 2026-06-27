<?php
// ============================================================
//  admin_panel/samples.php
//  Manajemen Permintaan Sampel — Panel Admin ThreadB2B.
// ============================================================
$pageTitle  = 'Permintaan Sampel';
$activePage = 'samples';

require_once __DIR__ . '/partials/config.php';
?>
<?php include __DIR__ . '/partials/_header.php'; ?>

<style>
/* ---- Samples page styles ---- */
.tb-page-head{display:flex;align-items:center;justify-content:space-between;margin-bottom:22px;flex-wrap:wrap;gap:12px}
.tb-page-head h2{font-size:20px;font-weight:700;color:#1a1d29;margin:0}
.tb-page-head p{font-size:13.5px;color:#6b7280;margin:4px 0 0}

/* Filter bar */
.tb-filter-bar{display:flex;align-items:center;gap:10px;margin-bottom:18px;flex-wrap:wrap}
.tb-filter-bar input[type="search"]{flex:1;min-width:200px;max-width:280px;height:38px;
    padding:0 12px;border:1px solid #e0e1e6;border-radius:8px;font-size:13.5px;color:#1a1d29;
    outline:none;transition:.15s}
.tb-filter-bar input[type="search"]:focus{border-color:#4338ca;box-shadow:0 0 0 3px #eef2ff}
.tb-filter-bar input[type="date"]{height:38px;padding:0 10px;border:1px solid #e0e1e6;
    border-radius:8px;font-size:13px;color:#374151;outline:none;background:#fff;transition:.15s}
.tb-filter-bar input[type="date"]:focus{border-color:#4338ca}
.tb-filter-bar label{font-size:12.5px;color:#6b7280;font-weight:600;white-space:nowrap}

.tb-status-tabs{display:flex;gap:4px;background:#f3f4f6;border-radius:9px;padding:3px}
.tb-status-tabs button{height:30px;padding:0 13px;border:none;border-radius:7px;font-size:12.5px;
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
.tb-badge.pending        {background:#fffbeb;color:#b45309}.tb-badge.pending::before{background:#d97706}
.tb-badge.waiting_result {background:#eff6ff;color:#1d4ed8}.tb-badge.waiting_result::before{background:#3b82f6}
.tb-badge.result_ready   {background:#f5f3ff;color:#6d28d9}.tb-badge.result_ready::before{background:#7c3aed}
.tb-badge.approved       {background:#ecfdf5;color:#047857}.tb-badge.approved::before{background:#16a34a}
.tb-badge.rejected       {background:#fef2f2;color:#b91c1c}.tb-badge.rejected::before{background:#dc2626}
.tb-badge.revision       {background:#fff7ed;color:#c2410c}.tb-badge.revision::before{background:#ea580c}

/* Action buttons */
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

/* Empty & loading */
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
.tb-modal{background:#fff;border-radius:16px;width:100%;max-width:700px;max-height:92vh;
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

/* Hasil mini grid */
.tb-hasil-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:10px;margin-bottom:6px}
@media(max-width:520px){.tb-hasil-grid{grid-template-columns:repeat(2,1fr)}}
.tb-hasil-item{background:#f8f9fb;border-radius:10px;padding:11px 13px;text-align:center}
.tb-hasil-item .val{font-size:15px;font-weight:700;color:#1a1d29;font-family:'DM Mono',monospace}
.tb-hasil-item .lbl{font-size:11px;color:#9ca3af;font-weight:600;margin-top:3px;text-transform:uppercase}

/* Form input hasil */
.tb-result-form{background:#fafbff;border:1px solid #e9eaee;border-radius:12px;padding:16px 18px;margin-top:4px}
.tb-result-form h5{font-size:12px;font-weight:700;text-transform:uppercase;letter-spacing:.04em;
    color:#6b7280;margin:0 0 14px}
.tb-form-row{margin-bottom:12px}
.tb-form-row label{display:block;font-size:11px;font-weight:700;color:#6b7280;
    text-transform:uppercase;letter-spacing:.05em;margin-bottom:5px}
.tb-form-row input[type="text"],
.tb-form-row input[type="number"],
.tb-form-row select,
.tb-form-row textarea{
    width:100%;box-sizing:border-box;border:1px solid #e0e1e6;border-radius:8px;
    padding:0 11px;font-size:13.5px;color:#1a1d29;outline:none;background:#fff;transition:.15s;
    font-family:inherit}
.tb-form-row input[type="text"],
.tb-form-row input[type="number"],
.tb-form-row select{height:36px}
.tb-form-row textarea{padding:9px 11px;resize:vertical;min-height:72px}
.tb-form-row input:focus,.tb-form-row select:focus,.tb-form-row textarea:focus{
    border-color:#4338ca;box-shadow:0 0 0 3px #eef2ff}
.tb-form-2col{display:grid;grid-template-columns:1fr 1fr;gap:12px}
@media(max-width:480px){.tb-form-2col{grid-template-columns:1fr}}

/* Foto sampel */
.tb-foto-wrap img{border-radius:8px;max-width:100%;max-height:160px;object-fit:cover;
    border:1px solid #e9eaee;margin-top:6px}

/* Buttons */
.tb-btn{height:36px;padding:0 16px;border-radius:8px;font-size:13px;font-weight:600;
    cursor:pointer;border:1px solid transparent;transition:.12s;display:inline-flex;align-items:center;gap:6px}
.tb-btn-primary{background:#4338ca;color:#fff;border-color:#4338ca}
.tb-btn-primary:hover{background:#3730a3}
.tb-btn-ghost{background:#fff;color:#374151;border-color:#e0e1e6}
.tb-btn-ghost:hover{border-color:#9ca3af}
.tb-btn-success{background:#16a34a;color:#fff;border-color:#16a34a}
.tb-btn-success:hover{background:#15803d}
.tb-btn-danger{background:#dc2626;color:#fff;border-color:#dc2626}
.tb-btn-danger:hover{background:#b91c1c}
.tb-btn:disabled{opacity:.5;cursor:default}

/* Alert inline */
.tb-inline-alert{display:none;align-items:center;gap:8px;padding:10px 14px;border-radius:8px;
    font-size:13px;margin-bottom:14px}
.tb-inline-alert.show{display:flex}
.tb-inline-alert.error{background:#fef2f2;color:#991b1b;border:1px solid #fecaca}
.tb-inline-alert.success{background:#ecfdf5;color:#065f46;border:1px solid #a7f3d0}

/* Approval badge */
.apv-badge{display:inline-block;padding:2px 8px;border-radius:12px;font-size:11px;font-weight:700}
.apv-badge.pending            {background:#fffbeb;color:#b45309}
.apv-badge.approved           {background:#ecfdf5;color:#047857}
.apv-badge.rejected           {background:#fef2f2;color:#b91c1c}
.apv-badge.revision_requested {background:#fff7ed;color:#c2410c}
</style>

<div class="tb-layout">
    <?php include __DIR__ . '/partials/_sidebar.php'; ?>

    <main class="tb-main">
        <?php include __DIR__ . '/partials/_navbar.php'; ?>

        <div style="padding:24px;">

            <div class="tb-page-head">
                <div>
                    <h2>Permintaan Sampel</h2>
                    <p>Proses dan pantau semua permintaan sampel benang dari buyer.</p>
                </div>
            </div>

            <!-- Alert global -->
            <div class="tb-inline-alert error" id="sampelGlobalError">
                <i class="bi bi-exclamation-triangle-fill"></i>
                <span id="sampelGlobalErrorText">Terjadi kesalahan.</span>
            </div>

            <!-- Filter bar -->
            <div class="tb-filter-bar">
                <input type="search" id="sampelSearch" placeholder="Cari perusahaan atau jenis benang…" autocomplete="off">

                <div class="tb-status-tabs" id="statusTabs">
                    <button class="active" data-status="all">Semua</button>
                    <button data-status="pending">Pending</button>
                    <button data-status="waiting_result">Waiting Result</button>
                    <button data-status="result_ready">Result Ready</button>
                    <button data-status="approved">Approved</button>
                    <button data-status="rejected">Rejected</button>
                    <button data-status="revision">Revision</button>
                </div>

                <label for="filterDari">Dari</label>
                <input type="date" id="filterDari">
                <label for="filterSampai">s/d</label>
                <input type="date" id="filterSampai">

                <button class="tb-filter-btn" id="btnApplyFilter">
                    <i class="bi bi-funnel"></i> Terapkan
                </button>
                <button class="tb-filter-btn" id="btnRefreshSamples">
                    <i class="bi bi-arrow-clockwise"></i> Refresh
                </button>
            </div>

            <!-- Table -->
            <div class="tb-table-wrap">
                <table class="tb-table">
                    <thead>
                        <tr>
                            <th>#ID</th>
                            <th>Buyer / PIC</th>
                            <th>Jenis Benang</th>
                            <th>Ukuran</th>
                            <th>Kode Warna Target</th>
                            <th>Tgl Permintaan</th>
                            <th>Dibutuhkan</th>
                            <th>Hasil</th>
                            <th>Status</th>
                            <th style="text-align:right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="sampelTableBody">
                        <?php for ($i = 0; $i < 6; $i++): ?>
                        <tr class="tb-skeleton-row">
                            <?php for ($j = 0; $j < 10; $j++): ?>
                            <td><div class="tb-skeleton" style="width:<?= [50,120,100,60,90,70,70,60,70,40][$j] ?>px"></div></td>
                            <?php endfor; ?>
                        </tr>
                        <?php endfor; ?>
                    </tbody>
                </table>
                <div class="tb-pagination" id="sampelPagination" style="display:none">
                    <span id="sampelPagInfo">Menampilkan data…</span>
                    <div class="tb-pag-btns" id="sampelPagBtns"></div>
                </div>
            </div>

        </div><!-- /padding -->
    </main>
</div>

<!-- ============================================================
     MODAL — Detail & Proses Sampel
     ============================================================ -->
<div class="tb-modal-overlay" id="modalSampel" role="dialog" aria-modal="true" aria-labelledby="modalSampelTitle">
    <div class="tb-modal">
        <div class="tb-modal__head">
            <h3 id="modalSampelTitle">Detail Permintaan Sampel</h3>
            <button class="tb-modal__close" id="btnCloseModal"><i class="bi bi-x"></i></button>
        </div>
        <div class="tb-modal__body" id="modalSampelBody">
            <div style="text-align:center;padding:40px 0;color:#9ca3af">
                <i class="bi bi-hourglass-split" style="font-size:28px"></i>
                <p style="margin-top:10px;font-size:14px">Memuat data…</p>
            </div>
        </div>
        <div class="tb-modal__foot" id="modalSampelFoot">
            <button class="tb-btn tb-btn-ghost" id="btnCloseModal2">Tutup</button>
        </div>
    </div>
</div>

<!-- ============================================================
     MODAL — Konfirmasi approve / reject
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
            <p id="confirmNote"    style="font-size:12.5px;color:#9ca3af;margin:0"></p>
        </div>
        <div class="tb-modal__foot">
            <button class="tb-btn tb-btn-ghost" id="btnCancelConfirm">Batal</button>
            <button class="tb-btn"              id="btnDoConfirm">Konfirmasi</button>
        </div>
    </div>
</div>


<script>
(function () {
    'use strict';
    const ADMIN_URL = '<?= ADMIN_URL ?>';

    /* ── State ─────────────────────────────────────────────── */
    let state = { status:'all', search:'', dari:'', sampai:'', page:1, limit:20 };
    let activeSampel = null;

    /* ── DOM refs ──────────────────────────────────────────── */
    const tbody         = document.getElementById('sampelTableBody');
    const pagination    = document.getElementById('sampelPagination');
    const pagInfo       = document.getElementById('sampelPagInfo');
    const pagBtns       = document.getElementById('sampelPagBtns');
    const searchInput   = document.getElementById('sampelSearch');
    const statusTabs    = document.getElementById('statusTabs');
    const filterDari    = document.getElementById('filterDari');
    const filterSampai  = document.getElementById('filterSampai');
    const btnApply      = document.getElementById('btnApplyFilter');
    const btnRefresh    = document.getElementById('btnRefreshSamples');
    const globalError   = document.getElementById('sampelGlobalError');
    const globalErrText = document.getElementById('sampelGlobalErrorText');

    const modalSampel   = document.getElementById('modalSampel');
    const modalBody     = document.getElementById('modalSampelBody');
    const modalFoot     = document.getElementById('modalSampelFoot');
    const modalTitle    = document.getElementById('modalSampelTitle');

    const modalConfirm  = document.getElementById('modalConfirm');
    const confirmTitle  = document.getElementById('confirmTitle');
    const confirmMsg    = document.getElementById('confirmMessage');
    const confirmNote   = document.getElementById('confirmNote');
    const confirmError  = document.getElementById('confirmError');
    const confirmErrTxt = document.getElementById('confirmErrorText');
    const btnDoConfirm  = document.getElementById('btnDoConfirm');

    /* ── Helpers ───────────────────────────────────────────── */
    const STATUS_LABEL = {
        pending:'Pending', waiting_result:'Waiting Result', result_ready:'Result Ready',
        approved:'Approved', rejected:'Rejected', revision:'Revision',
    };
    const APV_LABEL = {
        pending:'Pending', approved:'Approved', rejected:'Rejected', revision_requested:'Perlu Revisi',
    };

    function badgeHtml(status) {
        return `<span class="tb-badge ${status}">${STATUS_LABEL[status] || status}</span>`;
    }
    function apvBadge(status) {
        return `<span class="apv-badge ${status}">${APV_LABEL[status] || status}</span>`;
    }
    function fmtDate(str) {
        if (!str) return '—';
        return new Date(str).toLocaleDateString('id-ID', {day:'numeric',month:'short',year:'numeric'});
    }
    function escHtml(str) {
        return String(str ?? '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
    }
    function showGlobalError(msg) { globalErrText.textContent = msg; globalError.classList.add('show'); }
    function hideGlobalError()    { globalError.classList.remove('show'); }

    /* ── Skeleton ──────────────────────────────────────────── */
    function renderSkeleton() {
        const w = [50,120,100,60,90,70,70,60,70,40];
        tbody.innerHTML = Array.from({length:6}, () =>
            `<tr class="tb-skeleton-row">${w.map(x=>`<td><div class="tb-skeleton" style="width:${x}px"></div></td>`).join('')}</tr>`
        ).join('');
        pagination.style.display = 'none';
    }

    /* ── Load samples ──────────────────────────────────────── */
    let searchTimer;
    async function loadSamples() {
        hideGlobalError();
        renderSkeleton();

        const params = new URLSearchParams({
            status:state.status, dari:state.dari, sampai:state.sampai,
            page:state.page, limit:state.limit,
        });

        try {
            const res  = await fetch(`${ADMIN_URL}/fetch-data/fetchSamples.php?${params}`, {credentials:'same-origin'});
            const ct   = res.headers.get('content-type') || '';
            if (!ct.includes('application/json'))
                throw new Error('Sesi habis atau terjadi error server. Silakan refresh halaman.');
            const json = await res.json();
            if (json.status === 'error') throw new Error(json.message);
            renderSamples(json.samples, json.pagination);
        } catch (err) {
            tbody.innerHTML = `<tr><td colspan="10" class="tb-table-empty">
                <i class="bi bi-exclamation-triangle"></i>
                <p>${escHtml(err.message || 'Gagal memuat data.')}</p></td></tr>`;
            showGlobalError(err.message || 'Gagal memuat data.');
            pagination.style.display = 'none';
        }
    }

    function renderSamples(samples, pag) {
        if (!samples || samples.length === 0) {
            tbody.innerHTML = `<tr><td colspan="10" class="tb-table-empty">
                <i class="bi bi-palette"></i>
                <p>Tidak ada permintaan sampel yang ditemukan.</p></td></tr>`;
            pagination.style.display = 'none';
            return;
        }

        tbody.innerHTML = samples.map(s => {
            const hasilBadge = s.id_result
                ? apvBadge(s.status_approval || 'pending')
                : `<span style="font-size:12px;color:#c7c8cc">Belum ada</span>`;

            return `<tr>
                <td class="is-mono">#${s.id_request}</td>
                <td>
                    <div class="company">${escHtml(s.nama_perusahaan)}</div>
                    <div class="pic">${escHtml(s.nama_pic)}</div>
                </td>
                <td>${escHtml(s.jenis_benang)}</td>
                <td>${escHtml(s.ukuran_benang || '—')}</td>
                <td>${escHtml(s.kode_warna_target || '—')}</td>
                <td style="white-space:nowrap;font-size:12.5px;color:#6b7280">${fmtDate(s.tanggal)}</td>
                <td style="white-space:nowrap;font-size:12.5px;color:#6b7280">${fmtDate(s.tanggal_dibutuhkan)}</td>
                <td>${hasilBadge}</td>
                <td>${badgeHtml(s.status)}</td>
                <td>
                    <div class="tb-row-actions" style="justify-content:flex-end">
                        <button class="tb-btn-icon" title="Lihat detail & proses"
                            onclick="openSampelDetail(${s.id_request})">
                            <i class="bi bi-eye"></i>
                        </button>
                    </div>
                </td>
            </tr>`;
        }).join('');

        /* Pagination */
        const total = pag.total, pages = pag.total_pages;
        const from  = (pag.page - 1) * pag.limit + 1;
        const to    = Math.min(pag.page * pag.limit, total);
        pagInfo.textContent = `Menampilkan ${from}–${to} dari ${total} permintaan`;

        pagBtns.innerHTML = '';
        pagBtns.appendChild(makePageBtn('‹', pag.page - 1, pag.page <= 1));
        pageRange(pag.page, pages).forEach(p => {
            if (p === '…') {
                const el = document.createElement('button');
                el.textContent = '…'; el.disabled = true; pagBtns.appendChild(el);
            } else pagBtns.appendChild(makePageBtn(p, p, false, p === pag.page));
        });
        pagBtns.appendChild(makePageBtn('›', pag.page + 1, pag.page >= pages));
        pagination.style.display = 'flex';
    }

    function makePageBtn(label, target, disabled, active=false) {
        const btn = document.createElement('button');
        btn.textContent = label; btn.disabled = disabled;
        if (active) btn.classList.add('active');
        if (!disabled) btn.addEventListener('click', () => { state.page = target; loadSamples(); });
        return btn;
    }
    function pageRange(cur, total) {
        if (total <= 7) return Array.from({length:total}, (_,i) => i+1);
        if (cur <= 4)   return [1,2,3,4,5,'…',total];
        if (cur >= total-3) return [1,'…',total-4,total-3,total-2,total-1,total];
        return [1,'…',cur-1,cur,cur+1,'…',total];
    }

    /* ── Fetch detail satu row → buka modal ─────────────────── */
    // Data sudah ada di row, langsung pakai tanpa fetch ulang
    const _rowCache = {};  // simpan data per id_request agar bisa dipakai ulang

    window.openSampelDetail = async function (idRequest) {
        modalTitle.textContent = `Detail Permintaan Sampel`;
        modalBody.innerHTML = `<div style="text-align:center;padding:40px 0;color:#9ca3af">
            <i class="bi bi-hourglass-split" style="font-size:28px"></i>
            <p style="margin-top:10px;font-size:14px">Memuat data…</p></div>`;
        modalFoot.innerHTML = `<button class="tb-btn tb-btn-ghost" onclick="closeModalSampel()">Tutup</button>`;
        openModal(modalSampel);

        try {
            const res  = await fetch(`${ADMIN_URL}/fetch-data/fetchSamples.php?id_request=${idRequest}&limit=1`, {credentials:'same-origin'});
            const json = await res.json();
            if (json.status === 'error') throw new Error(json.message);
            const s = (json.samples || [])[0];
            if (!s) throw new Error('Data tidak ditemukan.');
            activeSampel = s;
            modalTitle.textContent = `Sampel #${s.id_request} — ${s.nama_perusahaan}`;
            renderModalBody(s);
        } catch (err) {
            modalBody.innerHTML = `<div style="text-align:center;padding:32px;color:#b91c1c;font-size:14px">
                <i class="bi bi-exclamation-triangle-fill" style="font-size:28px;display:block;margin-bottom:8px"></i>
                ${escHtml(err.message || 'Gagal memuat detail.')}
            </div>`;
        }
    };

    function renderModalBody(s) {
        const canProcess = ['pending','waiting_result','result_ready'].includes(s.status);

        const pilOpts = ['A','B','rejected','pending'].map(v =>
            `<option value="${v}" ${s.pilihan===v?'selected':''}>${v}</option>`).join('');

        const apvOpts = [
            {v:'pending',            l:'Pending'},
            {v:'approved',           l:'Approved'},
            {v:'rejected',           l:'Rejected'},
            {v:'revision_requested', l:'Revision Requested'},
        ].map(o => `<option value="${o.v}" ${(s.status_approval||'pending')===o.v?'selected':''}>${o.l}</option>`).join('');

        const nextStatus = {pending:'waiting_result', waiting_result:'result_ready', result_ready:'approved'};
        const srqOpts = [
            {v:'waiting_result',l:'Waiting Result'},
            {v:'result_ready',  l:'Result Ready'},
            {v:'approved',      l:'Approved'},
            {v:'rejected',      l:'Rejected'},
            {v:'revision',      l:'Revision'},
        ].map(o => `<option value="${o.v}" ${(nextStatus[s.status]||'result_ready')===o.v?'selected':''}>${o.l}</option>`).join('');

        modalBody.innerHTML = `
            <div class="tb-detail-grid">
                <div class="tb-detail-item"><label>ID Request</label><span class="mono">#${s.id_request}</span></div>
                <div class="tb-detail-item"><label>Status</label><span>${badgeHtml(s.status)}</span></div>
                <div class="tb-detail-item"><label>Perusahaan</label><span>${escHtml(s.nama_perusahaan)}</span></div>
                <div class="tb-detail-item"><label>PIC</label><span>${escHtml(s.nama_pic)}</span></div>
                <div class="tb-detail-item"><label>Jenis Benang</label><span>${escHtml(s.jenis_benang)}</span></div>
                <div class="tb-detail-item"><label>Ukuran Benang</label><span>${escHtml(s.ukuran_benang || '—')}</span></div>
                <div class="tb-detail-item"><label>Kode Warna Target</label><span>${escHtml(s.kode_warna_target || '—')}</span></div>
                <div class="tb-detail-item"><label>Tgl Permintaan</label><span>${fmtDate(s.tanggal)}</span></div>
                <div class="tb-detail-item"><label>Dibutuhkan Sebelum</label><span>${fmtDate(s.tanggal_dibutuhkan)}</span></div>
            </div>

            ${s.catatan ? `<div class="tb-detail-item" style="margin-bottom:16px">
                <label style="display:block;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:#9ca3af;margin-bottom:4px">Catatan Buyer</label>
                <p style="margin:0;font-size:13.5px;color:#374151;white-space:pre-line">${escHtml(s.catatan)}</p>
            </div>` : ''}

            ${s.upload_sampel ? `
            <div class="tb-section-title">Foto Sampel (dari Buyer)</div>
            <div class="tb-foto-wrap"><img src="${escHtml(s.upload_sampel)}" alt="Foto Sampel"></div>
            ` : ''}

            ${s.id_result ? `
            <div class="tb-section-title">Hasil Sampel Saat Ini</div>
            <div class="tb-hasil-grid">
                <div class="tb-hasil-item"><div class="val">${escHtml(s.kode_warna_hasil || '—')}</div><div class="lbl">Kode Warna</div></div>
                <div class="tb-hasil-item"><div class="val">${escHtml(s.pilihan || '—')}</div><div class="lbl">Pilihan</div></div>
                <div class="tb-hasil-item"><div class="val">${s.nilai_delta_e != null ? s.nilai_delta_e : '—'}</div><div class="lbl">Delta E</div></div>
                <div class="tb-hasil-item"><div class="val" style="font-size:12px">${apvBadge(s.status_approval || 'pending')}</div><div class="lbl">Approval</div></div>
            </div>
            ` : ''}

            ${canProcess ? `
            <div class="tb-section-title">Input / Update Hasil Sampel</div>
            <div class="tb-result-form">
                <h5><i class="bi bi-clipboard2-check" style="margin-right:6px;color:#4338ca"></i>Form Hasil</h5>
                <div class="tb-form-2col">
                    <div class="tb-form-row">
                        <label>Kode Warna Hasil</label>
                        <input type="text" id="rKodeWarna" value="${escHtml(s.kode_warna_hasil || '')}" placeholder="e.g. 59651" maxlength="30">
                    </div>
                    <div class="tb-form-row">
                        <label>Pilihan</label>
                        <select id="rPilihan">${pilOpts}</select>
                    </div>
                    <div class="tb-form-row">
                        <label>Nilai Delta E</label>
                        <input type="number" id="rDeltaE" step="0.01" min="0" value="${escHtml(s.nilai_delta_e || '')}" placeholder="e.g. 1.50">
                    </div>
                    <div class="tb-form-row">
                        <label>Status Approval</label>
                        <select id="rStatusApproval">${apvOpts}</select>
                    </div>
                </div>
                <div class="tb-form-row">
                    <label>Catatan Hasil</label>
                    <textarea id="rCatatan" placeholder="Catatan untuk buyer…"></textarea>
                </div>
                <div class="tb-form-row">
                    <label>Update Status Request</label>
                    <select id="rStatusRequest">${srqOpts}</select>
                </div>
                <div class="tb-inline-alert error" id="modalSaveError">
                    <i class="bi bi-exclamation-triangle-fill"></i>
                    <span id="modalSaveErrorText"></span>
                </div>
            </div>
            ` : `
            <div style="margin-top:16px;padding:12px 14px;background:#f8f9fb;border-radius:10px;
                font-size:13px;color:#6b7280;text-align:center">
                <i class="bi bi-lock" style="margin-right:6px"></i>
                Permintaan sudah berstatus <strong>${STATUS_LABEL[s.status] || s.status}</strong> dan tidak dapat diubah.
            </div>
            `}
        `;

        /* Footer */
        const btns = [`<button class="tb-btn tb-btn-ghost" onclick="closeModalSampel()">Tutup</button>`];
        if (canProcess) {
            btns.unshift(`<button class="tb-btn tb-btn-primary" id="btnSaveResult" onclick="saveSampleResult()">
                <i class="bi bi-save"></i> Simpan Hasil
            </button>`);
        }
        if (s.status === 'result_ready') {
            btns.unshift(`<button class="tb-btn tb-btn-danger" onclick="closeModalSampel();confirmStatusChange(${s.id_request},'rejected','Tolak Sampel')">
                <i class="bi bi-x-circle"></i> Tolak
            </button>`);
            btns.unshift(`<button class="tb-btn tb-btn-success" onclick="closeModalSampel();confirmStatusChange(${s.id_request},'approved','Setujui Sampel')">
                <i class="bi bi-check-circle"></i> Setujui
            </button>`);
        }
        modalFoot.innerHTML = btns.join('');
    }

    window.closeModalSampel = function () { closeModal(modalSampel); activeSampel = null; };

    /* ── Simpan hasil ───────────────────────────────────────── */
    window.saveSampleResult = async function () {
        if (!activeSampel) return;
        const btn    = document.getElementById('btnSaveResult');
        const errEl  = document.getElementById('modalSaveError');
        const errTxt = document.getElementById('modalSaveErrorText');
        errEl?.classList.remove('show');
        if (btn) { btn.disabled = true; btn.innerHTML = '<i class="bi bi-hourglass-split"></i> Menyimpan…'; }

        const payload = {
            id_request      : activeSampel.id_request,
            kode_warna_hasil: document.getElementById('rKodeWarna')?.value.trim()  || null,
            pilihan         : document.getElementById('rPilihan')?.value            || 'pending',
            nilai_delta_e   : document.getElementById('rDeltaE')?.value             || null,
            status_approval : document.getElementById('rStatusApproval')?.value     || 'pending',
            catatan         : document.getElementById('rCatatan')?.value.trim()     || null,
            status_request  : document.getElementById('rStatusRequest')?.value      || null,
        };

        try {
            const res  = await fetch(`${ADMIN_URL}/fetch-data/saveSampleResult.php`, {
                method:'POST', credentials:'same-origin',
                headers:{'Content-Type':'application/json'},
                body: JSON.stringify(payload),
            });
            const json = await res.json();
            if (json.status === 'error') throw new Error(json.message);
            closeModal(modalSampel);
            activeSampel = null;
            loadSamples();
        } catch (err) {
            if (errTxt) errTxt.textContent = err.message || 'Gagal menyimpan.';
            errEl?.classList.add('show');
        } finally {
            if (btn) { btn.disabled = false; btn.innerHTML = '<i class="bi bi-save"></i> Simpan Hasil'; }
        }
    };

    /* ── Konfirmasi approve / reject ─────────────────────────── */
    window.confirmStatusChange = function (idRequest, newStatus, judul) {
        const isApprove = newStatus === 'approved';
        confirmTitle.textContent = judul;
        confirmMsg.textContent   = `Anda akan ${isApprove ? 'menyetujui' : 'menolak'} hasil sampel permintaan #${idRequest}.`;
        confirmNote.textContent  = isApprove
            ? 'Buyer akan diberitahu bahwa sampel mereka disetujui.'
            : 'Buyer akan diberitahu bahwa sampel mereka ditolak.';
        confirmError.classList.remove('show');
        btnDoConfirm.className   = `tb-btn ${isApprove ? 'tb-btn-success' : 'tb-btn-danger'}`;
        btnDoConfirm.textContent = isApprove ? 'Ya, Setujui' : 'Ya, Tolak';
        btnDoConfirm.onclick     = () => doStatusChange(idRequest, newStatus);
        openModal(modalConfirm);
    };

    async function doStatusChange(idRequest, newStatus) {
        btnDoConfirm.disabled = true;
        confirmError.classList.remove('show');
        try {
            const res  = await fetch(`${ADMIN_URL}/fetch-data/saveSampleResult.php`, {
                method:'POST', credentials:'same-origin',
                headers:{'Content-Type':'application/json'},
                body: JSON.stringify({id_request: idRequest, status_request: newStatus}),
            });
            const json = await res.json();
            if (json.status === 'error') throw new Error(json.message);
            closeModal(modalConfirm);
            loadSamples();
        } catch (err) {
            confirmErrTxt.textContent = err.message || 'Gagal memperbarui status.';
            confirmError.classList.add('show');
        } finally {
            btnDoConfirm.disabled = false;
        }
    }

    /* ── Modal helpers ─────────────────────────────────────── */
    function openModal(el)  { el.classList.add('open');    document.body.style.overflow = 'hidden'; }
    function closeModal(el) { el.classList.remove('open'); document.body.style.overflow = ''; }

    /* ── Events ────────────────────────────────────────────── */
    document.getElementById('btnCloseModal')?.addEventListener('click', closeModalSampel);
    document.getElementById('btnCloseModal2')?.addEventListener('click', closeModalSampel);
    document.getElementById('btnCancelConfirm')?.addEventListener('click', () => closeModal(modalConfirm));
    document.getElementById('btnCloseConfirm')?.addEventListener('click', () => closeModal(modalConfirm));

    [modalSampel, modalConfirm].forEach(m => {
        m.addEventListener('click', e => { if (e.target === m) closeModal(m); });
    });

    statusTabs.querySelectorAll('button').forEach(btn => {
        btn.addEventListener('click', () => {
            statusTabs.querySelectorAll('button').forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
            state.status = btn.dataset.status;
            state.page   = 1;
            loadSamples();
        });
    });

    searchInput.addEventListener('input', () => {
        clearTimeout(searchTimer);
        searchTimer = setTimeout(() => { state.search = searchInput.value.trim(); state.page = 1; loadSamples(); }, 380);
    });

    btnApply.addEventListener('click', () => {
        state.dari = filterDari.value; state.sampai = filterSampai.value; state.page = 1; loadSamples();
    });
    btnRefresh.addEventListener('click', () => { state.page = 1; loadSamples(); });

    document.addEventListener('keydown', e => {
        if (e.key === 'Escape') {
            if (modalConfirm.classList.contains('open')) closeModal(modalConfirm);
            else if (modalSampel.classList.contains('open')) closeModal(modalSampel);
        }
    });

    /* URL prefill */
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
    loadSamples();

})();
</script>

<?php
$extraJs = [];
include __DIR__ . '/partials/_footer.php';
?>