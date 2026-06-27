/* ============================================================
   buyer_panel/script.js
   ThreadB2B — Buyer Panel Global Script
   Inisialisasi UI, tema, notifikasi, sidebar, chatbot toggle
   ============================================================ */

   'use strict';

   /* ── Theme Manager ─────────────────────────────────────────── */
   const ThemeManager = (() => {
       const STORAGE_KEY = 'tbTheme';
       const THEMES      = ['light', 'dark'];
   
       function get() {
           return localStorage.getItem(STORAGE_KEY) || 'light';
       }
   
       function apply(theme) {
           document.body.classList.remove(...THEMES.map(t => `theme-${t}`));
           document.body.classList.add(`theme-${theme}`);
           localStorage.setItem(STORAGE_KEY, theme);
   
           // Sync ke server via AJAX (opsional)
           fetch(`${window.APP_URL ?? ''}/assets/themeSet.php`, {
               method:  'POST',
               headers: { 'Content-Type': 'application/json' },
               body:    JSON.stringify({ theme }),
           }).catch(() => {}); // silent fail
       }
   
       function toggle() {
           const next = get() === 'dark' ? 'light' : 'dark';
           apply(next);
       }
   
       function init() {
           apply(get());
       }
   
       return { get, apply, toggle, init };
   })();
   
   /* ── Sidebar Manager ───────────────────────────────────────── */
   const SidebarManager = (() => {
       const sidebar  = () => document.getElementById('sidebar');
       const overlay  = () => document.getElementById('sidebarOverlay');
   
       function open() {
           sidebar()?.classList.add('open');
           overlay()?.classList.add('visible');
           document.body.style.overflow = 'hidden';
       }
   
       function close() {
           sidebar()?.classList.remove('open');
           overlay()?.classList.remove('visible');
           document.body.style.overflow = '';
       }
   
       function toggle() {
           sidebar()?.classList.contains('open') ? close() : open();
       }
   
       function init() {
           document.getElementById('btnSidebarToggle')?.addEventListener('click', toggle);
           overlay()?.addEventListener('click', close);
   
           // Tutup otomatis saat resize ke desktop
           window.addEventListener('resize', () => {
               if (window.innerWidth >= 992) close();
           });
       }
   
       return { open, close, toggle, init };
   })();
   
   /* ── Notification Manager ──────────────────────────────────── */
   const NotifManager = (() => {
       const POLL_INTERVAL = 60_000; // 60 detik
       let   pollingTimer  = null;
   
       async function fetchNotifications() {
           try {
               const res  = await fetch(`${window.BUYER_URL ?? ''}/fetch-data/fetchNotifications.php`);
               if (!res.ok) return;
               const data = await res.json();
               render(data.notifications ?? []);
               updateBadge(data.unread_count ?? 0);
           } catch {
               // Network error — abaikan
           }
       }
   
       function render(items) {
           const list = document.getElementById('notifList');
           if (!list) return;
   
           if (!items.length) {
               list.innerHTML = `
                   <div class="text-center text-muted py-4 small">
                       <i class="bi bi-bell-slash d-block fs-4 mb-1"></i>
                       Tidak ada notifikasi baru
                   </div>`;
               return;
           }
   
           list.innerHTML = items.map(n => `
               <div class="notif-item ${n.is_read ? '' : 'unread'}" data-id="${n.id}">
                   <div class="notif-item-icon ${_notifColor(n.type)}">${_notifIcon(n.type)}</div>
                   <div>
                       <div class="notif-item-text">${_escape(n.message)}</div>
                       <div class="notif-item-time">${_timeAgo(n.created_at)}</div>
                   </div>
               </div>`
           ).join('');
   
           // Klik item → tandai dibaca
           list.querySelectorAll('.notif-item').forEach(el => {
               el.addEventListener('click', () => markRead(el.dataset.id));
           });
       }
   
       function updateBadge(count) {
           const badge = document.querySelector('.notif-badge');
           if (!badge) return;
           if (count > 0) {
               badge.textContent = count > 99 ? '99+' : count;
               badge.style.display = 'flex';
           } else {
               badge.style.display = 'none';
           }
       }
   
       async function markRead(id) {
           try {
               await fetch(`${window.BUYER_URL ?? ''}/fetch-data/markNotificationRead.php`, {
                   method:  'POST',
                   headers: { 'Content-Type': 'application/json' },
                   body:    JSON.stringify({ id }),
               });
               fetchNotifications();
           } catch {}
       }
   
       async function markAllRead() {
           try {
               await fetch(`${window.BUYER_URL ?? ''}/fetch-data/markNotificationRead.php`, {
                   method:  'POST',
                   headers: { 'Content-Type': 'application/json' },
                   body:    JSON.stringify({ all: true }),
               });
               fetchNotifications();
           } catch {}
       }
   
       // Helpers
       function _notifIcon(type) {
           const icons = {
               order:   '<i class="bi bi-bag"></i>',
               invoice: '<i class="bi bi-receipt"></i>',
               sample:  '<i class="bi bi-palette"></i>',
               return:  '<i class="bi bi-arrow-return-left"></i>',
               system:  '<i class="bi bi-info-circle"></i>',
           };
           return icons[type] ?? icons.system;
       }
   
       function _notifColor(type) {
           const colors = {
               order:   'text-primary',
               invoice: 'text-warning',
               sample:  'text-info',
               return:  'text-danger',
               system:  'text-secondary',
           };
           return colors[type] ?? 'text-secondary';
       }
   
       function _timeAgo(dateStr) {
           if (!dateStr) return '';
           const diff  = (Date.now() - new Date(dateStr).getTime()) / 1000;
           if (diff <    60) return 'Baru saja';
           if (diff <  3600) return `${Math.floor(diff / 60)} menit lalu`;
           if (diff < 86400) return `${Math.floor(diff / 3600)} jam lalu`;
           return `${Math.floor(diff / 86400)} hari lalu`;
       }
   
       function _escape(str) {
           const d = document.createElement('div');
           d.textContent = str;
           return d.innerHTML;
       }
   
       function startPolling() {
           fetchNotifications();
           pollingTimer = setInterval(fetchNotifications, POLL_INTERVAL);
       }
   
       function stopPolling() {
           clearInterval(pollingTimer);
       }
   
       function init() {
           document.getElementById('btnMarkAllRead')?.addEventListener('click', (e) => {
               e.preventDefault();
               markAllRead();
           });
   
           // Fetch saat dropdown dibuka
           document.getElementById('btnNotifikasi')?.addEventListener('show.bs.dropdown', fetchNotifications);
   
           startPolling();
       }
   
       return { init, fetchNotifications, markAllRead, stopPolling };
   })();
   
   /* ── Order Status Badge Helper ─────────────────────────────── */
   const StatusBadge = {
       order(status) {
           const map = {
               pending:    ['badge-pending',    'Pending'],
               processing: ['badge-processing', 'Processing'],
               shipped:    ['badge-shipped',    'Shipped'],
               done:       ['badge-done',       'Selesai'],
               cancelled:  ['badge-cancelled',  'Dibatalkan'],
           };
           const [cls, label] = map[status] ?? ['badge-secondary', status];
           return `<span class="badge ${cls}">${label}</span>`;
       },
   
       sample(status) {
           const map = {
               pending:        ['badge-warning',   'Pending'],
               waiting_result: ['badge-info',      'Menunggu Hasil'],
               result_ready:   ['badge-amber',     'Hasil Tersedia'],
               approved:       ['badge-success',   'Disetujui'],
               rejected:       ['badge-danger',    'Ditolak'],
               revision:       ['badge-secondary', 'Revisi'],
           };
           const [cls, label] = map[status] ?? ['badge-secondary', status];
           return `<span class="badge ${cls}">${label}</span>`;
       },
   
       invoice(status) {
           const map = {
               DRAFT:   ['badge-secondary', 'Draft'],
               ISSUED:  ['badge-info',      'Diterbitkan'],
               PAID:    ['badge-success',   'Lunas'],
               OVERDUE: ['badge-danger',    'Overdue'],
           };
           const [cls, label] = map[status] ?? ['badge-secondary', status];
           return `<span class="badge ${cls}">${label}</span>`;
       },
   
       returns(status) {
           const map = {
               submitted:    ['badge-info',      'Diajukan'],
               under_review: ['badge-warning',   'Sedang Ditinjau'],
               approved:     ['badge-amber',     'Disetujui'],
               resolved:     ['badge-success',   'Selesai'],
               rejected:     ['badge-danger',    'Ditolak'],
           };
           const [cls, label] = map[status] ?? ['badge-secondary', status];
           return `<span class="badge ${cls}">${label}</span>`;
       },
   };
   
   /* ── Currency & Date Formatter ─────────────────────────────── */
   const Fmt = {
       currency(amount, currency = 'IDR') {
           return new Intl.NumberFormat('id-ID', {
               style:    'currency',
               currency,
               maximumFractionDigits: 0,
           }).format(amount);
       },
   
       date(dateStr, opts = {}) {
           if (!dateStr) return '—';
           return new Intl.DateTimeFormat('id-ID', {
               day:   '2-digit',
               month: 'short',
               year:  'numeric',
               ...opts,
           }).format(new Date(dateStr));
       },
   
       number(n) {
           return new Intl.NumberFormat('id-ID').format(n);
       },
   };
   
   /* ── Toast Notification ────────────────────────────────────── */
   const Toast = (() => {
       let container;
   
       function _getContainer() {
           if (!container) {
               container = document.createElement('div');
               container.id = 'toastContainer';
               Object.assign(container.style, {
                   position:    'fixed',
                   bottom:      '24px',
                   right:       '24px',
                   zIndex:      '9999',
                   display:     'flex',
                   flexDirection: 'column',
                   gap:         '10px',
                   maxWidth:    '360px',
               });
               document.body.appendChild(container);
           }
           return container;
       }
   
       function show(message, type = 'info', duration = 4000) {
           const c    = _getContainer();
           const el   = document.createElement('div');
           const icons = {
               success: 'bi-check-circle-fill',
               danger:  'bi-x-circle-fill',
               warning: 'bi-exclamation-triangle-fill',
               info:    'bi-info-circle-fill',
           };
           const colors = {
               success: '#16a34a',
               danger:  '#dc2626',
               warning: '#d97706',
               info:    '#0284c7',
           };
   
           el.className = 'tb-toast';
           el.innerHTML = `
               <i class="bi ${icons[type] ?? icons.info}" style="color:${colors[type]}; flex-shrink:0; font-size:18px;"></i>
               <span style="flex:1;font-size:13.5px;line-height:1.5;">${message}</span>
               <button onclick="this.parentElement.remove()" style="background:none;border:none;cursor:pointer;padding:0;color:inherit;opacity:.5;font-size:16px;">&times;</button>`;
   
           Object.assign(el.style, {
               display:      'flex',
               alignItems:   'center',
               gap:          '10px',
               background:   'var(--card-bg)',
               border:       '1px solid var(--card-border)',
               borderRadius: 'var(--radius-md)',
               padding:      '14px 16px',
               boxShadow:    '0 8px 32px rgba(15,31,61,.16)',
               color:        'var(--text-primary)',
               opacity:      '0',
               transform:    'translateY(12px)',
               transition:   'opacity .25s, transform .25s',
           });
   
           c.appendChild(el);
           requestAnimationFrame(() => {
               el.style.opacity   = '1';
               el.style.transform = 'translateY(0)';
           });
   
           setTimeout(() => {
               el.style.opacity   = '0';
               el.style.transform = 'translateY(12px)';
               setTimeout(() => el.remove(), 280);
           }, duration);
       }
   
       return {
           success: (msg, d) => show(msg, 'success', d),
           error:   (msg, d) => show(msg, 'danger',  d),
           warning: (msg, d) => show(msg, 'warning', d),
           info:    (msg, d) => show(msg, 'info',    d),
       };
   })();
   
   /* ── Confirm Dialog ────────────────────────────────────────── */
   function tbConfirm(message, onConfirm, { danger = false, label = 'Ya, Lanjutkan' } = {}) {
       const existing = document.getElementById('tbConfirmModal');
       existing?.remove();
   
       const modal = document.createElement('div');
       modal.id        = 'tbConfirmModal';
       modal.className = 'modal fade';
       modal.setAttribute('tabindex', '-1');
       modal.innerHTML = `
           <div class="modal-dialog modal-dialog-centered modal-sm">
               <div class="modal-content" style="border-radius:var(--radius-lg);border:1px solid var(--card-border);background:var(--card-bg);">
                   <div class="modal-body p-4 text-center">
                       <i class="bi ${danger ? 'bi-exclamation-triangle-fill text-danger' : 'bi-question-circle-fill text-warning'}"
                          style="font-size:36px;margin-bottom:12px;display:block;"></i>
                       <p style="font-size:14px;color:var(--text-primary);margin-bottom:0;">${message}</p>
                   </div>
                   <div class="modal-footer border-0 pt-0 pb-4 px-4 justify-content-center gap-2">
                       <button class="btn btn-outline-primary" data-bs-dismiss="modal">Batal</button>
                       <button class="btn ${danger ? 'btn-danger' : 'btn-primary'}" id="tbConfirmOk">${label}</button>
                   </div>
               </div>
           </div>`;
   
       document.body.appendChild(modal);
       const bsModal = new bootstrap.Modal(modal);
       bsModal.show();
   
       document.getElementById('tbConfirmOk').addEventListener('click', () => {
           bsModal.hide();
           onConfirm();
       });
   
       modal.addEventListener('hidden.bs.modal', () => modal.remove());
   }
   
   /* ── Global AJAX Helper ────────────────────────────────────── */
   async function tbFetch(url, options = {}) {
       const defaults = {
           headers: { 'X-Requested-With': 'XMLHttpRequest' },
       };
       const cfg = { ...defaults, ...options };
       if (cfg.headers) cfg.headers = { ...defaults.headers, ...options.headers };
   
       const res  = await fetch(url, cfg);
       const data = await res.json();
   
       if (!res.ok || data.success === false) {
           throw new Error(data.message ?? `HTTP ${res.status}`);
       }
   
       return data;
   }
   
   /* ── Color Hex Preview ─────────────────────────────────────── */
   function initColorPreview(inputSelector = '#inputKodeWarna', swatchSelector = '#colorSwatch') {
       const input  = document.querySelector(inputSelector);
       const swatch = document.querySelector(swatchSelector);
       if (!input || !swatch) return;
   
       function update() {
           const val = input.value.trim();
           const hex = val.startsWith('#') ? val : `#${val}`;
           if (/^#[0-9a-f]{6}$/i.test(hex)) {
               swatch.style.background  = hex;
               swatch.style.borderColor = hex;
           } else {
               swatch.style.background  = 'var(--card-border)';
               swatch.style.borderColor = 'var(--card-border)';
           }
       }
   
       input.addEventListener('input', update);
       update();
   }
   
   /* ── Photo Dropzone ────────────────────────────────────────── */
   function initDropzone(dropzoneSelector, fileInputSelector, previewSelector, maxFiles = 5) {
       const zone    = document.querySelector(dropzoneSelector);
       const input   = document.querySelector(fileInputSelector);
       const preview = document.querySelector(previewSelector);
       if (!zone || !input) return;
   
       zone.addEventListener('click', () => input.click());
   
       zone.addEventListener('dragover', (e) => {
           e.preventDefault();
           zone.classList.add('drag-over');
       });
   
       zone.addEventListener('dragleave', () => zone.classList.remove('drag-over'));
   
       zone.addEventListener('drop', (e) => {
           e.preventDefault();
           zone.classList.remove('drag-over');
           const dt = new DataTransfer();
           [...e.dataTransfer.files].slice(0, maxFiles).forEach(f => dt.items.add(f));
           input.files = dt.files;
           input.dispatchEvent(new Event('change'));
       });
   
       input.addEventListener('change', () => {
           if (!preview) return;
           preview.innerHTML = '';
           [...input.files].forEach(file => {
               if (!file.type.startsWith('image/')) return;
               const reader = new FileReader();
               reader.onload = (e) => {
                   const img = document.createElement('img');
                   img.src = e.target.result;
                   Object.assign(img.style, {
                       width:        '72px',
                       height:       '72px',
                       objectFit:    'cover',
                       borderRadius: 'var(--radius-md)',
                       border:       '2px solid var(--card-border)',
                   });
                   preview.appendChild(img);
               };
               reader.readAsDataURL(file);
           });
       });
   }
   
   /* ── Init All ──────────────────────────────────────────────── */
   document.addEventListener('DOMContentLoaded', () => {
       ThemeManager.init();
       SidebarManager.init();
       NotifManager.init();
   
       // Theme toggle button (jika ada di halaman)
       document.getElementById('btnThemeToggle')?.addEventListener('click', ThemeManager.toggle);
   
       // Tooltip Bootstrap
       document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(el => {
           new bootstrap.Tooltip(el, { trigger: 'hover' });
       });
   
       // Auto-dismiss alert setelah 5 detik
       document.querySelectorAll('.alert[data-auto-dismiss]').forEach(el => {
           setTimeout(() => {
               el.style.transition = 'opacity .4s';
               el.style.opacity    = '0';
               setTimeout(() => el.remove(), 420);
           }, 5000);
       });
   });
   
   /* ── Exports ke global scope ───────────────────────────────── */
   window.ThemeManager  = ThemeManager;
   window.SidebarManager = SidebarManager;
   window.NotifManager  = NotifManager;
   window.StatusBadge   = StatusBadge;
   window.Fmt           = Fmt;
   window.Toast         = Toast;
   window.tbConfirm     = tbConfirm;
   window.tbFetch       = tbFetch;
   window.initColorPreview = initColorPreview;
   window.initDropzone  = initDropzone;