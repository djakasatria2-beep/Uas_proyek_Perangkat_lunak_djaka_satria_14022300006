<?php
// ============================================================
//  buyer_panel/partials/_header.php
//  Header / topbar untuk panel Buyer.
//  Membutuhkan variabel dari config.php: $currentBuyer, $hasOverdue, BUYER_URL
//  Di-include setelah config.php pada setiap halaman Buyer.
// ============================================================

$namaPerusahaan = $currentBuyer['nama_perusahaan'] ?? 'Buyer';
$namaPic         = $currentBuyer['nama_pic'] ?? '';
$statusVerif     = $currentBuyer['status_verifikasi'] ?? 'pending';

// Inisial untuk avatar (dari nama perusahaan)
$inisial = mb_strtoupper(mb_substr($namaPerusahaan, 0, 1));

// Label & warna badge status verifikasi
$statusBadge = [
    'approved' => ['label' => 'Terverifikasi', 'class' => 'badge-verified'],
    'pending'  => ['label' => 'Menunggu Verifikasi', 'class' => 'badge-pending'],
    'rejected' => ['label' => 'Ditolak', 'class' => 'badge-rejected'],
    'blocked'  => ['label' => 'Diblokir', 'class' => 'badge-rejected'],
][$statusVerif] ?? ['label' => 'Menunggu Verifikasi', 'class' => 'badge-pending'];
?>
<header class="bp-header" id="bpHeader">
    <div class="bp-header__left">
        <!-- Tombol toggle sidebar (mobile & desktop collapse) -->
        <button type="button" class="bp-icon-btn bp-header__menu-btn" id="bpSidebarToggle" aria-label="Buka/tutup menu" aria-controls="bpSidebar" aria-expanded="false">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="22" height="22">
                <line x1="3" y1="6" x2="21" y2="6"></line>
                <line x1="3" y1="12" x2="21" y2="12"></line>
                <line x1="3" y1="18" x2="21" y2="18"></line>
            </svg>
        </button>

        <a href="<?= BUYER_URL ?>/dashboard.php" class="bp-header__brand">
            <span class="bp-header__brand-text">Buyer Panel</span>
        </a>
    </div>

    <div class="bp-header__right">
        <!-- Status verifikasi (desktop) -->
        <span class="bp-badge <?= $statusBadge['class'] ?> bp-header__badge-desktop">
            <?= htmlspecialchars($statusBadge['label']) ?>
        </span>

        <!-- Notifikasi overdue -->
        <div class="bp-header__notif">
            <button type="button" class="bp-icon-btn" id="bpNotifBtn" aria-label="Notifikasi" aria-haspopup="true" aria-expanded="false">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="22" height="22">
                    <path d="M18 8a6 6 0 0 0-12 0c0 7-3 9-3 9h18s-3-2-3-9"></path>
                    <path d="M13.73 21a2 2 0 0 1-3.46 0"></path>
                </svg>
                <?php if ($hasOverdue): ?>
                    <span class="bp-notif-dot" aria-hidden="true"></span>
                <?php endif; ?>
            </button>

            <div class="bp-header__notif-panel" id="bpNotifPanel">
                <div class="bp-header__notif-title">Notifikasi</div>
                <?php if ($hasOverdue): ?>
                    <a href="<?= BUYER_URL ?>/invoices.php?status=overdue" class="bp-header__notif-item bp-header__notif-item--alert">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="18" height="18">
                            <circle cx="12" cy="12" r="10"></circle>
                            <line x1="12" y1="8" x2="12" y2="12"></line>
                            <line x1="12" y1="16" x2="12.01" y2="16"></line>
                        </svg>
                        <span>Anda memiliki invoice yang telah lewat jatuh tempo.</span>
                    </a>
                <?php else: ?>
                    <div class="bp-header__notif-empty">Tidak ada notifikasi baru.</div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Dropdown profil -->
        <div class="bp-header__profile">
            <button type="button" class="bp-header__profile-btn" id="bpProfileBtn" aria-haspopup="true" aria-expanded="false">
                <span class="bp-avatar"><?= htmlspecialchars($inisial) ?></span>
                <span class="bp-header__profile-info">
                    <span class="bp-header__profile-name"><?= htmlspecialchars($namaPerusahaan) ?></span>
                    <span class="bp-header__profile-pic"><?= htmlspecialchars($namaPic) ?></span>
                </span>
                <svg class="bp-header__chevron" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="16" height="16">
                    <polyline points="6 9 12 15 18 9"></polyline>
                </svg>
            </button>

            <div class="bp-header__profile-menu" id="bpProfileMenu">
                <span class="bp-badge <?= $statusBadge['class'] ?> bp-header__badge-mobile">
                    <?= htmlspecialchars($statusBadge['label']) ?>
                </span>
                <a href="<?= BUYER_URL ?>/profile.php" class="bp-header__profile-menu-item">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="18" height="18">
                        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                        <circle cx="12" cy="7" r="4"></circle>
                    </svg>
                    <span>Profil Saya</span>
                </a>
                <a href="<?= BUYER_URL ?>/settings.php" class="bp-header__profile-menu-item">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="18" height="18">
                        <circle cx="12" cy="12" r="3"></circle>
                        <path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 1 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 1 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 1 1-2.83-2.83l.06-.06A1.65 1.65 0 0 0 4.6 15a1.65 1.65 0 0 0-1.51-1H3a2 2 0 1 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 1 1 2.83-2.83l.06.06A1.65 1.65 0 0 0 9 4.6a1.65 1.65 0 0 0 1-1.51V3a2 2 0 1 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 1 1 2.83 2.83l-.06.06A1.65 1.65 0 0 0 19.4 9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 1 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"></path>
                    </svg>
                    <span>Pengaturan</span>
                </a>
                <div class="bp-header__profile-menu-divider"></div>
                <a href="<?= SITE_URL ?>/logout.php" class="bp-header__profile-menu-item bp-header__profile-menu-item--danger">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="18" height="18">
                        <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
                        <polyline points="16 17 21 12 16 7"></polyline>
                        <line x1="21" y1="12" x2="9" y2="12"></line>
                    </svg>
                    <span>Keluar</span>
                </a>
            </div>
        </div>
    </div>
