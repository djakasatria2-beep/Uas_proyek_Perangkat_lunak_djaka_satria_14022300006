// ============================================================
//  ThreadB2B — admin_panel/script.js
//  Script utama panel Admin:
//  - Sidebar open/close (mobile)
//  - Dark/light theme toggle
//  - Notifikasi polling
//  - Navbar avatar loader
// ============================================================

(function () {
    'use strict';

    // ----------------------------------------------------------
    // 1. Sidebar Toggle (mobile)
    // ----------------------------------------------------------
    const sidebar  = document.getElementById('tbSidebar');
    const overlay  = document.getElementById('sidebarOverlay');
    const btnOpen  = document.getElementById('sidebarOpen');
    const btnClose = document.getElementById('sidebarClose');

    function openSidebar() {
        sidebar?.classList.add('open');
        overlay?.classList.add('open');
        document.body.style.overflow = 'hidden';
    }

    function closeSidebar() {
        sidebar?.classList.remove('open');
        overlay?.classList.remove('open');
        document.body.style.overflow = '';
    }

    btnOpen?.addEventListener('click', openSidebar);
    btnClose?.addEventListener('click', closeSidebar);
    overlay?.addEventListener('click', closeSidebar);

    // Tutup sidebar saat resize ke desktop
    window.addEventListener('resize', () => {
        if (window.innerWidth >= 1200) closeSidebar();
    });

    // ----------------------------------------------------------
    // 2. Dark / Light Theme Toggle
    // ----------------------------------------------------------
    const themeToggle = document.getElementById('themeToggle');
    const themeIcon   = document.getElementById('themeIcon');
    const htmlEl      = document.documentElement;

    function applyTheme(theme) {
        htmlEl.setAttribute('data-theme', theme);
        if (themeIcon) {
            themeIcon.className = theme === 'dark' ? 'bi bi-moon' : 'bi bi-sun';
        }
        localStorage.setItem('tb_theme', theme);
    }

    // Load tersimpan
    const savedTheme = localStorage.getItem('tb_theme') || 'light';
    applyTheme(savedTheme);

    themeToggle?.addEventListener('click', () => {
        const current = htmlEl.getAttribute('data-theme') || 'light';
        applyTheme(current === 'dark' ? 'light' : 'dark');

        // Simpan ke server juga (opsional)
        fetch('../assets/themeSet.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ theme: htmlEl.getAttribute('data-theme') }),
        }).catch(() => {});
    });

    // ----------------------------------------------------------
    // 3. Notifikasi Polling
    // ----------------------------------------------------------
    const notifCount = document.getElementById('notifCount');
    const notifList  = document.getElementById('notifList');
    const markAllBtn = document.getElementById('markAllRead');

    async function fetchNotifications() {
        try {
            const res  = await fetch('../assets/fetchNotifications.php');
            const data = await res.json();

            if (!data.success || !Array.isArray(data.data)) return;

            const items = data.data;
            const unread = items.filter(n => !n.is_read).length;

            // Badge
            if (notifCount) {
                notifCount.textContent = unread > 99 ? '99+' : unread;
                notifCount.classList.toggle('d-none', unread === 0);
            }

            // List
            if (notifList) {
                if (items.length === 0) {
                    notifList.innerHTML = `
                        <div class="tb-notif-dropdown__empty">
                            <i class="bi bi-bell-slash"></i>
                            <p>Tidak ada notifikasi baru</p>
                        </div>`;
                    return;
                }

                notifList.innerHTML = items.map(n => `
                    <div class="tb-notif-item ${n.is_read ? '' : 'unread'}"
                         data-id="${n.id_notif}"
                         onclick="markNotifRead(${n.id_notif}, this)">
                        <div class="tb-notif-item__icon" style="background:${n.icon_bg || '#eff4ff'};color:${n.icon_color || '#1a56db'}">
                            <i class="bi ${n.icon || 'bi-bell'}"></i>
                        </div>
                        <div class="tb-notif-item__body">
                            <div class="tb-notif-item__text">${escapeHtml(n.pesan)}</div>
                            <div class="tb-notif-item__time">${escapeHtml(n.waktu_relatif || '')}</div>
                        </div>
                    </div>`).join('');
            }
        } catch (err) {
            console.warn('fetchNotifications error:', err);
        }
    }

    // Mark single read
    window.markNotifRead = async function (id, el) {
        try {
            el?.classList.remove('unread');
            await fetch('../assets/markNotificationRead.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id_notif: id }),
            });
            fetchNotifications();
        } catch (err) { console.warn(err); }
    };

    // Mark all read
    markAllBtn?.addEventListener('click', async () => {
        try {
            await fetch('../assets/markNotificationRead.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ all: true }),
            });
            fetchNotifications();
        } catch (err) { console.warn(err); }
    });

    // Poll setiap 60 detik
    fetchNotifications();
    setInterval(fetchNotifications, 60_000);

    // ----------------------------------------------------------
    // 4. Load avatar navbar
    // ----------------------------------------------------------
    const navbarAvatar = document.getElementById('navbarAvatar');

    async function loadAvatar() {
        try {
            const res  = await fetch('../assets/loadProfilePic.php');
            const data = await res.json();
            if (data.success && data.path && navbarAvatar) {
                navbarAvatar.innerHTML = `<img src="${data.path}" alt="Foto profil">`;
            }
        } catch (err) { /* gunakan icon default */ }
    }

    loadAvatar();

    // ----------------------------------------------------------
    // 5. Utility: escapeHtml
    // ----------------------------------------------------------
    function escapeHtml(str) {
        const map = { '&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;' };
        return String(str).replace(/[&<>"']/g, m => map[m]);
    }

    // ----------------------------------------------------------
    // 6. Auto-dismiss flash alerts (Bootstrap)
    // ----------------------------------------------------------
    document.querySelectorAll('.alert.alert-dismissible.auto-dismiss').forEach(el => {
        setTimeout(() => {
            const bsAlert = bootstrap.Alert.getOrCreateInstance(el);
            bsAlert?.close();
        }, 4000);
    });

})();