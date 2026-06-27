<?php
// ============================================================
//  admin_panel/dashboard.php
//  Dashboard utama panel Admin ThreadB2B.
// ============================================================
$pageTitle  = 'Dashboard';
$activePage = 'dashboard';

require_once __DIR__ . '/partials/config.php';
?>
<?php include __DIR__ . '/partials/_header.php'; ?>

<style>
    /* ---- Dashboard-only styles (pindahkan ke style.css jika sudah stabil) ---- */
    .tb-dash-greeting{margin-bottom:24px}
    .tb-dash-greeting h2{font-size:22px;font-weight:700;margin:0 0 4px;color:#1a1d29}
    .tb-dash-greeting p{font-size:14px;color:#6b7280;margin:0}

    .tb-stat-grid{display:grid;grid-template-columns:repeat(5,1fr);gap:16px;margin-bottom:28px}
    @media (max-width:1200px){.tb-stat-grid{grid-template-columns:repeat(3,1fr)}}
    @media (max-width:768px){.tb-stat-grid{grid-template-columns:repeat(2,1fr)}}
    @media (max-width:480px){.tb-stat-grid{grid-template-columns:1fr}}

    .tb-stat-card{background:#fff;border:1px solid #e9eaee;border-radius:14px;padding:18px;
        display:flex;flex-direction:column;gap:10px;position:relative;overflow:hidden}
    .tb-stat-card__icon{width:38px;height:38px;border-radius:10px;display:flex;align-items:center;
        justify-content:center;font-size:18px}
    .tb-stat-card__icon.is-revenue{background:#eef2ff;color:#4338ca}
    .tb-stat-card__icon.is-orders{background:#ecfdf5;color:#047857}
    .tb-stat-card__icon.is-overdue{background:#fef2f2;color:#b91c1c}
    .tb-stat-card__icon.is-samples{background:#fffbeb;color:#b45309}
    .tb-stat-card__icon.is-today{background:#eff6ff;color:#1d4ed8}
    .tb-stat-card__label{font-size:12.5px;color:#6b7280;font-weight:600;text-transform:uppercase;letter-spacing:.02em}
    .tb-stat-card__value{font-size:24px;font-weight:700;color:#111827;font-family:'DM Mono',monospace;
        min-height:30px;display:flex;align-items:center}
    .tb-stat-card__value.is-loading{color:#c7cad1}
    .tb-stat-card__hint{font-size:12px;color:#9ca3af}

    .tb-dash-row{display:grid;grid-template-columns:1.4fr 1fr;gap:20px}
    @media (max-width:992px){.tb-dash-row{grid-template-columns:1fr}}

    .tb-panel{background:#fff;border:1px solid #e9eaee;border-radius:14px;padding:20px}
    .tb-panel__head{display:flex;align-items:center;justify-content:space-between;margin-bottom:16px}
    .tb-panel__head h3{font-size:15px;font-weight:700;margin:0;color:#1a1d29}
    .tb-panel__action{font-size:12.5px;color:#4338ca;background:none;border:none;font-weight:600;cursor:pointer}
    .tb-panel__action:hover{text-decoration:underline}

    .tb-inv-row{display:flex;align-items:center;gap:12px;padding:10px 0;border-bottom:1px solid #f1f2f5}
    .tb-inv-row:last-child{border-bottom:none}
    .tb-inv-row__dot{width:9px;height:9px;border-radius:50%;flex-shrink:0}
    .tb-inv-row__label{flex:1;font-size:13.5px;color:#374151;font-weight:500}
    .tb-inv-row__count{font-size:14px;font-weight:700;color:#111827;font-family:'DM Mono',monospace}

    .tb-quicklink{display:flex;align-items:center;gap:12px;padding:12px 14px;border:1px solid #ececf0;
        border-radius:10px;text-decoration:none;color:#1a1d29;margin-bottom:10px;transition:.15s}
    .tb-quicklink:hover{background:#f7f7fa;border-color:#dadbe2}
    .tb-quicklink i{font-size:16px;color:#4338ca}
    .tb-quicklink span{font-size:13.5px;font-weight:600}

    .tb-dash-alert{display:none;align-items:center;gap:10px;background:#fef2f2;color:#991b1b;
        border:1px solid #fecaca;border-radius:10px;padding:10px 14px;font-size:13px;margin-bottom:18px}
    .tb-dash-alert.show{display:flex}

    .tb-skeleton{background:linear-gradient(90deg,#eee 25%,#f5f5f5 37%,#eee 63%);
        background-size:400% 100%;animation:tbShimmer 1.4s ease infinite;border-radius:6px;width:60px;height:18px}
    @keyframes tbShimmer{0%{background-position:100% 50%}100%{background-position:0 50%}}
</style>

<div class="tb-layout">
    <?php include __DIR__ . '/partials/_sidebar.php'; ?>

    <main class="tb-main">
        <?php include __DIR__ . '/partials/_navbar.php'; ?>

        <div style="padding:24px;">

            <div class="tb-dash-greeting">
                <h2>Selamat datang, <?= htmlspecialchars(explode('@', $currentAdmin['email'])[0]) ?> 👋</h2>
                <p id="dashDateToday">Memuat ringkasan hari ini...</p>
            </div>

            <div class="tb-dash-alert" id="dashError">
                <i class="bi bi-exclamation-triangle-fill"></i>
                <span id="dashErrorText">Gagal memuat data dashboard.</span>
            </div>

            <!-- ── Stat cards ─────────────────────────────────── -->
            <div class="tb-stat-grid" id="statGrid">

                <div class="tb-stat-card">
                    <div class="tb-stat-card__icon is-revenue"><i class="bi bi-cash-stack"></i></div>
                    <span class="tb-stat-card__label">Revenue Bulan Ini</span>
                    <div class="tb-stat-card__value is-loading" data-field="revenue_bulan_ini">
                        <span class="tb-skeleton"></span>
                    </div>
                    <span class="tb-stat-card__hint">Invoice berstatus PAID</span>
                </div>

                <div class="tb-stat-card">
                    <div class="tb-stat-card__icon is-orders"><i class="bi bi-bag-check"></i></div>
                    <span class="tb-stat-card__label">Orders Aktif</span>
                    <div class="tb-stat-card__value is-loading" data-field="total_orders_aktif">
                        <span class="tb-skeleton"></span>
                    </div>
                    <span class="tb-stat-card__hint">Pending + Processing</span>
                </div>

                <div class="tb-stat-card">
                    <div class="tb-stat-card__icon is-overdue"><i class="bi bi-exclamation-octagon"></i></div>
                    <span class="tb-stat-card__label">Buyer Overdue</span>
                    <div class="tb-stat-card__value is-loading" data-field="total_buyer_overdue">
                        <span class="tb-skeleton"></span>
                    </div>
                    <span class="tb-stat-card__hint">Diblokir karena invoice telat</span>
                </div>

                <div class="tb-stat-card">
                    <div class="tb-stat-card__icon is-samples"><i class="bi bi-palette"></i></div>
                    <span class="tb-stat-card__label">Sampel Pending</span>
                    <div class="tb-stat-card__value is-loading" data-field="total_sampel_pending">
                        <span class="tb-skeleton"></span>
                    </div>
                    <span class="tb-stat-card__hint">Menunggu diproses</span>
                </div>

                <div class="tb-stat-card">
                    <div class="tb-stat-card__icon is-today"><i class="bi bi-calendar-event"></i></div>
                    <span class="tb-stat-card__label">Order Hari Ini</span>
                    <div class="tb-stat-card__value is-loading" data-field="order_hari_ini">
                        <span class="tb-skeleton"></span>
                    </div>
                    <span class="tb-stat-card__hint">Pesanan masuk hari ini</span>
                </div>

            </div>

            <!-- ── Invoice summary + Quick links ─────────────────── -->
            <div class="tb-dash-row">

                <div class="tb-panel">
                    <div class="tb-panel__head">
                        <h3>Ringkasan Status Invoice</h3>
                        <button class="tb-panel__action" id="btnRefreshStats">
                            <i class="bi bi-arrow-clockwise"></i> Refresh
                        </button>
                    </div>
                    <div id="invoiceSummaryList">
                        <div class="tb-inv-row"><span class="tb-skeleton" style="width:100%;height:14px"></span></div>
                        <div class="tb-inv-row"><span class="tb-skeleton" style="width:100%;height:14px"></span></div>
                        <div class="tb-inv-row"><span class="tb-skeleton" style="width:100%;height:14px"></span></div>
                    </div>
                </div>

                <div class="tb-panel">
                    <div class="tb-panel__head">
                        <h3>Aksi Cepat</h3>
                    </div>

                    <a href="<?= ADMIN_URL ?>/buyers.php?status=pending" class="tb-quicklink">
                        <i class="bi bi-person-check"></i>
                        <span>Verifikasi Buyer Baru</span>
                    </a>
                    <a href="<?= ADMIN_URL ?>/invoices.php?status=ISSUED" class="tb-quicklink">
                        <i class="bi bi-receipt-cutoff"></i>
                        <span>Invoice Belum Lunas</span>
                    </a>
                    <a href="<?= ADMIN_URL ?>/samples.php?status=pending" class="tb-quicklink">
                        <i class="bi bi-palette"></i>
                        <span>Permintaan Sampel Pending</span>
                    </a>
                    <button type="button" class="tb-quicklink" style="width:100%;border:1px solid #ececf0;background:#fff;cursor:pointer;text-align:left" id="btnCheckOverdue">
                        <i class="bi bi-clock-history"></i>
                        <span>Cek &amp; Tandai Invoice Overdue</span>
                    </button>
                </div>

            </div>

        </div>

        <script>
        (function () {
            const ADMIN_URL = '<?= ADMIN_URL ?>';

            const dashDateEl   = document.getElementById('dashDateToday');
            const errorBox     = document.getElementById('dashError');
            const errorText    = document.getElementById('dashErrorText');
            const invoiceList  = document.getElementById('invoiceSummaryList');
            const btnRefresh   = document.getElementById('btnRefreshStats');
            const btnOverdue   = document.getElementById('btnCheckOverdue');

            const STATUS_META = {
                DRAFT:   { label: 'Draft',          color: '#9ca3af' },
                ISSUED:  { label: 'Belum Dibayar',  color: '#2563eb' },
                PAID:    { label: 'Lunas',          color: '#16a34a' },
                OVERDUE: { label: 'Jatuh Tempo',    color: '#dc2626' },
            };

            function formatRupiah(num) {
                return 'Rp ' + Number(num || 0).toLocaleString('id-ID', { maximumFractionDigits: 0 });
            }

            function showError(msg) {
                errorText.textContent = msg;
                errorBox.classList.add('show');
            }
            function hideError() {
                errorBox.classList.remove('show');
            }

            function renderStats(data) {
                const setVal = (field, text) => {
                    const el = document.querySelector(`[data-field="${field}"]`);
                    if (!el) return;
                    el.textContent = text;
                    el.classList.remove('is-loading');
                };

                setVal('revenue_bulan_ini',    formatRupiah(data.revenue_bulan_ini));
                setVal('total_orders_aktif',   data.total_orders_aktif);
                setVal('total_buyer_overdue',  data.total_buyer_overdue);
                setVal('total_sampel_pending', data.total_sampel_pending);
                setVal('order_hari_ini',       data.order_hari_ini);

                // Invoice summary
                const summary = data.invoice_summary || {};
                const keys = Object.keys(summary);
                if (keys.length === 0) {
                    invoiceList.innerHTML = '<p style="font-size:13px;color:#9ca3af;margin:0">Belum ada data invoice.</p>';
                } else {
                    invoiceList.innerHTML = keys.map(status => {
                        const meta = STATUS_META[status] || { label: status, color: '#6b7280' };
                        return `
                            <div class="tb-inv-row">
                                <span class="tb-inv-row__dot" style="background:${meta.color}"></span>
                                <span class="tb-inv-row__label">${meta.label}</span>
                                <span class="tb-inv-row__count">${summary[status]}</span>
                            </div>`;
                    }).join('');
                }
            }

            async function loadStats() {
                hideError();
                try {
                    const res  = await fetch(`${ADMIN_URL}/fetch-data/fetchDashboardStats.php`, {
                        credentials: 'same-origin',
                    });
                    const json = await res.json();

                    if (json.success === false || json.status === 'error') {
                        throw new Error(json.message || 'Gagal memuat statistik.');
                    }
                    renderStats(json.data || json);
                } catch (err) {
                    showError(err.message || 'Terjadi kesalahan saat memuat dashboard.');
                }
            }

            async function checkOverdue() {
                btnOverdue.disabled = true;
                const originalHtml = btnOverdue.innerHTML;
                btnOverdue.innerHTML = '<i class="bi bi-hourglass-split"></i><span>Memeriksa...</span>';

                try {
                    const res  = await fetch(`${ADMIN_URL}/fetch-data/checkOverdueBuyers.php`, {
                        credentials: 'same-origin',
                    });
                    const json = await res.json();
                    alert(json.message || 'Pemeriksaan selesai.');
                    loadStats();
                } catch (err) {
                    alert('Gagal menjalankan pemeriksaan overdue.');
                } finally {
                    btnOverdue.disabled = false;
                    btnOverdue.innerHTML = originalHtml;
                }
            }

            dashDateEl.textContent = new Date().toLocaleDateString('id-ID', {
                weekday: 'long', day: 'numeric', month: 'long', year: 'numeric',
            });

            btnRefresh?.addEventListener('click', loadStats);
            btnOverdue?.addEventListener('click', checkOverdue);

            loadStats();
        })();
        </script>

<?php
$extraJs = []; // tambahkan 'dashboard.js' di sini jika logikanya dipindah ke file terpisah
include __DIR__ . '/partials/_footer.php';
?>