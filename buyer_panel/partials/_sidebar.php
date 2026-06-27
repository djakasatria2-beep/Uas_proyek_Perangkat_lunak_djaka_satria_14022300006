<?php
// ============================================================
//  buyer_panel/partials/_sidebar.php
//  Sidebar navigasi untuk panel Buyer.
//  Membutuhkan: BUYER_URL, SITE_URL (dari config.php)
//  Bekerja sama dengan _header.php: toggle via class 'bp-sidebar-open' pada <body>
// ============================================================

// Tentukan halaman aktif berdasarkan nama file saat ini
$bpCurrentPage = basename($_SERVER['SCRIPT_NAME']);

$bpMenuItems = [
    [
        'label' => 'Dashboard',
        'href'  => BUYER_URL . '/dashboard.php',
        'match' => ['dashboard.php'],
        'icon'  => '<rect x="3" y="3" width="7" height="9" rx="1.5"></rect><rect x="14" y="3" width="7" height="5" rx="1.5"></rect><rect x="14" y="12" width="7" height="9" rx="1.5"></rect><rect x="3" y="16" width="7" height="5" rx="1.5"></rect>',
    ],
    [
        'label' => 'Order',
        'href'  => BUYER_URL . '/orders.php',
        'match' => ['orders.php', 'orders-new.php', 'orders-detail.php'],
        'icon'  => '<path d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.3 2.3c-.6.6-.2 1.7.7 1.7H17"></path><circle cx="9" cy="20" r="1.5"></circle><circle cx="17" cy="20" r="1.5"></circle>',
    ],
    [
        'label' => 'Sample Request',
        'href'  => BUYER_URL . '/samples.php',
        'match' => ['samples.php', 'samples-new.php', 'samples-detail.php'],
        'icon'  => '<path d="M9 2v6L4 19a1 1 0 0 0 1 1h14a1 1 0 0 0 1-1L15 8V2"></path><line x1="9" y1="2" x2="15" y2="2"></line><line x1="7" y1="14" x2="17" y2="14"></line>',
    ],
    [
        'label' => 'Invoice / Tagihan',
        'href'  => BUYER_URL . '/invoices.php',
        'match' => ['invoices.php', 'invoices-detail.php'],
        'icon'  => '<path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="8" y1="13" x2="16" y2="13"></line><line x1="8" y1="17" x2="16" y2="17"></line>',
    ],
    [
        'label' => 'Return',
        'href'  => BUYER_URL . '/returns.php',
        'match' => ['returns.php', 'returns-new.php', 'returns-detail.php'],
        'icon'  => '<polyline points="1 4 1 10 7 10"></polyline><path d="M3.51 15a9 9 0 1 0 2.13-9.36L1 10"></path>',
    ],
    [
        'label' => 'Tracking',
        'href'  => BUYER_URL . '/tracking.php',
        'match' => ['tracking.php'],
        'icon'  => '<circle cx="12" cy="12" r="10"></circle><polygon points="16.24 7.76 14.12 14.12 7.76 16.24 9.88 9.88 16.24 7.76"></polygon>',
    ],
    [
        'label' => 'Profil',
        'href'  => BUYER_URL . '/profile.php',
        'match' => ['profile.php'],
        'icon'  => '<path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle>',
    ],
    [
        'label' => 'Pengaturan',
        'href'  => BUYER_URL . '/settings.php',
        'match' => ['settings.php', 'password.php'],
        'icon'  => '<circle cx="12" cy="12" r="3"></circle><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 1 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 1 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 1 1-2.83-2.83l.06-.06A1.65 1.65 0 0 0 4.6 15a1.65 1.65 0 0 0-1.51-1H3a2 2 0 1 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 1 1 2.83-2.83l.06.06A1.65 1.65 0 0 0 9 4.6a1.65 1.65 0 0 0 1-1.51V3a2 2 0 1 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 1 1 2.83 2.83l-.06.06A1.65 1.65 0 0 0 19.4 9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 1 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"></path>',
    ],
];
?>
<!-- Overlay untuk mobile, klik di luar sidebar untuk menutup -->
<div class="bp-sidebar-overlay" id="bpSidebarOverlay"></div>

