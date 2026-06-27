<?php
// ============================================================
//  ThreadB2B — admin_panel/invoices-new.php
//  Halaman buat invoice baru: pilih buyer, pilih surat jalan
//  yang belum ditagih, lalu generate invoice + invoice_items.
// ============================================================
$pageTitle  = 'Buat Invoice Baru';
$activePage = 'invoices';

require_once __DIR__ . '/partials/config.php';
?>
<?php include __DIR__ . '/partials/_header.php'; ?>

<style>
.tb-page-head{display:flex;align-items:center;justify-content:space-between;margin-bottom:22px;flex-wrap:wrap;gap:12px}
.tb-page-head h2{font-size:20px;font-weight:700;color:#1a1d29;margin:0}
.tb-page-head p{font-size:13.5px;color:#6b7280;margin:4px 0 0}
.tb-back-link{display:inline-flex;align-items:center;gap:6px;font-size:13px;font-weight:600;
    color:#6b7280;text-decoration:none;margin-bottom:14px}
.tb-back-link:hover{color:#4338ca}

/* Layout 2 kolom: form kiri, ringkasan kanan (sticky) */
.tb-new-layout{display:grid;grid-template-columns:1fr 340px;gap:20px;align-items:start}
@media(max-width:900px){.tb-new-layout{grid-template-columns:1fr}}

.tb-card{background:#fff;border:1px solid #e9eaee;border-radius:14px;padding:20px}
.tb-card + .tb-card{margin-top:16px}
.tb-card h3{font-size:14px;font-weight:700;color:#1a1d29;margin:0 0 14px;display:flex;align-items:center;gap:8px}
.tb-card h3 .step{width:22px;height:22px;border-radius:50%;background:#4338ca;color:#fff;font-size:11.5px;
    display:flex;align-items:center;justify-content:center;flex-shrink:0}

.tb-field{margin-bottom:14px}
.tb-field label{display:block;font-size:12px;font-weight:700;color:#6b7280;margin-bottom:6px}
.tb-field select, .tb-field input{width:100%;height:38px;border:1px solid #e0e1e6;border-radius:8px;
    padding:0 12px;font-size:13.5px;color:#1a1d29;outline:none;transition:.15s}
.tb-field select:focus, .tb-field input:focus{border-color:#4338ca;box-shadow:0 0 0 3px #eef2ff}
.tb-field .hint{font-size:11.5px;color:#9ca3af;margin-top:4px}
.tb-field-row{display:grid;grid-template-columns:1fr 1fr;gap:14px}
@media(max-width:520px){.tb-field-row{grid-template-columns:1fr}}

.tb-buyer-info{display:none;background:#f8f9fb;border-radius:10px;padding:12px 14px;margin-top:10px;font-size:12.5px;color:#374151}
.tb-buyer-info.show{display:block}
.tb-buyer-info b{color:#1a1d29}

/* SJ picker */
.tb-sj-toolbar{display:flex;align-items:center;justify-content:space-between;margin-bottom:8px}
.tb-sj-toolbar .left{font-size:12px;color:#9ca3af}
.tb-sj-toolbar button{background:none;border:none;color:#4338ca;font-size:12px;font-weight:600;cursor:pointer;padding:0}
.tb-sj-list{border:1px solid #f1f2f5;border-radius:10px;max-height:340px;overflow-y:auto}
.tb-sj-item{display:flex;align-items:flex-start;gap:10px;padding:11px 14px;border-bottom:1px solid #f8f9fb;cursor:pointer;transition:.1s}
.tb-sj-item:last-child{border-bottom:none}
.tb-sj-item:hover{background:#fafbfc}
.tb-sj-item input[type="checkbox"]{margin-top:3px;flex-shrink:0;width:16px;height:16px;accent-color:#4338ca;cursor:pointer}
.tb-sj-item .sj-main{flex:1;min-width:0}
.tb-sj-item .sj-no{font-family:'DM Mono',monospace;font-size:12.5px;color:#4338ca;font-weight:700}
.tb-sj-item .sj-meta{font-size:11.5px;color:#9ca3af;margin-top:2px}
.tb-sj-item .sj-qty{font-size:13px;font-weight:700;color:#1a1d29;white-space:nowrap;flex-shrink:0}

.tb-empty-state{text-align:center;padding:40px 16px;color:#9ca3af}
.tb-empty-state i{font-size:28px;display:block;margin-bottom:8px;opacity:.5}
.tb-empty-state p{margin:0;font-size:13px}

/* Ringkasan (kanan, sticky) */
.tb-summary-card{position:sticky;top:20px}
.tb-summary-row{display:flex;justify-content:space-between;align-items:center;font-size:13px;
    color:#374151;padding:8px 0;border-bottom:1px solid #f8f9fb}
.tb-summary-row:last-of-type{border-bottom:none}
.tb-summary-row .lbl{color:#6b7280}
.tb-summary-row .val{font-family:'DM Mono',monospace;font-weight:600}
.tb-summary-total{display:flex;justify-content:space-between;align-items:center;margin-top:10px;
    padding-top:14px;border-top:1px solid #e9eaee}
.tb-summary-total .lbl{font-size:13.5px;font-weight:700;color:#1a1d29}
.tb-summary-total .val{font-size:18px;font-weight:700;color:#4338ca;font-family:'DM Mono',monospace}

.tb-btn{height:40px;padding:0 18px;border-radius:8px;font-size:13.5px;font-weight:600;
    cursor:pointer;border:1px solid transparent;transition:.12s;display:inline-flex;align-items:center;
    justify-content:center;gap:6px;width:100%}
.tb-btn-primary{background:#4338ca;color:#fff;border-color:#4338ca}
.tb-btn-primary:hover{background:#3730a3}
.tb-btn-primary:disabled{opacity:.5;cursor:default}
.tb-btn-ghost{background:#fff;color:#374151;border-color:#e0e1e6}
.tb-btn-ghost:hover{border-color:#9ca3af}

.tb-inline-alert{display:none;align-items:center;gap:8px;padding:10px 14px;border-radius:8px;
    font-size:13px;margin-bottom:14px}
.tb-inline-alert.show{display:flex}
.tb-inline-alert.error{background:#fef2f2;color:#991b1b;border:1px solid #fecaca}
.tb-inline-alert.success{background:#ecfdf5;color:#065f46;border:1px solid #a7f3d0}

.tb-skel{height:46px;border-radius:8px;margin-bottom:6px;background:linear-gradient(90deg,#eee 25%,#f5f5f5 37%,#eee 63%);
    background-size:400% 100%;animation:tbShimmer 1.4s ease infinite}
@keyframes tbShimmer{0%{background-position:100% 50%}100%{background-position:0 50%}}
</style>

<div class="tb-layout">
    <?php include __DIR__ . '/partials/_sidebar.php'; ?>

    <main class="tb-main">
        <?php include __DIR__ . '/partials/_navbar.php'; ?>

        <div style="padding:24px;max-width:1100px">

            <a href="<?= ADMIN_URL ?>/invoices.php" class="tb-back-link">
                <i class="bi bi-arrow-left"></i> Kembali ke Manajemen Invoice
            </a>

            <div class="tb-page-head">
                <div>
                    <h2>Buat Invoice Baru</h2>
                    <p>Pilih buyer, lalu pilih surat jalan yang belum ditagih untuk dijadikan satu invoice.</p>
                </div>
            </div>

            <div class="tb-inline-alert error" id="newGlobalError">
                <i class="bi bi-exclamation-triangle-fill"></i>
                <span id="newGlobalErrorText"></span>
            </div>
            <div class="tb-inline-alert success" id="newGlobalSuccess">
                <i class="bi bi-check-circle-fill"></i>
                <span id="newGlobalSuccessText"></span>
            </div>

            <div class="tb-new-layout">

                <!-- ── Kolom kiri: form ──────────────────────── -->
                <div>
                    <div class="tb-card">
                        <h3><span class="step">1</span> Pilih Buyer</h3>
                        <div class="tb-field">
                            <label for="buyerSelect">Buyer / Customer ID</label>
                            <select id="buyerSelect">
                                <option value="">— Memuat daftar buyer —</option>
                            </select>
                        </div>
                        <div class="tb-buyer-info" id="buyerInfo"></div>
                    </div>

                    <div class="tb-card">
                        <h3><span class="step">2</span> Pilih Surat Jalan Belum Ditagih</h3>
                        <div class="tb-sj-toolbar">
                            <span class="left" id="sjCountLabel">0 SJ dipilih</span>
                            <button type="button" id="btnSelectAllSj">Pilih semua</button>
                        </div>
                        <div class="tb-sj-list" id="sjList">
                            <div style="padding:14px">
                                <div class="tb-skel" style="width:100%"></div>
                                <div class="tb-skel" style="width:100%"></div>
                                <div class="tb-skel" style="width:100%"></div>
                            </div>
                        </div>
                    </div>

                    <div class="tb-card">
                        <h3><span class="step">3</span> Ketentuan Pembayaran</h3>
                        <div class="tb-field-row">
                            <div class="tb-field">
                                <label for="invoiceDate">Tanggal Invoice</label>
                                <input type="date" id="invoiceDate">
                            </div>
                            <div class="tb-field">
                                <label for="creditDays">Tenor (hari)</label>
                                <input type="number" id="creditDays" min="1" value="30">
                                <div class="hint" id="dueDateHint">Jatuh tempo: —</div>
                            </div>
                        </div>
                        <div class="tb-field">
                            <label for="ppnPct">PPN (%)</label>
                            <input type="number" id="ppnPct" min="0" step="0.01" value="11">
                        </div>
                    </div>
                </div>

                <!-- ── Kolom kanan: ringkasan ────────────────── -->
                <div>
                    <div class="tb-card tb-summary-card">
                        <h3>Ringkasan Invoice</h3>
                        <div class="tb-summary-row"><span class="lbl">Buyer</span><span class="val" id="sumBuyer">—</span></div>
                        <div class="tb-summary-row"><span class="lbl">Jumlah SJ</span><span class="val" id="sumSjCount">0</span></div>
                        <div class="tb-summary-row"><span class="lbl">Subtotal</span><span class="val" id="sumSubtotal">Rp 0</span></div>
                        <div class="tb-summary-row"><span class="lbl">PPN</span><span class="val" id="sumPpn">Rp 0</span></div>
                        <div class="tb-summary-total"><span class="lbl">Total</span><span class="val" id="sumTotal">Rp 0</span></div>

                        <div style="margin-top:18px;display:flex;flex-direction:column;gap:8px">
                            <button class="tb-btn tb-btn-primary" id="btnSubmitInvoice" disabled>
                                <i class="bi bi-receipt"></i> Buat Invoice
                            </button>
                            <a href="<?= ADMIN_URL ?>/invoices.php" class="tb-btn tb-btn-ghost" style="text-decoration:none">Batal</a>
                        </div>
                    </div>
                </div>

            </div><!-- /tb-new-layout -->

        </div><!-- /padding -->

    </main>
</div>

<script>
(function () {
    'use strict';
    const ADMIN_URL = '<?= ADMIN_URL ?>';

    /* ── DOM refs ──────────────────────────────────────────── */
    const buyerSelect   = document.getElementById('buyerSelect');
    const buyerInfo     = document.getElementById('buyerInfo');
    const sjList         = document.getElementById('sjList');
    const sjCountLabel   = document.getElementById('sjCountLabel');
    const btnSelectAllSj = document.getElementById('btnSelectAllSj');
    const invoiceDate    = document.getElementById('invoiceDate');
    const creditDays     = document.getElementById('creditDays');
    const dueDateHint    = document.getElementById('dueDateHint');
    const ppnPct         = document.getElementById('ppnPct');
    const btnSubmit      = document.getElementById('btnSubmitInvoice');

    const sumBuyer    = document.getElementById('sumBuyer');
    const sumSjCount  = document.getElementById('sumSjCount');
    const sumSubtotal = document.getElementById('sumSubtotal');
    const sumPpn      = document.getElementById('sumPpn');
    const sumTotal     = document.getElementById('sumTotal');

    const globalError    = document.getElementById('newGlobalError');
    const globalErrText  = document.getElementById('newGlobalErrorText');
    const globalSuccess  = document.getElementById('newGlobalSuccess');
    const globalSucText  = document.getElementById('newGlobalSuccessText');

    let sjData = []; // daftar SJ yang ditampilkan untuk buyer terpilih

    /* ── Helpers ───────────────────────────────────────────── */
    function showError(msg) {
        globalSuccess.classList.remove('show');
        globalErrText.textContent = msg;
        globalError.classList.add('show');
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }
    function hideError() { globalError.classList.remove('show'); }
    function showSuccess(msg) {
        globalError.classList.remove('show');
        globalSucText.textContent = msg;
        globalSuccess.classList.add('show');
    }
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

    /* Default tanggal invoice = hari ini */
    invoiceDate.value = new Date().toISOString().slice(0, 10);

    /* ── Load daftar buyer aktif ───────────────────────────── */
    async function loadBuyers() {
        try {
            const res  = await fetch(`${ADMIN_URL}/fetch-data/fetchActiveBuyers.php`, { credentials: 'same-origin' });
            const json = await res.json();
            if (json.status === 'error') throw new Error(json.message);
            const buyers = json.data?.buyers || json.buyers || [];
            if (buyers.length === 0) {
                buyerSelect.innerHTML = '<option value="">Tidak ada buyer approved</option>';
                return;
            }
            buyerSelect.innerHTML = '<option value="">— Pilih buyer —</option>' +
                buyers.map(b => `<option value="${escHtml(b.id_buyer)}"
                    data-nama="${escHtml(b.nama_perusahaan)}"
                    data-pic="${escHtml(b.nama_pic || '')}"
                    data-wa="${escHtml(b.no_whatsapp || '')}"
                    data-tenor="${escHtml(b.tenor_hari || 30)}">
                    ${escHtml(b.nama_perusahaan)}
                </option>`).join('');
        } catch (err) {
            buyerSelect.innerHTML = '<option value="">Gagal memuat daftar buyer</option>';
            showError(err.message || 'Gagal memuat daftar buyer.');
        }
    }

    /* ── Saat buyer dipilih → tampilkan info & load SJ ─────── */
    buyerSelect.addEventListener('change', () => {
        const opt = buyerSelect.selectedOptions[0];
        hideError();

        if (!buyerSelect.value) {
            buyerInfo.classList.remove('show');
            renderEmptySj('Pilih buyer terlebih dahulu.');
            updateSummary();
            return;
        }

        buyerInfo.innerHTML = `
            <div><b>${escHtml(opt.dataset.nama)}</b></div>
            <div>PIC: ${escHtml(opt.dataset.pic || '—')} &nbsp;•&nbsp; WA: ${escHtml(opt.dataset.wa || '—')}</div>
        `;
        buyerInfo.classList.add('show');

        if (opt.dataset.tenor) creditDays.value = opt.dataset.tenor;
        updateDueDateHint();
        loadUninvoicedSj(buyerSelect.value);
    });

    function renderEmptySj(msg) {
        sjData = [];
        sjList.innerHTML = `<div class="tb-empty-state"><i class="bi bi-inbox"></i><p>${escHtml(msg)}</p></div>`;
        sjCountLabel.textContent = '0 SJ dipilih';
    }

    /* ── Load SJ belum ditagih untuk buyer ─────────────────── */
    async function loadUninvoicedSj(idBuyer) {
        sjList.innerHTML = `<div style="padding:14px">
            <div class="tb-skel" style="width:100%"></div>
            <div class="tb-skel" style="width:100%"></div>
            <div class="tb-skel" style="width:100%"></div>
        </div>`;
        try {
            const res  = await fetch(`${ADMIN_URL}/fetch-data/fetchUninvoicedSJ.php?id_buyer=${encodeURIComponent(idBuyer)}`, { credentials: 'same-origin' });
            const json = await res.json();
            if (json.status === 'error') throw new Error(json.message);
            sjData = (json.data?.sj_list || json.sj_list || []);

            if (sjData.length === 0) {
                renderEmptySj('Tidak ada surat jalan yang belum ditagih untuk buyer ini.');
                updateSummary();
                return;
            }

            sjList.innerHTML = sjData.map((sj, i) => `
                <label class="tb-sj-item">
                    <input type="checkbox" class="sj-checkbox" data-idx="${i}">
                    <div class="sj-main">
                        <div class="sj-no">${escHtml(sj.sj_no)}</div>
                        <div class="sj-meta">${fmtDate(sj.sj_date)} ${sj.po_no ? '• PO ' + escHtml(sj.po_no) : ''}</div>
                    </div>
                    <div class="sj-qty">${formatRupiah(sj.total_amount_idr)}</div>
                </label>
            `).join('');

            sjList.querySelectorAll('.sj-checkbox').forEach(cb => cb.addEventListener('change', updateSummary));
            updateSummary();
        } catch (err) {
            renderEmptySj(err.message || 'Gagal memuat surat jalan.');
            updateSummary();
        }
    }

    /* ── Pilih semua SJ ─────────────────────────────────────── */
    btnSelectAllSj.addEventListener('click', () => {
        const boxes = sjList.querySelectorAll('.sj-checkbox');
        if (boxes.length === 0) return;
        const allChecked = Array.from(boxes).every(b => b.checked);
        boxes.forEach(b => b.checked = !allChecked);
        btnSelectAllSj.textContent = allChecked ? 'Pilih semua' : 'Batalkan semua';
        updateSummary();
    });

    /* ── Hitung ulang ringkasan tiap ada perubahan ─────────── */
    function getSelectedSj() {
        const checked = Array.from(sjList.querySelectorAll('.sj-checkbox:checked'));
        return checked.map(cb => sjData[parseInt(cb.dataset.idx, 10)]);
    }

    function updateSummary() {
        const selected = getSelectedSj();
        const opt = buyerSelect.selectedOptions[0];

        sumBuyer.textContent   = opt && buyerSelect.value ? opt.dataset.nama : '—';
        sumSjCount.textContent = selected.length;
        sjCountLabel.textContent = `${selected.length} SJ dipilih`;

        const subtotal = selected.reduce((sum, sj) => sum + Number(sj.total_amount_idr || 0), 0);
        const pct      = parseFloat(ppnPct.value) || 0;
        const ppn      = subtotal * (pct / 100);
        const total    = subtotal + ppn;

        sumSubtotal.textContent = formatRupiah(subtotal);
        sumPpn.textContent      = formatRupiah(ppn);
        sumTotal.textContent    = formatRupiah(total);

        btnSubmit.disabled = !(buyerSelect.value && selected.length > 0);
    }

    ppnPct.addEventListener('input', updateSummary);
    creditDays.addEventListener('input', updateDueDateHint);
    invoiceDate.addEventListener('change', updateDueDateHint);

    function updateDueDateHint() {
        const base = invoiceDate.value ? new Date(invoiceDate.value) : new Date();
        const days = parseInt(creditDays.value, 10) || 0;
        const due  = new Date(base);
        due.setDate(due.getDate() + days);
        dueDateHint.textContent = 'Jatuh tempo: ' + due.toLocaleDateString('id-ID', { day:'numeric', month:'short', year:'numeric' });
    }

    /* ── Submit invoice ─────────────────────────────────────── */
    btnSubmit.addEventListener('click', async () => {
        hideError();
        globalSuccess.classList.remove('show');

        const selected = getSelectedSj();
        if (!buyerSelect.value) { showError('Pilih buyer terlebih dahulu.'); return; }
        if (selected.length === 0) { showError('Pilih minimal satu surat jalan untuk ditagih.'); return; }

        btnSubmit.disabled = true;
        btnSubmit.innerHTML = '<i class="bi bi-hourglass-split"></i> Memproses…';

        try {
            const res  = await fetch(`${ADMIN_URL}/fetch-data/createInvoice.php`, {
                method     : 'POST',
                credentials: 'same-origin',
                headers    : { 'Content-Type': 'application/json' },
                body       : JSON.stringify({
                    id_buyer     : buyerSelect.value,
                    invoice_date : invoiceDate.value,
                    credit_days  : parseInt(creditDays.value, 10) || 30,
                    ppn_pct      : parseFloat(ppnPct.value) || 0,
                    sj_list      : selected.map(sj => sj.sj_no),
                }),
            });
            const json = await res.json();
            if (json.status === 'error') throw new Error(json.message);

            const invoiceId = json.data?.invoice_id || json.invoice_id;
            showSuccess(`Invoice ${invoiceId ? escHtml(invoiceId) : ''} berhasil dibuat. Mengalihkan…`);
            setTimeout(() => {
                window.location.href = `${ADMIN_URL}/invoices.php`;
            }, 1200);
        } catch (err) {
            showError(err.message || 'Gagal membuat invoice.');
            btnSubmit.disabled = false;
            btnSubmit.innerHTML = '<i class="bi bi-receipt"></i> Buat Invoice';
        }
    });

    /* ── Init ──────────────────────────────────────────────── */
    renderEmptySj('Pilih buyer terlebih dahulu.');
    updateDueDateHint();
    loadBuyers();

})();
</script>

<?php
$extraJs = [];
include __DIR__ . '/partials/_footer.php';
?>