</header>

<style>
    /* ====== Variabel warna ====== */
    :root {
        --bp-primary: #2563eb;
        --bp-primary-dark: #1d4ed8;
        --bp-border: #e5e7eb;
        --bp-bg: #ffffff;
        --bp-text: #111827;
        --bp-text-muted: #6b7280;
        --bp-danger: #dc2626;
        --bp-danger-bg: #fef2f2;
        --bp-warning-bg: #fffbeb;
        --bp-warning-text: #b45309;
        --bp-success-bg: #ecfdf5;
        --bp-success-text: #047857;
        --bp-rejected-bg: #fef2f2;
        --bp-rejected-text: #b91c1c;
    }

    .bp-header {
        position: sticky;
        top: 0;
        z-index: 40;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        height: 64px;
        padding: 0 16px;
        background: var(--bp-bg);
        border-bottom: 1px solid var(--bp-border);
    }

    .bp-header__left {
        display: flex;
        align-items: center;
        gap: 10px;
        min-width: 0;
    }

    .bp-icon-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 38px;
        height: 38px;
        border: none;
        background: transparent;
        border-radius: 8px;
        color: var(--bp-text);
        cursor: pointer;
        position: relative;
        flex-shrink: 0;
        transition: background 0.15s ease;
    }
    .bp-icon-btn:hover { background: #f3f4f6; }

    .bp-header__brand {
        display: flex;
        align-items: center;
        text-decoration: none;
        color: var(--bp-text);
        min-width: 0;
    }
    .bp-header__brand-text {
        font-weight: 700;
        font-size: 16px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .bp-header__right {
        display: flex;
        align-items: center;
        gap: 6px;
        flex-shrink: 0;
    }

    /* Badge status */
    .bp-badge {
        display: inline-flex;
        align-items: center;
        font-size: 12px;
        font-weight: 600;
        padding: 5px 10px;
        border-radius: 999px;
        white-space: nowrap;
    }
    .badge-verified { background: var(--bp-success-bg); color: var(--bp-success-text); }
    .badge-pending  { background: var(--bp-warning-bg); color: var(--bp-warning-text); }
    .badge-rejected { background: var(--bp-rejected-bg); color: var(--bp-rejected-text); }

    .bp-header__badge-mobile { display: none; }

    /* Notifikasi */
    .bp-header__notif { position: relative; }
    .bp-notif-dot {
        position: absolute;
        top: 6px;
        right: 6px;
        width: 8px;
        height: 8px;
        background: var(--bp-danger);
        border-radius: 50%;
        border: 2px solid var(--bp-bg);
    }
    .bp-header__notif-panel {
        display: none;
        position: absolute;
        top: calc(100% + 8px);
        right: 0;
        width: 300px;
        background: var(--bp-bg);
        border: 1px solid var(--bp-border);
        border-radius: 12px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.12);
        padding: 10px;
        z-index: 50;
    }
    .bp-header__notif-panel.is-open { display: block; }
    .bp-header__notif-title {
        font-size: 13px;
        font-weight: 700;
        color: var(--bp-text);
        padding: 6px 8px 8px;
    }
    .bp-header__notif-item {
        display: flex;
        align-items: flex-start;
        gap: 8px;
        padding: 10px 8px;
        border-radius: 8px;
        text-decoration: none;
        font-size: 13px;
        color: var(--bp-text);
    }
    .bp-header__notif-item--alert { color: var(--bp-danger); }
    .bp-header__notif-item:hover { background: #f9fafb; }
    .bp-header__notif-empty {
        padding: 14px 8px;
        font-size: 13px;
        color: var(--bp-text-muted);
        text-align: center;
    }

    /* Profil */
    .bp-header__profile { position: relative; }
    .bp-header__profile-btn {
        display: flex;
        align-items: center;
        gap: 8px;
        border: none;
        background: transparent;
        padding: 6px 8px;
        border-radius: 10px;
        cursor: pointer;
        max-width: 220px;
        transition: background 0.15s ease;
    }
    .bp-header__profile-btn:hover { background: #f3f4f6; }

    .bp-avatar {
        flex-shrink: 0;
        width: 34px;
        height: 34px;
        border-radius: 50%;
        background: var(--bp-primary);
        color: #fff;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 14px;
    }

    .bp-header__profile-info {
        display: flex;
        flex-direction: column;
        align-items: flex-start;
        min-width: 0;
        text-align: left;
    }
    .bp-header__profile-name {
        font-size: 13px;
        font-weight: 600;
        color: var(--bp-text);
        max-width: 140px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .bp-header__profile-pic {
        font-size: 12px;
        color: var(--bp-text-muted);
        max-width: 140px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .bp-header__chevron { color: var(--bp-text-muted); flex-shrink: 0; }

    .bp-header__profile-menu {
        display: none;
        position: absolute;
        top: calc(100% + 8px);
        right: 0;
        width: 230px;
        background: var(--bp-bg);
        border: 1px solid var(--bp-border);
        border-radius: 12px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.12);
        padding: 8px;
        z-index: 50;
    }
    .bp-header__profile-menu.is-open { display: block; }

    .bp-header__profile-menu-item {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 9px 10px;
        border-radius: 8px;
        text-decoration: none;
        font-size: 13.5px;
        color: var(--bp-text);
    }
    .bp-header__profile-menu-item:hover { background: #f9fafb; }
    .bp-header__profile-menu-item--danger { color: var(--bp-danger); }
    .bp-header__profile-menu-item--danger:hover { background: var(--bp-danger-bg); }
    .bp-header__profile-menu-divider {
        height: 1px;
        background: var(--bp-border);
        margin: 6px 4px;
    }

    /* ====== Responsive ====== */
    @media (max-width: 640px) {
        .bp-header { padding: 0 10px; gap: 6px; }
        .bp-header__brand-text { font-size: 14px; }
        .bp-header__badge-desktop { display: none; }
        .bp-header__badge-mobile {
            display: block;
            margin-bottom: 6px;
        }
        .bp-header__profile-info { display: none; }
        .bp-header__chevron { display: none; }
        .bp-header__profile-btn { padding: 6px; }
        .bp-header__notif-panel { width: 260px; right: -10px; }
        .bp-header__profile-menu { right: -10px; }
    }
</style>

<script>
(function () {
    function toggleDropdown(panelId, btnId, exclude) {
        var panel = document.getElementById(panelId);
        var btn = document.getElementById(btnId);
        if (!panel || !btn) return;

        btn.addEventListener('click', function (e) {
            e.stopPropagation();
            var willOpen = !panel.classList.contains('is-open');
            // Tutup dropdown lain
            exclude.forEach(function (id) {
                var el = document.getElementById(id);
                if (el) el.classList.remove('is-open');
            });
            panel.classList.toggle('is-open', willOpen);
            btn.setAttribute('aria-expanded', willOpen ? 'true' : 'false');
        });
    }

    toggleDropdown('bpNotifPanel', 'bpNotifBtn', ['bpProfileMenu']);
    toggleDropdown('bpProfileMenu', 'bpProfileBtn', ['bpNotifPanel']);

    document.addEventListener('click', function () {
        var notif = document.getElementById('bpNotifPanel');
        var profile = document.getElementById('bpProfileMenu');
        if (notif) notif.classList.remove('is-open');
        if (profile) profile.classList.remove('is-open');
    });

    // Toggle sidebar (bekerja sama dengan _sidebar.php: toggle class 'is-open' pada <body>)
    var sidebarToggle = document.getElementById('bpSidebarToggle');
    if (sidebarToggle) {
        sidebarToggle.addEventListener('click', function () {
            var isOpen = document.body.classList.toggle('bp-sidebar-open');
            sidebarToggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
        });
    }
})();
</script>