<aside class="bp-sidebar" id="bpSidebar">
    <nav class="bp-sidebar__nav">
        <ul class="bp-sidebar__list">
            <?php foreach ($bpMenuItems as $item): ?>
                <?php $isActive = in_array($bpCurrentPage, $item['match'], true); ?>
                <li class="bp-sidebar__item">
                    <a href="<?= htmlspecialchars($item['href']) ?>"
                       class="bp-sidebar__link <?= $isActive ? 'is-active' : '' ?>"
                       <?= $isActive ? 'aria-current="page"' : '' ?>>
                        <svg class="bp-sidebar__icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="20" height="20">
                            <?= $item['icon'] ?>
                        </svg>
                        <span class="bp-sidebar__label"><?= htmlspecialchars($item['label']) ?></span>
                    </a>
                </li>
            <?php endforeach; ?>
        </ul>
    </nav>

    <div class="bp-sidebar__bottom">
        <a href="<?= SITE_URL ?>/logout.php" class="bp-sidebar__link bp-sidebar__link--logout">
            <svg class="bp-sidebar__icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="20" height="20">
                <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
                <polyline points="16 17 21 12 16 7"></polyline>
                <line x1="21" y1="12" x2="9" y2="12"></line>
            </svg>
            <span class="bp-sidebar__label">Keluar</span>
        </a>

        <div class="bp-sidebar__footer-mini">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="16" height="16">
                <circle cx="12" cy="12" r="10"></circle>
                <line x1="12" y1="16" x2="12" y2="12"></line>
                <line x1="12" y1="8" x2="12.01" y2="8"></line>
            </svg>
            <span>Butuh bantuan? Hubungi admin.</span>
        </div>
    </div>
</aside>

<style>
    .bp-sidebar {
        position: fixed;
        top: 64px; /* tinggi header */
        left: 0;
        bottom: 0;
        width: 240px;
        background: #ffffff;
        border-right: 1px solid #e5e7eb;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        z-index: 39;
        transform: translateX(0);
        transition: transform 0.2s ease;
        overflow-y: auto;
    }

    .bp-sidebar__nav { padding: 14px 10px; }

    .bp-sidebar__list {
        list-style: none;
        margin: 0;
        padding: 0;
        display: flex;
        flex-direction: column;
        gap: 4px;
    }

    .bp-sidebar__link {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 10px 12px;
        border-radius: 10px;
        text-decoration: none;
        color: #374151;
        font-size: 14px;
        font-weight: 500;
        transition: background 0.15s ease, color 0.15s ease;
    }
    .bp-sidebar__link:hover { background: #f3f4f6; }
    .bp-sidebar__link.is-active {
        background: #eff6ff;
        color: #1d4ed8;
        font-weight: 600;
    }
    .bp-sidebar__icon { flex-shrink: 0; }

    /* Bagian bawah: logout + bantuan */
    .bp-sidebar__bottom {
        border-top: 1px solid #e5e7eb;
    }
    .bp-sidebar__bottom .bp-sidebar__link {
        margin: 10px 10px 0;
    }
    .bp-sidebar__link--logout {
        color: #dc2626;
    }
    .bp-sidebar__link--logout:hover {
        background: #fef2f2;
    }

    .bp-sidebar__footer-mini {
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 14px;
        font-size: 12px;
        color: #6b7280;
    }

    /* Overlay khusus mobile */
    .bp-sidebar-overlay {
        display: none;
        position: fixed;
        inset: 0;
        top: 64px;
        background: rgba(0,0,0,0.35);
        z-index: 38;
    }

    /* Konten utama digeser ke kanan selama sidebar tampil (desktop) */
    .bp-content {
        margin-left: 240px;
        transition: margin-left 0.2s ease;
    }

    /* ====== Responsive: mobile ====== */
    @media (max-width: 900px) {
        .bp-sidebar {
            transform: translateX(-100%);
            box-shadow: 4px 0 16px rgba(0,0,0,0.08);
        }
        body.bp-sidebar-open .bp-sidebar {
            transform: translateX(0);
        }
        body.bp-sidebar-open .bp-sidebar-overlay {
            display: block;
        }
        .bp-content {
            margin-left: 0;
        }
    }
</style>

<script>
(function () {
    var overlay = document.getElementById('bpSidebarOverlay');
    if (overlay) {
        overlay.addEventListener('click', function () {
            document.body.classList.remove('bp-sidebar-open');
            var toggleBtn = document.getElementById('bpSidebarToggle');
            if (toggleBtn) toggleBtn.setAttribute('aria-expanded', 'false');
        });
    }

    // Tutup sidebar otomatis saat link diklik (khusus mobile)
    document.querySelectorAll('.bp-sidebar__link').forEach(function (link) {
        link.addEventListener('click', function () {
            if (window.innerWidth <= 900) {
                document.body.classList.remove('bp-sidebar-open');
            }
        });
    });
})();
</script>