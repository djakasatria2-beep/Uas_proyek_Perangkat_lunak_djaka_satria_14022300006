<?php
// ============================================================
//  admin_panel/partials/_sidebar.php
//  Sidebar navigasi kiri panel Admin.
//  Variabel:
//    $activePage — string, nama halaman aktif untuk highlight menu
//                  ('dashboard','buyers','orders','samples',
//                   'invoices','documents','returns','marketing-users',
//                   'reports','audit-log','settings','profile','password')
// ============================================================
$activePage = $activePage ?? '';

$menuItems = [
    ['key' => 'dashboard',       'icon' => 'bi-grid-1x2',         'label' => 'Dashboard',        'href' => ADMIN_URL . '/dashboard.php'],
    ['key' => 'buyers',          'icon' => 'bi-buildings',        'label' => 'Buyers',           'href' => ADMIN_URL . '/buyers.php'],
    ['key' => 'orders',          'icon' => 'bi-bag',              'label' => 'Pesanan',          'href' => ADMIN_URL . '/orders.php'],
    ['key' => 'samples',         'icon' => 'bi-palette',          'label' => 'Sampel Warna',     'href' => ADMIN_URL . '/samples.php'],
    ['key' => 'invoices',        'icon' => 'bi-receipt',          'label' => 'Invoice',          'href' => ADMIN_URL . '/invoices.php'],
    ['key' => 'documents',       'icon' => 'bi-folder2-open',     'label' => 'Dokumen',          'href' => ADMIN_URL . '/documents.php'],
    ['key' => 'returns',         'icon' => 'bi-arrow-return-left','label' => 'Retur & Komplain', 'href' => ADMIN_URL . '/returns.php'],
    ['key' => 'marketing-users', 'icon' => 'bi-people',           'label' => 'Tim Marketing',    'href' => ADMIN_URL . '/marketing-users.php'],
    ['key' => 'reports',         'icon' => 'bi-bar-chart-line',   'label' => 'Laporan',          'href' => ADMIN_URL . '/reports.php'],
    ['key' => 'audit-log',       'icon' => 'bi-journal-text',     'label' => 'Audit Log',        'href' => ADMIN_URL . '/audit-log.php'],
];
?>
<aside class="tb-sidebar" id="tbSidebar">
    <!-- Logo -->
    <div class="tb-sidebar__brand">
        <a href="<?= ADMIN_URL ?>/dashboard.php" class="tb-sidebar__logo">
            <span class="tb-sidebar__logo-mark">T</span>
            <span class="tb-sidebar__logo-text">Thread<strong>B2B</strong></span>
        </a>
        <button class="tb-sidebar__toggle d-xl-none" id="sidebarClose" aria-label="Tutup menu">
            <i class="bi bi-x-lg"></i>
        </button>
    </div>

    <!-- Label role -->
    <div class="tb-sidebar__role-badge">
        <i class="bi bi-shield-check"></i> Admin Panel
    </div>

    <!-- Menu utama -->
    <nav class="tb-sidebar__nav" aria-label="Menu Admin">
        <ul class="tb-sidebar__menu">
            <?php foreach ($menuItems as $item): ?>
            <li class="tb-sidebar__item">
                <a href="<?= $item['href'] ?>"
                   class="tb-sidebar__link <?= $activePage === $item['key'] ? 'active' : '' ?>">
                    <i class="bi <?= $item['icon'] ?> tb-sidebar__icon"></i>
                    <span class="tb-sidebar__label"><?= $item['label'] ?></span>
                    <?php if ($activePage === $item['key']): ?>
                    <span class="tb-sidebar__active-dot"></span>
                    <?php endif; ?>
                </a>
            </li>
            <?php endforeach; ?>
        </ul>
    </nav>

    <!-- Divider -->
    <div class="tb-sidebar__divider"></div>

    <!-- Menu bawah: profil & logout -->
    <nav class="tb-sidebar__nav" aria-label="Akun">
        <ul class="tb-sidebar__menu">
            <li class="tb-sidebar__item">
                <a href="<?= ADMIN_URL ?>/profile.php"
                   class="tb-sidebar__link <?= $activePage === 'profile' ? 'active' : '' ?>">
                    <i class="bi bi-person tb-sidebar__icon"></i>
                    <span class="tb-sidebar__label">Profil</span>
                </a>
            </li>
            <li class="tb-sidebar__item">
                <a href="<?= ADMIN_URL ?>/settings.php"
                   class="tb-sidebar__link <?= $activePage === 'settings' ? 'active' : '' ?>">
                    <i class="bi bi-gear tb-sidebar__icon"></i>
                    <span class="tb-sidebar__label">Pengaturan</span>
                </a>
            </li>
            <li class="tb-sidebar__item">
                <a href="<?= ADMIN_URL ?>/logout.php"
                   class="tb-sidebar__link tb-sidebar__link--logout"
                   onclick="return confirm('Yakin ingin keluar?')">
                    <i class="bi bi-box-arrow-right tb-sidebar__icon"></i>
                    <span class="tb-sidebar__label">Logout</span>
                </a>
            </li>
        </ul>
    </nav>
</aside>

<!-- Overlay untuk mobile -->
<div class="tb-sidebar__overlay" id="sidebarOverlay"></div>