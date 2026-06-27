<?php
// ============================================================
//  ThreadB2B — admin_panel/invoices-detail.php
//  Halaman detail invoice: info buyer, rincian item per SJ,
//  ringkasan pembayaran, riwayat status, dan aksi (tandai lunas,
//  cetak, batalkan).
//  Diakses via: invoices-detail.php?invoice_id=INV-2026-01906
// ============================================================
$pageTitle  = 'Detail Invoice';
$activePage = 'invoices';

require_once __DIR__ . '/partials/config.php';

$invoiceId = trim($_GET['invoice_id'] ?? '');
if ($invoiceId === '') {
    header('Location: ' . ADMIN_URL . '/invoices.php');
    exit;
}
?>
<?php include __DIR__ . '/partials/_header.php'; ?>

<style>
.tb-back-link{display:inline-flex;align-items:center;gap:6px;font-size:13px;font-weight:600;
    color:#6b7280;text-decoration:none;margin-bottom:14px}
.tb-back-link:hover{color:#4338ca}

.tb-page-head{display:flex;align-items:flex-start;justify-content:space-between;margin-bottom:20px;flex-wrap:wrap;gap:14px}
.tb-page-head h2{font-size:20px;font-weight:700;color:#1a1d29;margin:0;display:flex;align-items:center;gap:10px}
.tb-page-head .mono-id{font-family:'DM Mono',monospace;color:#4338ca}
.tb-page-head p{font-size:13.5px;color:#6b7280;margin:4px 0 0}
.tb-head-actions{display:flex;gap:8px;flex-wrap:wrap}

.tb-inline-alert{display:none;align-items:center;gap:8px;padding:10px 14px;border-radius:8px;
    font-size:13px;margin-bottom:14px}
.tb-inline-alert.show{display:flex}
.tb-inline-alert.error{background:#fef2f2;color:#991b1b;border:1px solid #fecaca}
.tb-inline-alert.success{background:#ecfdf5;color:#065f46;border:1px solid #a7f3d0}

/* Status badges */
.tb-badge{display:inline-flex;align-items:center;gap:5px;padding:3px 10px;border-radius:20px;
    font-size:12px;font-weight:700;letter-spacing:.02em;white-space:nowrap}
.tb-badge::before{content:'';width:6px;height:6px;border-radius:50%;display:inline-block}
.tb-badge.DRAFT   {background:#f3f4f6;color:#6b7280}.tb-badge.DRAFT::before{background:#9ca3af}
.tb-badge.ISSUED  {background:#eff6ff;color:#1d4ed8}.tb-badge.ISSUED::before{background:#2563eb}
.tb-badge.PAID    {background:#ecfdf5;color:#047857}.tb-badge.PAID::before{background:#16a34a}
.tb-badge.OVERDUE {background:#fef2f2;color:#b91c1c}.tb-badge.OVERDUE::before{background:#dc2626}

/* Layout 2 kolom */
.tb-detail-layout{display:grid;grid-template-columns:1fr 320px;gap:20px;align-items:start}
@media(max-width:900px){.tb-detail-layout{grid-template-columns:1fr}}

.tb-card{background:#fff;border:1px solid #e9eaee;border-radius:14px;padding:20px}
.tb-card + .tb-card{margin-top:16px}
.tb-card h3{font-size:13.5px;font-weight:700;color:#1a1d29;margin:0 0 14px;text-transform:uppercase;
    letter-spacing:.04em;color:#9ca3af}

.tb-detail-grid{display:grid;grid-template-columns:1fr 1fr;gap:16px}
@media(max-width:520px){.tb-detail-grid{grid-template-columns:1fr}}
.tb-detail-item label{display:block;font-size:11px;font-weight:700;text-transform:uppercase;
    letter-spacing:.06em;color:#9ca3af;margin-bottom:4px}
.tb-detail-item span{font-size:13.5px;color:#1a1d29;font-weight:500;word-break:break-word}
.tb-detail-item span.mono{font-family:'DM Mono',monospace;color:#4338ca}

/* Items table */
.tb-mini-table{width:100%;border-collapse:collapse;font-size:13px}
.tb-mini-table th{font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.04em;
    color:#9ca3af;padding:8px 10px;text-align:left;border-bottom:1px solid #e9eaee;white-space:nowrap}
.tb-mini-table td{padding:10px 10px;border-bottom:1px solid #f8f9fb;color:#374151}
.tb-mini-table td.is-num{font-family:'DM Mono',monospace;text-align:right;white-space:nowrap}
.tb-mini-table tr:last-child td{border-bottom:none}
.tb-mini-table tfoot td{font-weight:700;color:#1a1d29;border-top:1px solid #e9eaee;border-bottom:none}

/* Timeline status */
.tb-timeline{list-style:none;margin:0;padding:0}
.tb-timeline li{display:flex;gap:10px;padding:8px 0;font-size:12.5px;color:#374151}
.tb-timeline li .dot{width:8px;height:8px;border-radius:50%;background:#4338ca;margin-top:5px;flex-shrink:0}
.tb-timeline li .t{color:#9ca3af;font-size:11.5px}

/* Sidebar summary */
.tb-summary-card{position:sticky;top:20px}
.tb-summary-row{display:flex;justify-content:space-between;align-items:center;font-size:13px;
    color:#374151;padding:8px 0;border-bottom:1px solid #f8f9fb}
.tb-summary-row .lbl{color:#6b7280}
.tb-summary-row .val{font-family:'DM Mono',monospace;font-weight:600}
.tb-summary-total{display:flex;justify-content:space-between;align-items:center;margin-top:10px;
    padding-top:14px;border-top:1px solid #e9eaee}
.tb-summary-total .lbl{font-size:13.5px;font-weight:700;color:#1a1d29}
.tb-summary-total .val{font-size:18px;font-weight:700;color:#4338ca;font-family:'DM Mono',monospace}

.tb-overdue-banner{display:flex;align-items:center;gap:10px;background:#fef2f2;color:#b91c1c;
    border:1px solid #fecaca;border-radius:10px;padding:12px 14px;font-size:13px;font-weight:600;margin-bottom:16px}

.tb-btn{height:38px;padding:0 16px;border-radius:8px;font-size:13px;font-weight:600;
    cursor:pointer;border:1px solid transparent;transition:.12s;display:inline-flex;align-items:center;
    justify-content:center;gap:6px}
.tb-btn-primary{background:#4338ca;color:#fff;border-color:#4338ca}
.tb-btn-primary:hover{background:#3730a3}
.tb-btn-success{background:#16a34a;color:#fff;border-color:#16a34a}
.tb-btn-success:hover{background:#15803d}
.tb-btn-danger{background:#dc2626;color:#fff;border-color:#dc2626}
.tb-btn-danger:hover{background:#b91c1c}
.tb-btn-ghost{background:#fff;color:#374151;border-color:#e0e1e6}
.tb-btn-ghost:hover{border-color:#9ca3af}
.tb-btn-block{width:100%}
.tb-btn:disabled{opacity:.5;cursor:default}

.tb-skel-block{height:18px;border-radius:5px;margin-bottom:8px;background:linear-gradient(90deg,#eee 25%,#f5f5f5 37%,#eee 63%);
    background-size:400% 100%;animation:tbShimmer 1.4s ease infinite}
@keyframes tbShimmer{0%{background-position:100% 50%}100%{background-position:0 50%}}

/* ---- Modal konfirmasi (reuse) ---- */
.tb-modal-overlay{position:fixed;inset:0;background:rgba(17,24,39,.45);z-index:1000;
    display:none;align-items:center;justify-content:center;padding:20px;backdrop-filter:blur(2px)}
.tb-modal-overlay.open{display:flex}
.tb-modal{background:#fff;border-radius:16px;width:100%;max-width:420px;max-height:92vh;
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
</style>

<div class="tb-layout">
    <?php include __DIR__ . '/partials/_sidebar.php'; ?>

    <main class="tb-main">
        <?php include __DIR__ . '/partials/_navbar.php'; ?>

        <div style="padding:24px;max-width:1100px">

            <a href="<?= ADMIN_URL ?>/invoices.php" class="tb-back-link">
                <i class="bi bi-arrow-left"></i> Kembali ke Manajemen Invoice
            </a>

            <div class="tb-inline-alert error" id="detGlobalError">
                <i class="bi bi-exclamation-triangle-fill"></i>
                <span id="detGlobalErrorText"></span>
            </div>
            <div class="tb-inline-alert success" id="detGlobalSuccess">
                <i class="bi bi-check-circle-fill"></i>
                <span id="detGlobalSuccessText"></span>
            </div>

            <div class="tb-page-head">
                <div>
                    <h2><span class="mono-id"><?= htmlspecialchars($invoiceId) ?></span> <span id="headBadge"></span></h2>
                    <p id="headSubtitle">Memuat data invoice…</p>
                </div>
                <div class="tb-head-actions">
                    <button class="tb-btn tb-btn-ghost" id="btnPrintInvoice">
                        <i class="bi bi-printer"></i> Cetak
                    </button>
                    <button class="tb-btn tb-btn-success" id="btnMarkPaid" style="display:none">
                        <i class="bi bi-check-lg"></i> Tandai Lunas
                    </button>
                    <button class="tb-btn tb-btn-danger" id="btnCancelInvoice" style="display:none">
                        <i class="bi bi-x-lg"></i> Batalkan
                    </button>
                </div>
            </div>

            <div class="tb-overdue-banner" id="overdueBanner" style="display:none">
                <i class="bi bi-exclamation-triangle-fill"></i>
                <span id="overdueBannerText"></span>
            </div>

            <div class="tb-detail-layout">

                <!-- ── Kolom kiri ─────────────────────────────── -->
                <div>
                    <div class="tb-card">
                        <h3>Informasi Buyer & Invoice</h3>
                        <div class="tb-detail-grid" id="infoGrid">
                            <div class="tb-skel-block" style="width:80%"></div>
                            <div class="tb-skel-block" style="width:60%"></div>
                            <div class="tb-skel-block" style="width:70%"></div>
                            <div class="tb-skel-block" style="width:50%"></div>
                        </div>
                    </div>

                    <div class="tb-card">
                        <h3>Rincian Item</h3>
                        <div style="overflow-x:auto">
                            <table class="tb-mini-table" id="itemsTable">
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
                                <tbody id="itemsBody">
                                    <tr><td colspan="6" style="text-align:center;color:#9ca3af;padding:24px 0">Memuat item…</td></tr>
                                </tbody>
                                <tfoot id="itemsFoot"></tfoot>
                            </table>
                        </div>
                    </div>

                    <div class="tb-card">
                        <h3>Riwayat Status</h3>
                        <ul class="tb-timeline" id="timelineList">
                            <li><span class="dot"></span><div>Memuat riwayat…</div></li>
                        </ul>
                    </div>
                </div>

                <!-- ── Kolom kanan ────────────────────────────── -->
                <div>
                    <div class="tb-card tb-summary-card">
                        <h3>Ringkasan Pembayaran</h3>
                        <div class="tb-summary-row"><span class="lbl">Subtotal</span><span class="val" id="sumSubtotal">—</span></div>
                        <div class="tb-summary-row"><span class="lbl">PPN</span><span class="val" id="sumPpn">—</span></div>
                        <div class="tb-summary-total"><span class="lbl">Total</span><span class="val" id="sumTotal">—</span></div>
                    </div>
                </div>

            </div><!-- /tb-detail-layout -->

        </div><!-- /padding -->

    </main>
</div>

<!-- ============================================================
     MODAL — Konfirmasi Aksi
     ============================================================ -->
<div class="tb-modal-overlay" id="modalConfirm" role="dialog" aria-modal="true">
    <div class="tb-modal">
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
    const ADMIN_URL  = '<?= ADMIN_URL ?>';
    const INVOICE_ID = <?= json_encode($invoiceId) ?>;

    /* ── DOM refs ──────────────────────────────────────────── */
    const headBadge      = document.getElementById('headBadge');
    const headSubtitle   = document.getElementById('headSubtitle');
    const overdueBanner  = document.getElementById('overdueBanner');
    const overdueBannerText = document.getElementById('overdueBannerText');
    const infoGrid       = document.getElementById('infoGrid');
    const itemsBody      = document.getElementById('itemsBody');
    const itemsFoot      = document.getElementById('itemsFoot');
    const timelineList   = document.getElementById('timelineList');
    const sumSubtotal    = document.getElementById('sumSubtotal');
    const sumPpn         = document.getElementById('sumPpn');
    const sumTotal       = document.getElementById('sumTotal');

    const btnPrint       = document.getElementById('btnPrintInvoice');
    const btnMarkPaid    = document.getElementById('btnMarkPaid');
    const btnCancelInv   = document.getElementById('btnCancelInvoice');

    const globalError    = document.getElementById('detGlobalError');
    const globalErrText  = document.getElementById('detGlobalErrorText');
    const globalSuccess  = document.getElementById('detGlobalSuccess');
    const globalSucText  = document.getElementById('detGlobalSuccessText');

    const modalConfirm   = document.getElementById('modalConfirm');
    const confirmTitle   = document.getElementById('confirmTitle');
    const confirmMsg     = document.getElementById('confirmMessage');
    const confirmNote    = document.getElementById('confirmNote');
    const confirmError   = document.getElementById('confirmError');
    const confirmErrTxt  = document.getElementById('confirmErrorText');
    const btnDoConfirm   = document.getElementById('btnDoConfirm');
    const btnCancel      = document.getElementById('btnCancelConfirm');
    const btnCloseConf   = document.getElementById('btnCloseConfirm');

    /* ── Helpers ───────────────────────────────────────────── */
    function escHtml(str) {
        return String(str ?? '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
    }
    function formatRupiah(n) {
        return 'Rp ' + Number(n || 0).toLocaleString('id-ID', { maximumFractionDigits: 0 });
    }
    function fmtDate(str) {
        if (!str) return '—';
        return new Date(str).toLocaleDateString('id-ID', { day:'numeric', month:'short', year:'numeric' });
    }
    function fmtDateTime(str) {
        if (!str) return '—';
        return new Date(str).toLocaleString('id-ID', { day:'numeric', month:'short', year:'numeric', hour:'2-digit', minute:'2-digit' });
    }
    function badgeHtml(status) {
        const label = { DRAFT:'Draft', ISSUED:'Issued', PAID:'Lunas', OVERDUE:'Overdue' }[status] || status;
        return `<span class="tb-badge ${status}">${label}</span>`;
    }
    function showError(msg) {
        globalSuccess.classList.remove('show');
        globalErrText.textContent = msg;
        globalError.classList.add('show');
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }
    function showSuccess(msg) {
        globalError.classList.remove('show');
        globalSucText.textContent = msg;
        globalSuccess.classList.add('show');
    }

    /* ── Load detail invoice ───────────────────────────────── */
    async function loadDetail() {
        try {
            const res  = await fetch(`${ADMIN_URL}/fetch-data/fetchInvoiceDetail.php?invoice_id=${encodeURIComponent(INVOICE_ID)}`, { credentials: 'same-origin' });
            const json = await res.json();
            if (json.status === 'error') throw new Error(json.message);

            const data    = json.data || json;
            const inv     = data.invoice;
            const items   = data.items || [];
            const history = data.history || [];

            renderHeader(inv);
            renderInfo(inv);
            renderItems(items, inv);
            renderTimeline(history, inv);
            renderActions(inv);
        } catch (err) {
            showError(err.message || 'Gagal memuat detail invoice.');
            infoGrid.innerHTML = `<p style="color:#9ca3af;font-size:13px">Data tidak dapat dimuat.</p>`;
            itemsBody.innerHTML = `<tr><td colspan="6" style="text-align:center;color:#9ca3af;padding:24px 0">Data tidak tersedia.</td></tr>`;
            timelineList.innerHTML = `<li><span class="dot"></span><div>Tidak ada riwayat.</div></li>`;
        }
    }

    function isOverdue(inv) {
        return inv.status !== 'PAID' && inv.due_date && new Date(inv.due_date) < new Date(new Date().toDateString());
    }

    function renderHeader(inv) {
        const overdue = isOverdue(inv);
        const statusDisplay = overdue ? 'OVERDUE' : inv.status;
        headBadge.innerHTML = badgeHtml(statusDisplay);
        headSubtitle.textContent = `${inv.nama_perusahaan || inv.customer_id} • Diterbitkan ${fmtDate(inv.invoice_date)}`;

        if (overdue) {
            const daysLate = Math.floor((new Date() - new Date(inv.due_date)) / 86400000);
            overdueBannerText.textContent = `Invoice ini telah melewati jatuh tempo ${daysLate} hari (jatuh tempo: ${fmtDate(inv.due_date)}).`;
            overdueBanner.style.display = 'flex';
        } else {
            overdueBanner.style.display = 'none';
        }
    }

    function renderInfo(inv) {
        infoGrid.innerHTML = `
            <div class="tb-detail-item"><label>Buyer</label><span>${escHtml(inv.nama_perusahaan || '—')}</span></div>
            <div class="tb-detail-item"><label>Customer ID</label><span class="mono">${escHtml(inv.customer_id)}</span></div>
            <div class="tb-detail-item"><label>Tanggal Invoice</label><span>${fmtDate(inv.invoice_date)}</span></div>
            <div class="tb-detail-item"><label>Jatuh Tempo</label><span style="${isOverdue(inv) ? 'color:#b91c1c' : ''}">${fmtDate(inv.due_date)}</span></div>
            <div class="tb-detail-item"><label>Tenor</label><span>${inv.credit_days ? inv.credit_days + ' hari' : '—'}</span></div>
            <div class="tb-detail-item"><label>Dibuat Oleh</label><span>${escHtml(inv.created_by || '—')}</span></div>
            <div class="tb-detail-item"><label>Dibuat Pada</label><span>${fmtDateTime(inv.created_at)}</span></div>
            <div class="tb-detail-item"><label>No. WhatsApp Buyer</label><span>${escHtml(inv.no_whatsapp || '—')}</span></div>
        `;
    }

    function renderItems(items, inv) {
        if (items.length === 0) {
            itemsBody.innerHTML = `<tr><td colspan="6" style="text-align:center;color:#9ca3af;padding:24px 0">Tidak ada item pada invoice ini.</td></tr>`;
            itemsFoot.innerHTML = '';
        } else {
            itemsBody.innerHTML = items.map(it => `<tr>
                <td style="font-family:'DM Mono',monospace;font-size:12px;color:#4338ca">${escHtml(it.sj_no || '—')}</td>
                <td>${escHtml(it.item_no || '—')}</td>
                <td>${escHtml(it.colour_no || '—')}</td>
                <td class="is-num">${Number(it.qty || 0).toLocaleString('id-ID')} ${escHtml(it.unit || '')}</td>
                <td class="is-num">${formatRupiah(it.price_idr)}</td>
                <td class="is-num">${formatRupiah(it.amount_idr)}</td>
            </tr>`).join('');

            itemsFoot.innerHTML = `
                <tr><td colspan="5" style="text-align:right">Subtotal</td><td class="is-num">${formatRupiah(inv.subtotal_idr)}</td></tr>
                <tr><td colspan="5" style="text-align:right">PPN (${inv.ppn_pct ?? 11}%)</td><td class="is-num">${formatRupiah(inv.ppn_idr)}</td></tr>
                <tr><td colspan="5" style="text-align:right;font-size:13.5px">Total</td><td class="is-num" style="font-size:13.5px;color:#4338ca">${formatRupiah(inv.total_idr)}</td></tr>
            `;
        }

        sumSubtotal.textContent = formatRupiah(inv.subtotal_idr);
        sumPpn.textContent      = formatRupiah(inv.ppn_idr);
        sumTotal.textContent    = formatRupiah(inv.total_idr);
    }

    function renderTimeline(history, inv) {
        const entries = history.length > 0 ? history : [
            { label: 'Invoice dibuat', tanggal: inv.created_at },
        ];
        timelineList.innerHTML = entries.map(h => `
            <li><span class="dot"></span>
                <div>
                    <div>${escHtml(h.label || h.status || '—')}</div>
                    <div class="t">${fmtDateTime(h.tanggal || h.created_at)}</div>
                </div>
            </li>
        `).join('');
    }

    function renderActions(inv) {
        btnMarkPaid.style.display  = inv.status !== 'PAID' ? 'inline-flex' : 'none';
        btnCancelInv.style.display = inv.status === 'DRAFT' ? 'inline-flex' : 'none';

        btnMarkPaid.onclick = () => confirmAction(
            'Tandai Lunas',
            `Tandai invoice "${INVOICE_ID}" sebagai lunas?`,
            'Tindakan ini akan mengubah status invoice menjadi PAID.',
            'tb-btn-success', 'Ya, Tandai Lunas',
            () => callAction(`${ADMIN_URL}/fetch-data/markInvoicePaid.php`, { invoice_id: INVOICE_ID }, 'Invoice berhasil ditandai lunas.')
        );

        btnCancelInv.onclick = () => confirmAction(
            'Batalkan Invoice',
            `Batalkan invoice "${INVOICE_ID}"?`,
            'Surat jalan terkait akan dikembalikan ke status belum ditagih.',
            'tb-btn-danger', 'Ya, Batalkan',
            () => callAction(`${ADMIN_URL}/fetch-data/cancelInvoice.php`, { invoice_id: INVOICE_ID }, 'Invoice berhasil dibatalkan.')
        );
    }

    /* ── Cetak ─────────────────────────────────────────────── */
    btnPrint.addEventListener('click', () => {
        window.open(`${ADMIN_URL}/print/invoice.php?invoice_id=${encodeURIComponent(INVOICE_ID)}`, '_blank');
    });

    /* ── Modal konfirmasi generik ──────────────────────────── */
    function confirmAction(title, message, note, btnClass, btnLabel, onConfirm) {
        confirmTitle.textContent = title;
        confirmMsg.textContent   = message;
        confirmNote.textContent  = note;
        confirmError.classList.remove('show');
        btnDoConfirm.className   = `tb-btn ${btnClass}`;
        btnDoConfirm.textContent = btnLabel;
        btnDoConfirm.onclick     = onConfirm;
        openModal(modalConfirm);
    }

    async function callAction(url, payload, successMsg) {
        setConfirmLoading(true);
        confirmError.classList.remove('show');
        try {
            const res  = await fetch(url, {
                method     : 'POST',
                credentials: 'same-origin',
                headers    : { 'Content-Type': 'application/json' },
                body       : JSON.stringify(payload),
            });
            const json = await res.json();
            if (json.status === 'error') throw new Error(json.message);
            closeModal(modalConfirm);
            showSuccess(successMsg);
            loadDetail();
        } catch (err) {
            confirmErrTxt.textContent = err.message || 'Gagal memproses permintaan.';
            confirmError.classList.add('show');
        } finally {
            setConfirmLoading(false);
        }
    }

    function setConfirmLoading(loading) {
        btnDoConfirm.disabled = loading;
        btnCancel.disabled    = loading;
        if (loading) btnDoConfirm.innerHTML = '<i class="bi bi-hourglass-split"></i> Memproses…';
    }

    function openModal(el) { el.classList.add('open'); document.body.style.overflow = 'hidden'; }
    function closeModal(el) { el.classList.remove('open'); document.body.style.overflow = ''; }

    [btnCancel, btnCloseConf].forEach(b => b.addEventListener('click', () => closeModal(modalConfirm)));
    modalConfirm.addEventListener('click', e => { if (e.target === modalConfirm) closeModal(modalConfirm); });
    document.addEventListener('keydown', e => { if (e.key === 'Escape') closeModal(modalConfirm); });

    /* ── Init ──────────────────────────────────────────────── */
    loadDetail();

})();
</script>

<?php
$extraJs = [];
include __DIR__ . '/partials/_footer.php';
?>