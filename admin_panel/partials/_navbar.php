<?php
// ============================================================
//  admin_panel/partials/_navbar.php
//  Navbar atas panel Admin.
//  Butuh: $currentAdmin (dari config.php) dan $pageTitle
// ============================================================
$pageTitle = $pageTitle ?? 'Dashboard';
?>
<header class="tb-navbar" id="tbNavbar">
    <!-- Kiri: toggle sidebar (mobile) + breadcrumb halaman -->
    <div class="tb-navbar__left">
        <button class="tb-navbar__menu-btn" id="sidebarOpen" aria-label="Buka menu">
            <i class="bi bi-list"></i>
        </button>
        <h1 class="tb-navbar__page-title"><?= htmlspecialchars($pageTitle) ?></h1>
    </div>

    <!-- Kanan: notifikasi + info user -->
    <div class="tb-navbar__right">
        <!-- Toggle tema -->
        <button class="tb-navbar__icon-btn" id="themeToggle" aria-label="Toggle tema" title="Ganti tema">
            <i class="bi bi-sun" id="themeIcon"></i>
        </button>

        <!-- Notifikasi -->
        <div class="tb-navbar__notif dropdown">
            <button class="tb-navbar__icon-btn position-relative"
                    id="notifBtn"
                    data-bs-toggle="dropdown"
                    aria-expanded="false"
                    aria-label="Notifikasi">
                <i class="bi bi-bell"></i>
                <span class="tb-navbar__notif-badge d-none" id="notifCount">0</span>
            </button>
            <div class="dropdown-menu dropdown-menu-end tb-notif-dropdown" aria-labelledby="notifBtn">
                <div class="tb-notif-dropdown__header">
                    <span>Notifikasi</span>
                    <button class="tb-notif-dropdown__read-all" id="markAllRead">Tandai semua dibaca</button>
                </div>
                <div class="tb-notif-dropdown__list" id="notifList">
                    <div class="tb-notif-dropdown__empty">
                        <i class="bi bi-bell-slash"></i>
                        <p>Tidak ada notifikasi baru</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Info user + dropdown -->
        <div class="tb-navbar__user dropdown">
            <button class="tb-navbar__user-btn"
                    data-bs-toggle="dropdown"
                    aria-expanded="false">
                <div class="tb-navbar__avatar" id="navbarAvatar">
                    <i class="bi bi-person-fill"></i>
                </div>
                <div class="tb-navbar__user-info d-none d-md-block">
                    <span class="tb-navbar__user-name" id="navbarUserName">
                        <?= htmlspecialchars($currentAdmin['email'] ?? '') ?>
                    </span>
                    <span class="tb-navbar__user-role">Administrator</span>
                </div>
                <i class="bi bi-chevron-down tb-navbar__chevron"></i>
            </button>
            <ul class="dropdown-menu dropdown-menu-end tb-user-dropdown">
                <li>
                    <a class="dropdown-item" href="<?= ADMIN_URL ?>/profile.php">
                        <i class="bi bi-person me-2"></i> Profil Saya
                    </a>
                </li>
                <li>
                    <a class="dropdown-item" href="<?= ADMIN_URL ?>/settings.php">
                        <i class="bi bi-gear me-2"></i> Pengaturan
                    </a>
                </li>
                <li>
                    <a class="dropdown-item" href="<?= ADMIN_URL ?>/password.php">
                        <i class="bi bi-key me-2"></i> Ganti Password
                    </a>
                </li>
                <li><hr class="dropdown-divider"></li>
                <li>
                    <a class="dropdown-item text-danger" href="<?= ADMIN_URL ?>/logout.php"
                       onclick="return confirm('Yakin ingin keluar?')">
                        <i class="bi bi-box-arrow-right me-2"></i> Logout
                    </a>
                </li>
            </ul>
        </div>
    </div>
</header>