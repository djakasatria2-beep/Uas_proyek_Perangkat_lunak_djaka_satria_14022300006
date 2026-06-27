<?php
// ============================================================
// kontak.php — Halaman Kontak THREADB2B
// Form pesan dikirim via AJAX ke submit_kontak.php
// Data kontak diambil dari tabel company_profile
// ============================================================

require_once __DIR__ . '/assets/config.php';
$db = getDB();

// ── company_profile ───────────────────────────────────────────
$company = $db->query("SELECT * FROM company_profile LIMIT 1")->fetch_assoc();
$company = $company ?: [
    'nama_pt'  => 'PT Sing Long Brothers Industrial',
    'alamat'   => 'Factory: Jalan Raya Serang Km. 62, Cikande, Serang, Banten, Indonesia',
    'email'    => 'info@singlongbrothers.co.id',
    'phone'    => '(0254) 401122, 401123, 401124',
    'maps'     => '',
];

// ── FAQ statis ────────────────────────────────────────────────
$faq_rows = [
    [
        'q' => 'Apakah tersedia layanan sample benang sebelum pemesanan?',
        'a' => 'Ya, kami menyediakan layanan sample request untuk calon buyer. Silakan ajukan permintaan melalui platform THREADB2B atau hubungi tim marketing kami langsung.'
    ],
    [
        'q' => 'Berapa minimum order quantity (MOQ) untuk pemesanan benang?',
        'a' => 'MOQ kami bervariasi tergantung jenis dan spesifikasi benang, umumnya mulai dari 100 KG per item. Hubungi tim sales kami untuk informasi MOQ spesifik.'
    ],
    [
        'q' => 'Apakah PT Sing Long Brothers Industrial melayani ekspor?',
        'a' => 'Ya, kami melayani ekspor ke lebih dari 15 negara di Asia Tenggara, Asia Timur, dan Eropa. Tim ekspor kami siap membantu proses dokumen dan pengiriman internasional.'
    ],
    [
        'q' => 'Bagaimana cara mendaftar sebagai buyer di platform THREADB2B?',
        'a' => 'Klik tombol "Daftar" di pojok kanan atas, isi formulir pendaftaran, dan unggah dokumen perusahaan. Akun Anda akan diverifikasi tim kami dalam 1x24 jam kerja.'
    ],
    [
        'q' => 'Apa saja metode pembayaran yang tersedia?',
        'a' => 'Kami menerima transfer bank, Letter of Credit (L/C), dan sistem tenor (net 30/45 hari) untuk buyer terverifikasi. Detail lebih lanjut dapat didiskusikan dengan tim finance kami.'
    ],
    [
        'q' => 'Berapa lama lead time produksi untuk pesanan custom?',
        'a' => 'Lead time produksi untuk pesanan custom (warna/spesifikasi khusus) umumnya 14–21 hari kerja setelah sample disetujui. Untuk produk stok, pengiriman bisa dilakukan dalam 3–7 hari kerja.'
    ],
];
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Kontak – THREADB2B | PT Sing Long Brothers Industrial</title>
<meta name="description" content="Hubungi PT Sing Long Brothers Industrial untuk informasi produk benang, sample request, dan kerjasama bisnis tekstil B2B.">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;500;600;700;800&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
<style>
/* ── DESIGN TOKENS ───────────────────────────────────────── */
:root {
  --navy: #0B3C91; --navy-dark: #072d6e; --navy-deeper: #041e4f;
  --red: #E31E24;  --red-dark: #b8181d;
  --white: #ffffff; --off-white: #f7f8fc; --light-gray: #eef0f6;
  --mid-gray: #c8ccd8; --text-dark: #0d1b3e; --text-mid: #4a5470;
  --text-light: #8891ab;
  --font-head: 'Syne', sans-serif; --font-body: 'DM Sans', sans-serif;
  --radius: 12px; --radius-lg: 20px;
  --shadow: 0 4px 24px rgba(11,60,145,0.10);
  --shadow-hover: 0 12px 40px rgba(11,60,145,0.18);
  --transition: all 0.32s cubic-bezier(0.4,0,0.2,1);
}
*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
html { scroll-behavior: smooth; }
body { font-family: var(--font-body); color: var(--text-dark); background: var(--white); overflow-x: hidden; line-height: 1.6; }

/* ── LOADER ──────────────────────────────────────────────── */
#loader { position: fixed; inset: 0; background: var(--navy-deeper); z-index: 9999; display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 24px; transition: opacity 0.5s ease, visibility 0.5s ease; }
#loader.hidden { opacity: 0; visibility: hidden; }
.loader-bar { width: 200px; height: 3px; background: rgba(255,255,255,0.15); border-radius: 99px; overflow: hidden; }
.loader-bar-fill { height: 100%; width: 0%; background: linear-gradient(90deg, var(--red), var(--navy)); border-radius: 99px; animation: loadFill 1.4s ease forwards; }
@keyframes loadFill { to { width: 100%; } }
.loader-text { color: rgba(255,255,255,0.5); font-family: var(--font-body); font-size: 0.8rem; letter-spacing: 0.15em; text-transform: uppercase; }
.loader-logo-text { font-family: var(--font-head); font-size: 1.8rem; font-weight: 800; color: white; }

/* ── REVEAL ──────────────────────────────────────────────── */
.reveal { opacity: 0; transform: translateY(28px); transition: opacity 0.65s ease, transform 0.65s ease; }
.reveal.visible { opacity: 1; transform: translateY(0); }
.reveal-delay-1 { transition-delay: 0.08s; }
.reveal-delay-2 { transition-delay: 0.16s; }
.reveal-delay-3 { transition-delay: 0.24s; }
.reveal-delay-4 { transition-delay: 0.32s; }

/* ── NAVBAR ──────────────────────────────────────────────── */
#navbar { position: fixed; top: 0; left: 0; right: 0; z-index: 1000; padding: 0 48px; height: 72px; display: flex; align-items: center; justify-content: space-between; transition: var(--transition); background: transparent; }
#navbar.scrolled { background: rgba(255,255,255,0.97); backdrop-filter: blur(16px); box-shadow: 0 2px 20px rgba(11,60,145,0.10); height: 64px; }
#navbar.scrolled .nav-logo-text { color: var(--navy); }
#navbar.scrolled .nav-link { color: var(--text-dark); }
#navbar.scrolled .nav-link:hover { color: var(--navy); }
#navbar.scrolled .hamburger span { background: var(--text-dark); }
#navbar.scrolled .btn-nav-login { border-color: var(--navy); color: var(--navy); }
#navbar.scrolled .btn-nav-login:hover { background: var(--navy); color: white; }
.nav-logo { display: flex; align-items: center; gap: 12px; text-decoration: none; }
.nav-logo-box { width: 38px; height: 38px; background: var(--red); border-radius: 9px; display: flex; align-items: center; justify-content: center; }
.nav-logo-box svg { width: 20px; height: 20px; stroke: white; fill: none; stroke-width: 2; }
.nav-logo-text { font-family: var(--font-head); font-weight: 700; font-size: 1.1rem; color: var(--white); transition: var(--transition); line-height: 1.1; }
.nav-logo-text span { display: block; font-size: 0.65rem; font-weight: 400; letter-spacing: 0.1em; opacity: 0.75; font-family: var(--font-body); }
.nav-menu { display: flex; align-items: center; gap: 4px; list-style: none; }
.nav-link { color: rgba(255,255,255,0.9); text-decoration: none; font-size: 0.875rem; font-weight: 500; padding: 8px 14px; border-radius: 8px; transition: var(--transition); position: relative; }
.nav-link:hover { color: var(--white); background: rgba(255,255,255,0.12); }
#navbar.scrolled .nav-link.active { color: var(--navy); }
.nav-link.active::after { content:''; position: absolute; bottom: 4px; left: 50%; transform: translateX(-50%); width: 20px; height: 2px; background: var(--red); border-radius: 99px; }
.nav-actions { display: flex; align-items: center; gap: 10px; }
.btn-nav-login { background: transparent; border: 1.5px solid rgba(255,255,255,0.6); color: var(--white); font-family: var(--font-body); font-size: 0.85rem; font-weight: 500; padding: 8px 18px; border-radius: 8px; text-decoration: none; transition: var(--transition); }
.btn-nav-login:hover { background: rgba(255,255,255,0.15); }
.btn-nav-register { background: var(--red); border: none; color: var(--white); font-family: var(--font-body); font-size: 0.85rem; font-weight: 600; padding: 8px 20px; border-radius: 8px; text-decoration: none; transition: var(--transition); }
.btn-nav-register:hover { background: var(--red-dark); }
.hamburger { display: none; flex-direction: column; gap: 5px; cursor: pointer; padding: 4px; background: none; border: none; }
.hamburger span { display: block; width: 24px; height: 2px; background: white; border-radius: 99px; transition: var(--transition); }
.hamburger.open span:nth-child(1) { transform: rotate(45deg) translate(5px,5px); }
.hamburger.open span:nth-child(2) { opacity: 0; }
.hamburger.open span:nth-child(3) { transform: rotate(-45deg) translate(5px,-5px); }
.mobile-menu { position: fixed; top: 0; right: 0; width: 300px; height: 100vh; background: var(--white); box-shadow: -8px 0 40px rgba(0,0,0,0.15); z-index: 1001; transform: translateX(100%); transition: transform 0.35s cubic-bezier(0.4,0,0.2,1); padding: 80px 32px 32px; display: flex; flex-direction: column; gap: 8px; }
.mobile-menu.open { transform: translateX(0); }
.mobile-overlay { display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.4); z-index: 1000; opacity: 0; transition: opacity 0.3s; }
.mobile-overlay.active { display: block; opacity: 1; }
.mobile-nav-link { display: block; color: var(--text-dark); text-decoration: none; font-size: 1rem; font-weight: 500; padding: 12px 0; border-bottom: 1px solid var(--light-gray); transition: color 0.2s; }
.mobile-nav-link:hover { color: var(--navy); }
.mobile-actions { margin-top: 24px; display: flex; flex-direction: column; gap: 10px; }

/* ── PAGE HERO ───────────────────────────────────────────── */
.page-hero { min-height: 380px; background: linear-gradient(135deg, var(--navy-deeper) 0%, var(--navy) 60%, #1a4fa8 100%); position: relative; display: flex; align-items: center; overflow: hidden; }
.page-hero-pattern { position: absolute; inset: 0; background-image: url('data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNjAiIGhlaWdodD0iNjAiIHZpZXdCb3g9IjAgMCA2MCA2MCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48ZyBmaWxsPSJub25lIiBmaWxsLXJ1bGU9ImV2ZW5vZGQiPjxnIGZpbGw9IiNmZmZmZmYiIGZpbGwtb3BhY2l0eT0iMC4wMyI+PHBhdGggZD0iTTM2IDM0djZoNnYtNmgtNnptMCAwdi02aC02djZoNnptNiAwaDZ2LTZoLTZ2NnptLTEyIDBoLTZ2Nmg2di02eiIvPjwvZz48L2c+PC9zdmc+'); opacity: 0.5; }
.page-hero-accent { position: absolute; top: -60px; right: -60px; width: 420px; height: 420px; background: radial-gradient(circle, rgba(227,30,36,0.22) 0%, transparent 70%); pointer-events: none; }
.page-hero-accent-2 { position: absolute; bottom: -100px; left: -100px; width: 360px; height: 360px; background: radial-gradient(circle, rgba(255,255,255,0.05) 0%, transparent 70%); pointer-events: none; }
.page-hero-content { position: relative; z-index: 2; max-width: 1200px; margin: 0 auto; padding: 120px 48px 72px; width: 100%; }
.breadcrumb { display: flex; align-items: center; gap: 8px; margin-bottom: 20px; }
.breadcrumb a { font-size: 0.8rem; color: rgba(255,255,255,0.55); text-decoration: none; transition: color 0.2s; }
.breadcrumb a:hover { color: rgba(255,255,255,0.9); }
.breadcrumb-sep { color: rgba(255,255,255,0.3); font-size: 0.8rem; }
.breadcrumb-current { font-size: 0.8rem; color: rgba(255,255,255,0.9); font-weight: 500; }
.page-hero-badge { display: inline-flex; align-items: center; gap: 8px; background: rgba(227,30,36,0.18); border: 1px solid rgba(227,30,36,0.4); color: #ff8f91; font-size: 0.75rem; font-weight: 600; letter-spacing: 0.12em; text-transform: uppercase; padding: 7px 16px; border-radius: 99px; margin-bottom: 20px; }
.badge-dot { width: 6px; height: 6px; background: var(--red); border-radius: 50%; animation: blink 1.4s ease-in-out infinite; }
@keyframes blink { 0%,100%{opacity:1}50%{opacity:0.2} }
.page-hero-title { font-family: var(--font-head); font-size: clamp(1.9rem, 4vw, 3rem); font-weight: 800; color: var(--white); line-height: 1.1; margin-bottom: 16px; letter-spacing: -0.02em; }
.page-hero-title .accent { color: var(--red); }
.page-hero-sub { font-size: 1rem; color: rgba(255,255,255,0.65); max-width: 540px; line-height: 1.7; }
.page-hero-chips { display: flex; gap: 12px; flex-wrap: wrap; margin-top: 28px; }
.hero-chip { display: inline-flex; align-items: center; gap: 8px; background: rgba(255,255,255,0.09); border: 1px solid rgba(255,255,255,0.14); color: rgba(255,255,255,0.8); font-size: 0.8rem; font-weight: 500; padding: 8px 16px; border-radius: 99px; backdrop-filter: blur(6px); }
.hero-chip svg { width: 14px; height: 14px; stroke: rgba(255,255,255,0.6); fill: none; stroke-width: 2; }

/* ── CHANNEL CARDS (quick contact strip) ─────────────────── */
.channel-strip { background: var(--white); padding: 0 48px; }
.channel-inner { max-width: 1200px; margin: 0 auto; display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px; transform: translateY(-48px); }
.channel-card { background: white; border-radius: var(--radius-lg); padding: 28px 24px; box-shadow: 0 8px 40px rgba(11,60,145,0.13); border: 1px solid rgba(11,60,145,0.06); text-align: center; transition: var(--transition); text-decoration: none; display: flex; flex-direction: column; align-items: center; gap: 14px; }
.channel-card:hover { transform: translateY(-6px); box-shadow: var(--shadow-hover); }
.channel-icon { width: 56px; height: 56px; border-radius: 14px; display: flex; align-items: center; justify-content: center; }
.channel-icon svg { width: 26px; height: 26px; fill: none; stroke-width: 1.75; }
.channel-card.phone .channel-icon   { background: rgba(11,60,145,0.08); }
.channel-card.phone .channel-icon svg { stroke: var(--navy); }
.channel-card.email .channel-icon   { background: rgba(227,30,36,0.08); }
.channel-card.email .channel-icon svg { stroke: var(--red); }
.channel-card.whatsapp .channel-icon { background: rgba(37,211,102,0.1); }
.channel-card.whatsapp .channel-icon svg { stroke: #25D366; }
.channel-card.visit .channel-icon   { background: rgba(11,60,145,0.08); }
.channel-card.visit .channel-icon svg { stroke: var(--navy); }
.channel-label { font-size: 0.72rem; font-weight: 700; letter-spacing: 0.12em; text-transform: uppercase; color: var(--text-light); }
.channel-value { font-family: var(--font-head); font-size: 0.92rem; font-weight: 700; color: var(--text-dark); line-height: 1.3; }
.channel-sub { font-size: 0.78rem; color: var(--text-light); }

/* ── FORM + INFO SECTION ─────────────────────────────────── */
#kontak-form { background: var(--off-white); padding: 20px 48px 100px; }
.kontak-inner { max-width: 1200px; margin: 0 auto; }
.kontak-grid { display: grid; grid-template-columns: 1.1fr 0.9fr; gap: 52px; align-items: start; }

/* Form card */
.form-card { background: white; border-radius: var(--radius-lg); padding: 52px 48px; box-shadow: var(--shadow); }
.form-card-title { font-family: var(--font-head); font-size: 1.5rem; font-weight: 800; color: var(--text-dark); margin-bottom: 6px; }
.form-card-sub { font-size: 0.88rem; color: var(--text-mid); margin-bottom: 36px; }
.form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
.form-group { margin-bottom: 18px; }
.form-group.full { grid-column: 1 / -1; }
.form-label { display: block; font-size: 0.8rem; font-weight: 600; color: var(--text-dark); margin-bottom: 8px; letter-spacing: 0.02em; }
.form-label .req { color: var(--red); margin-left: 2px; }
.form-input, .form-select, .form-textarea { width: 100%; padding: 13px 16px; border: 1.5px solid var(--light-gray); border-radius: var(--radius); font-family: var(--font-body); font-size: 0.9rem; color: var(--text-dark); background: var(--off-white); transition: border-color 0.25s ease, box-shadow 0.25s ease, background 0.2s; outline: none; appearance: none; }
.form-input::placeholder, .form-textarea::placeholder { color: var(--mid-gray); }
.form-input:focus, .form-select:focus, .form-textarea:focus { border-color: var(--navy); box-shadow: 0 0 0 3px rgba(11,60,145,0.09); background: white; }
.form-select { background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='%238891ab' stroke-width='2'%3E%3Cpolyline points='6 9 12 15 18 9'/%3E%3C/svg%3E"); background-repeat: no-repeat; background-position: right 14px center; padding-right: 40px; cursor: pointer; }
.form-textarea { height: 140px; resize: vertical; }
.form-alert { padding: 14px 18px; border-radius: 10px; font-size: 0.875rem; font-weight: 500; margin-bottom: 20px; display: none; align-items: center; gap: 10px; }
.form-alert.success { background: #e8f8f1; border: 1px solid #a3e4c3; color: #0d6e42; display: flex; }
.form-alert.error   { background: #fff0f0; border: 1px solid #f5c6c6; color: #c0392b;  display: flex; }
.form-alert svg { width: 18px; height: 18px; flex-shrink: 0; stroke: currentColor; fill: none; stroke-width: 2; }
.btn-submit { width: 100%; background: linear-gradient(135deg, var(--navy), #1a5ed6); color: white; border: none; padding: 16px; border-radius: var(--radius); font-family: var(--font-head); font-size: 0.95rem; font-weight: 700; cursor: pointer; transition: var(--transition); display: flex; align-items: center; justify-content: center; gap: 10px; margin-top: 8px; }
.btn-submit:hover { transform: translateY(-2px); box-shadow: 0 8px 30px rgba(11,60,145,0.35); }
.btn-submit:disabled { opacity: 0.65; cursor: not-allowed; transform: none; box-shadow: none; }
.btn-submit svg { width: 18px; height: 18px; stroke: white; fill: none; stroke-width: 2; }
.form-note { text-align: center; font-size: 0.78rem; color: var(--text-light); margin-top: 14px; display: flex; align-items: center; justify-content: center; gap: 6px; }
.form-note svg { width: 13px; height: 13px; stroke: var(--text-light); fill: none; stroke-width: 2; }

/* Info kolom kanan */
.info-col { display: flex; flex-direction: column; gap: 20px; }
.info-section-title { font-family: var(--font-head); font-size: 1.3rem; font-weight: 800; color: var(--text-dark); margin-bottom: 4px; }
.info-section-sub { font-size: 0.875rem; color: var(--text-mid); line-height: 1.6; margin-bottom: 4px; }
.info-card { background: white; border-radius: var(--radius); padding: 20px; border: 1px solid rgba(11,60,145,0.07); display: flex; align-items: flex-start; gap: 16px; transition: var(--transition); }
.info-card:hover { transform: translateX(4px); box-shadow: var(--shadow); }
.info-card-icon { width: 44px; height: 44px; background: rgba(11,60,145,0.08); border-radius: 10px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.info-card-icon svg { width: 20px; height: 20px; stroke: var(--navy); fill: none; stroke-width: 1.75; }
.info-card-icon.red { background: rgba(227,30,36,0.08); }
.info-card-icon.red svg { stroke: var(--red); }
.info-card-icon.green { background: rgba(37,211,102,0.1); }
.info-card-icon.green svg { stroke: #25D366; }
.info-card-text strong { display: block; font-size: 0.8rem; font-weight: 700; color: var(--text-dark); margin-bottom: 4px; }
.info-card-text span { font-size: 0.85rem; color: var(--text-mid); line-height: 1.5; }
.info-card-text a { color: var(--navy); text-decoration: none; transition: color 0.2s; }
.info-card-text a:hover { color: var(--red); }
.jam-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 8px; }
.jam-item { background: var(--off-white); border-radius: 8px; padding: 10px 14px; }
.jam-day { font-size: 0.75rem; font-weight: 600; color: var(--text-dark); }
.jam-hour { font-size: 0.78rem; color: var(--text-mid); margin-top: 2px; }
.jam-item.closed .jam-hour { color: var(--red); }
.btn-whatsapp { display: flex; align-items: center; gap: 12px; background: #25D366; color: white; font-family: var(--font-head); font-size: 0.9rem; font-weight: 700; padding: 14px 24px; border-radius: var(--radius); text-decoration: none; transition: var(--transition); }
.btn-whatsapp:hover { background: #1ebe5c; transform: translateY(-2px); box-shadow: 0 8px 24px rgba(37,211,102,0.4); }
.btn-whatsapp svg { width: 20px; height: 20px; fill: white; flex-shrink: 0; }
.divider { height: 1px; background: var(--light-gray); margin: 4px 0; }

/* ── MAP SECTION ─────────────────────────────────────────── */
#peta { background: var(--white); padding: 80px 48px; }
.peta-inner { max-width: 1200px; margin: 0 auto; }
.section-badge { display: inline-flex; align-items: center; gap: 8px; background: rgba(11,60,145,0.07); border: 1px solid rgba(11,60,145,0.15); color: var(--navy); font-size: 0.73rem; font-weight: 700; letter-spacing: 0.13em; text-transform: uppercase; padding: 7px 16px; border-radius: 99px; margin-bottom: 14px; }
.section-title { font-family: var(--font-head); font-size: clamp(1.6rem, 3vw, 2.3rem); font-weight: 800; color: var(--text-dark); line-height: 1.15; margin-bottom: 10px; }
.section-title .accent { color: var(--red); }
.section-sub { font-size: 0.92rem; color: var(--text-mid); max-width: 540px; line-height: 1.7; margin-bottom: 36px; }
.peta-grid { display: grid; grid-template-columns: 1fr 1.6fr; gap: 40px; align-items: start; }
.peta-info { display: flex; flex-direction: column; gap: 16px; }
.peta-detail-card { background: var(--off-white); border-radius: var(--radius-lg); padding: 28px; border: 1px solid var(--light-gray); }
.peta-detail-card h3 { font-family: var(--font-head); font-size: 0.95rem; font-weight: 700; color: var(--text-dark); margin-bottom: 16px; padding-bottom: 12px; border-bottom: 1px solid var(--light-gray); }
.peta-row { display: flex; align-items: flex-start; gap: 12px; margin-bottom: 14px; }
.peta-row:last-child { margin-bottom: 0; }
.peta-row svg { width: 16px; height: 16px; stroke: var(--navy); fill: none; stroke-width: 2; flex-shrink: 0; margin-top: 3px; }
.peta-row-text { font-size: 0.85rem; color: var(--text-mid); line-height: 1.5; }
.peta-row-text strong { display: block; font-weight: 600; color: var(--text-dark); margin-bottom: 2px; font-size: 0.82rem; }
.btn-direction { display: inline-flex; align-items: center; gap: 10px; background: var(--navy); color: white; font-family: var(--font-head); font-size: 0.85rem; font-weight: 700; padding: 12px 22px; border-radius: var(--radius); text-decoration: none; transition: var(--transition); }
.btn-direction:hover { background: var(--navy-dark); transform: translateY(-2px); box-shadow: 0 6px 20px rgba(11,60,145,0.35); }
.btn-direction svg { width: 16px; height: 16px; stroke: white; fill: none; stroke-width: 2; }
.peta-map { border-radius: var(--radius-lg); overflow: hidden; box-shadow: var(--shadow); border: 1px solid rgba(11,60,145,0.08); }
.peta-map iframe { display: block; width: 100%; height: 420px; border: none; }

/* ── FAQ SECTION ─────────────────────────────────────────── */
#faq { background: var(--off-white); padding: 80px 48px; }
.faq-inner { max-width: 1200px; margin: 0 auto; }
.faq-layout { display: grid; grid-template-columns: 1fr 2fr; gap: 64px; align-items: start; }
.faq-sticky { position: sticky; top: 100px; }
.faq-list { display: flex; flex-direction: column; gap: 12px; }
.faq-item { background: white; border-radius: var(--radius); border: 1px solid var(--light-gray); overflow: hidden; transition: var(--transition); }
.faq-item.open { border-color: rgba(11,60,145,0.2); box-shadow: var(--shadow); }
.faq-q { display: flex; align-items: center; justify-content: space-between; gap: 16px; padding: 20px 24px; cursor: pointer; user-select: none; }
.faq-q-text { font-family: var(--font-head); font-size: 0.93rem; font-weight: 700; color: var(--text-dark); line-height: 1.4; }
.faq-item.open .faq-q-text { color: var(--navy); }
.faq-toggle { width: 32px; height: 32px; border-radius: 8px; background: var(--off-white); display: flex; align-items: center; justify-content: center; flex-shrink: 0; transition: var(--transition); }
.faq-item.open .faq-toggle { background: var(--navy); }
.faq-toggle svg { width: 16px; height: 16px; stroke: var(--text-mid); fill: none; stroke-width: 2.5; transition: transform 0.3s ease; }
.faq-item.open .faq-toggle svg { stroke: white; transform: rotate(45deg); }
.faq-a { max-height: 0; overflow: hidden; transition: max-height 0.35s ease, padding 0.25s ease; padding: 0 24px; }
.faq-a-inner { font-size: 0.88rem; color: var(--text-mid); line-height: 1.75; padding-bottom: 20px; }
.faq-item.open .faq-a { max-height: 300px; }

/* ── CTA BANNER ──────────────────────────────────────────── */
#cta { background: linear-gradient(135deg, var(--navy-deeper), var(--navy)); padding: 80px 48px; position: relative; overflow: hidden; }
#cta::before { content:''; position: absolute; inset:0; background-image: url('data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNjAiIGhlaWdodD0iNjAiIHZpZXdCb3g9IjAgMCA2MCA2MCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48ZyBmaWxsPSJub25lIiBmaWxsLXJ1bGU9ImV2ZW5vZGQiPjxnIGZpbGw9IiNmZmZmZmYiIGZpbGwtb3BhY2l0eT0iMC4wMyI+PHBhdGggZD0iTTM2IDM0djZoNnYtNmgtNnptMCAwdi02aC02djZoNnptNiAwaDZ2LTZoLTZ2NnptLTEyIDBoLTZ2Nmg2di02eiIvPjwvZz48L2c+PC9zdmc+'); opacity: 0.4; }
.cta-accent { position: absolute; top: -80px; right: -80px; width: 400px; height: 400px; background: radial-gradient(circle, rgba(227,30,36,0.2) 0%, transparent 70%); pointer-events: none; }
.cta-inner { max-width: 1200px; margin: 0 auto; position: relative; z-index: 2; display: grid; grid-template-columns: 1fr auto; gap: 48px; align-items: center; }
.cta-text-badge { display: inline-flex; align-items: center; gap: 8px; background: rgba(227,30,36,0.18); border: 1px solid rgba(227,30,36,0.4); color: #ff8f91; font-size: 0.73rem; font-weight: 700; letter-spacing: 0.12em; text-transform: uppercase; padding: 6px 14px; border-radius: 99px; margin-bottom: 16px; }
.cta-title { font-family: var(--font-head); font-size: clamp(1.5rem, 3vw, 2.2rem); font-weight: 800; color: white; line-height: 1.2; margin-bottom: 14px; }
.cta-sub { font-size: 0.92rem; color: rgba(255,255,255,0.65); line-height: 1.7; max-width: 520px; }
.cta-actions { display: flex; flex-direction: column; gap: 12px; flex-shrink: 0; align-items: flex-end; }
.btn-cta-primary { display: inline-flex; align-items: center; gap: 10px; background: var(--red); color: white; font-family: var(--font-head); font-size: 0.9rem; font-weight: 700; padding: 15px 28px; border-radius: var(--radius); text-decoration: none; transition: var(--transition); white-space: nowrap; }
.btn-cta-primary:hover { background: var(--red-dark); transform: translateY(-2px); box-shadow: 0 8px 28px rgba(227,30,36,0.4); }
.btn-cta-secondary { display: inline-flex; align-items: center; gap: 10px; background: rgba(255,255,255,0.1); color: white; font-family: var(--font-head); font-size: 0.9rem; font-weight: 600; padding: 15px 28px; border-radius: var(--radius); text-decoration: none; border: 1.5px solid rgba(255,255,255,0.25); transition: var(--transition); white-space: nowrap; }
.btn-cta-secondary:hover { background: rgba(255,255,255,0.18); border-color: white; transform: translateY(-2px); }
.btn-cta-primary svg, .btn-cta-secondary svg { width: 17px; height: 17px; stroke: white; fill: none; stroke-width: 2; }

/* ── FOOTER ──────────────────────────────────────────────── */
footer { background: var(--navy-deeper); color: white; padding: 80px 48px 40px; }
.footer-grid { display: grid; grid-template-columns: 2fr 1fr 1fr 1.5fr; gap: 60px; margin-bottom: 60px; }
.footer-brand-name { font-family: var(--font-head); font-weight: 700; font-size: 1.05rem; color: white; line-height: 1.1; margin-bottom: 20px; }
.footer-brand-name span { display: block; font-size: 0.62rem; font-weight: 400; opacity: 0.55; letter-spacing: 0.08em; font-family: var(--font-body); }
.footer-about { font-size: 0.85rem; color: rgba(255,255,255,0.5); line-height: 1.7; margin-bottom: 28px; }
.footer-social { display: flex; gap: 12px; }
.social-btn { width: 38px; height: 38px; border-radius: 9px; background: rgba(255,255,255,0.07); border: 1px solid rgba(255,255,255,0.1); display: flex; align-items: center; justify-content: center; text-decoration: none; transition: var(--transition); }
.social-btn:hover { background: var(--red); border-color: var(--red); }
.social-btn svg { width: 16px; height: 16px; stroke: rgba(255,255,255,0.7); fill: none; stroke-width: 1.75; }
.footer-col-title { font-family: var(--font-head); font-size: 0.85rem; font-weight: 700; letter-spacing: 0.1em; text-transform: uppercase; color: white; margin-bottom: 20px; padding-bottom: 12px; border-bottom: 1px solid rgba(255,255,255,0.07); }
.footer-links { list-style: none; display: flex; flex-direction: column; gap: 10px; }
.footer-links a { color: rgba(255,255,255,0.5); text-decoration: none; font-size: 0.875rem; transition: color 0.2s; }
.footer-links a:hover { color: rgba(255,255,255,0.9); }
.footer-contact-item { display: flex; align-items: flex-start; gap: 12px; margin-bottom: 14px; }
.footer-contact-icon { width: 18px; height: 18px; stroke: rgba(255,255,255,0.35); fill: none; stroke-width: 1.75; flex-shrink: 0; margin-top: 2px; }
.footer-contact-text { font-size: 0.85rem; color: rgba(255,255,255,0.5); line-height: 1.5; }
.footer-bottom { border-top: 1px solid rgba(255,255,255,0.06); padding-top: 32px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px; }
.footer-copy { font-size: 0.82rem; color: rgba(255,255,255,0.35); }

/* ── RESPONSIVE ──────────────────────────────────────────── */
@media (max-width: 1100px) {
  .channel-inner { grid-template-columns: repeat(2, 1fr); }
  .footer-grid { grid-template-columns: 1fr 1fr; gap: 40px; }
  .cta-inner { grid-template-columns: 1fr; gap: 32px; }
  .cta-actions { flex-direction: row; align-items: flex-start; }
  .faq-layout { grid-template-columns: 1fr; }
  .faq-sticky { position: static; }
  .peta-grid { grid-template-columns: 1fr; }
}
@media (max-width: 900px) {
  .kontak-grid { grid-template-columns: 1fr; }
  .form-row { grid-template-columns: 1fr; }
}
@media (max-width: 768px) {
  #navbar { padding: 0 24px; }
  .nav-menu, .nav-actions { display: none; }
  .hamburger { display: flex; }
  .page-hero-content { padding: 100px 24px 60px; }
  #kontak-form, #peta, #faq, #cta, footer { padding-left: 24px; padding-right: 24px; }
  .channel-strip { padding: 0 24px; }
  .channel-inner { grid-template-columns: repeat(2, 1fr); transform: translateY(-36px); }
  .form-card { padding: 32px 28px; }
  .footer-grid { grid-template-columns: 1fr; gap: 32px; }
  .cta-actions { flex-direction: column; }
  .btn-cta-primary, .btn-cta-secondary { width: 100%; justify-content: center; }
}
@media (max-width: 480px) {
  .channel-inner { grid-template-columns: 1fr 1fr; gap: 12px; }
  .page-hero-chips { display: none; }
}
</style>
</head>
<body>

<!-- LOADER -->
<div id="loader">
  <div class="loader-logo-text">THREADB2B</div>
  <div class="loader-bar"><div class="loader-bar-fill"></div></div>
  <div class="loader-text">Memuat Halaman Kontak…</div>
</div>

<!-- MOBILE OVERLAY -->
<div class="mobile-overlay" id="mobileOverlay" onclick="closeMobileMenu()"></div>

<!-- MOBILE MENU -->
<div class="mobile-menu" id="mobileMenu">
  <a href="index.php"    class="mobile-nav-link">Beranda</a>
  <a href="about.php"    class="mobile-nav-link">Profil Perusahaan</a>
  <a href="index.php#produk" class="mobile-nav-link">Produk</a>
  <a href="kontak.php"   class="mobile-nav-link" style="color:var(--navy)">Kontak</a>
  <div class="mobile-actions">
    <a href="login.php"    class="btn-nav-login"    style="text-align:center;display:block;padding:10px">Masuk</a>
    <a href="register.php" class="btn-nav-register" style="text-align:center;display:block;padding:10px">Daftar</a>
  </div>
</div>

<!-- NAVBAR -->
<nav id="navbar">
  <a href="index.php" class="nav-logo">
    <div class="nav-logo-box">
      <svg viewBox="0 0 24 24"><path d="M12 2L2 7l10 5 10-5-10-5z"/><path d="M2 17l10 5 10-5"/><path d="M2 12l10 5 10-5"/></svg>
    </div>
    <div class="nav-logo-text">THREADB2B<span>Premium Yarn B2B Platform</span></div>
  </a>
  <ul class="nav-menu">
    <li><a href="index.php"         class="nav-link">Beranda</a></li>
    <li><a href="about.php"         class="nav-link">Profil Perusahaan</a></li>
    <li><a href="index.php#produk"  class="nav-link">Produk</a></li>
    <li><a href="kontak.php"        class="nav-link active">Kontak</a></li>
  </ul>
  <div class="nav-actions">
    <a href="login.php"    class="btn-nav-login">Masuk</a>
    <a href="register.php" class="btn-nav-register">Daftar</a>
  </div>
  <button class="hamburger" id="hamburger" onclick="toggleMobileMenu()">
    <span></span><span></span><span></span>
  </button>
</nav>

<!-- PAGE HERO -->
<div class="page-hero">
  <div class="page-hero-pattern"></div>
  <div class="page-hero-accent"></div>
  <div class="page-hero-accent-2"></div>
  <div class="page-hero-content">
    <div class="breadcrumb">
      <a href="index.php">Beranda</a>
      <span class="breadcrumb-sep">/</span>
      <span class="breadcrumb-current">Kontak</span>
    </div>
    <div class="page-hero-badge">
      <span class="badge-dot"></span>
      Kami Siap Membantu Anda
    </div>
    <h1 class="page-hero-title">
      Hubungi <span class="accent">Tim Kami</span>
    </h1>
    <p class="page-hero-sub">
      Punya pertanyaan tentang produk, ingin mengajukan sample, atau siap bermitra?
      Tim profesional kami siap merespons dalam 1×24 jam kerja.
    </p>
    <div class="page-hero-chips">
      <span class="hero-chip">
        <svg viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
        Respons Cepat 1×24 Jam
      </span>
      <span class="hero-chip">
        <svg viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
        Konsultasi Teknis Gratis
      </span>
      <span class="hero-chip">
        <svg viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
        Sample Request Tersedia
      </span>
    </div>
  </div>
</div>

<!-- CHANNEL CARDS -->
<div class="channel-strip">
  <div class="channel-inner">
    <a href="tel:+620254401122" class="channel-card phone reveal reveal-delay-1">
      <div class="channel-icon">
        <svg viewBox="0 0 24 24"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07A19.5 19.5 0 0 1 4.69 12 19.79 19.79 0 0 1 1.61 3.18 2 2 0 0 1 3.6 1h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 8.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
      </div>
      <div class="channel-label">Telepon</div>
      <div class="channel-value"><?= htmlspecialchars($company['phone']) ?></div>
      <div class="channel-sub">Senin – Jumat, 08.00–17.00</div>
    </a>
    <a href="mailto:<?= htmlspecialchars($company['email']) ?>" class="channel-card email reveal reveal-delay-2">
      <div class="channel-icon">
        <svg viewBox="0 0 24 24"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
      </div>
      <div class="channel-label">Email</div>
      <div class="channel-value"><?= htmlspecialchars($company['email']) ?></div>
      <div class="channel-sub">Balas dalam 1×24 jam kerja</div>
    </a>
    <a href="https://wa.me/6225440112?text=Halo,%20saya%20ingin%20bertanya%20tentang%20produk%20benang%20THREADB2B" target="_blank" class="channel-card whatsapp reveal reveal-delay-3">
      <div class="channel-icon">
        <svg viewBox="0 0 24 24"><path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"/></svg>
      </div>
      <div class="channel-label">WhatsApp</div>
      <div class="channel-value">Chat Langsung</div>
      <div class="channel-sub">Respons lebih cepat</div>
    </a>
    <a href="https://maps.google.com/?q=Cikande+Serang+Banten+Indonesia" target="_blank" class="channel-card visit reveal reveal-delay-4">
      <div class="channel-icon">
        <svg viewBox="0 0 24 24"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
      </div>
      <div class="channel-label">Kunjungi Kami</div>
      <div class="channel-value">Cikande, Serang</div>
      <div class="channel-sub">Banten, Indonesia</div>
    </a>
  </div>
</div>

<!-- FORM + INFO -->
<section id="kontak-form">
  <div class="kontak-inner">
    <div class="kontak-grid">

      <!-- FORM KIRI -->
      <div class="form-card reveal">
        <div class="form-card-title">Kirim Pesan</div>
        <div class="form-card-sub">Isi formulir di bawah ini dan tim kami akan menghubungi Anda segera.</div>

        <div id="formAlert" class="form-alert" style="display:none">
          <svg viewBox="0 0 24 24" id="alertIcon"></svg>
          <span id="alertText"></span>
        </div>

        <div class="form-row">
          <div class="form-group">
            <label class="form-label" for="inputNama">Nama Lengkap <span class="req">*</span></label>
            <input class="form-input" id="inputNama" type="text" placeholder="Contoh: Budi Santoso">
          </div>
          <div class="form-group">
            <label class="form-label" for="inputPerusahaan">Nama Perusahaan <span class="req">*</span></label>
            <input class="form-input" id="inputPerusahaan" type="text" placeholder="Contoh: PT Garmen Makmur">
          </div>
        </div>

        <div class="form-row">
          <div class="form-group">
            <label class="form-label" for="inputEmail">Email <span class="req">*</span></label>
            <input class="form-input" id="inputEmail" type="email" placeholder="email@perusahaan.com">
          </div>
          <div class="form-group">
            <label class="form-label" for="inputTelepon">Nomor WhatsApp / Telepon</label>
            <input class="form-input" id="inputTelepon" type="tel" placeholder="+62 8xx xxxx xxxx">
          </div>
        </div>

        <div class="form-group">
          <label class="form-label" for="inputTopik">Topik Pertanyaan <span class="req">*</span></label>
          <select class="form-select" id="inputTopik">
            <option value="" disabled selected>— Pilih topik —</option>
            <option value="Informasi Produk">Informasi Produk</option>
            <option value="Sample Request">Permintaan Sample</option>
            <option value="Harga & Penawaran">Harga &amp; Penawaran</option>
            <option value="Pengiriman & Ekspor">Pengiriman &amp; Ekspor</option>
            <option value="Kerjasama B2B">Kerjasama B2B</option>
            <option value="Keluhan / After Sales">Keluhan / After Sales</option>
            <option value="Lainnya">Lainnya</option>
          </select>
        </div>

        <div class="form-group">
          <label class="form-label" for="inputPesan">Pesan <span class="req">*</span></label>
          <textarea class="form-textarea" id="inputPesan" placeholder="Tuliskan detail pertanyaan atau kebutuhan Anda di sini…"></textarea>
        </div>

        <button class="btn-submit" id="btnKirim" onclick="kirimPesan()">
          <svg viewBox="0 0 24 24"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
          Kirim Pesan
        </button>
        <div class="form-note">
          <svg viewBox="0 0 24 24"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
          Data Anda aman dan tidak akan dibagikan kepada pihak ketiga.
        </div>
      </div>

      <!-- INFO KANAN -->
      <div class="info-col reveal reveal-delay-2">
        <div>
          <div class="info-section-title">Informasi Kontak</div>
          <div class="info-section-sub">Pilih cara yang paling nyaman untuk menghubungi kami.</div>
        </div>

        <div class="info-card">
          <div class="info-card-icon">
            <svg viewBox="0 0 24 24"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
          </div>
          <div class="info-card-text">
            <strong>Alamat Pabrik</strong>
            <span><?= htmlspecialchars($company['alamat']) ?></span>
          </div>
        </div>

        <div class="info-card">
          <div class="info-card-icon">
            <svg viewBox="0 0 24 24"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07A19.5 19.5 0 0 1 4.69 12 19.79 19.79 0 0 1 1.61 3.18 2 2 0 0 1 3.6 1h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 8.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
          </div>
          <div class="info-card-text">
            <strong>Telepon</strong>
            <span>
              <a href="tel:+620254401122"><?= htmlspecialchars($company['phone']) ?></a>
            </span>
          </div>
        </div>

        <div class="info-card">
          <div class="info-card-icon red">
            <svg viewBox="0 0 24 24"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
          </div>
          <div class="info-card-text">
            <strong>Email</strong>
            <span><a href="mailto:<?= htmlspecialchars($company['email']) ?>"><?= htmlspecialchars($company['email']) ?></a></span>
          </div>
        </div>

        <div class="info-card">
          <div class="info-card-icon">
            <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
          </div>
          <div class="info-card-text">
            <strong>Jam Operasional</strong>
            <div class="jam-grid" style="margin-top:10px">
              <div class="jam-item">
                <div class="jam-day">Senin – Jumat</div>
                <div class="jam-hour">08.00 – 17.00 WIB</div>
              </div>
              <div class="jam-item">
                <div class="jam-day">Sabtu</div>
                <div class="jam-hour">08.00 – 13.00 WIB</div>
              </div>
              <div class="jam-item closed">
                <div class="jam-day">Minggu</div>
                <div class="jam-hour">Tutup</div>
              </div>
              <div class="jam-item closed">
                <div class="jam-day">Hari Libur Nasional</div>
                <div class="jam-hour">Tutup</div>
              </div>
            </div>
          </div>
        </div>

        <div class="divider"></div>

        <a href="https://wa.me/6225440112?text=Halo,%20saya%20ingin%20bertanya%20tentang%20produk%20benang%20THREADB2B" target="_blank" class="btn-whatsapp">
          <svg viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 0 1-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 0 1-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 0 1 2.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0 0 12.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 0 0 5.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 0 0-3.48-8.413z"/></svg>
          Chat via WhatsApp
        </a>
      </div>

    </div>
  </div>
</section>

<!-- MAP -->
<section id="peta">
  <div class="peta-inner">
    <div class="reveal">
      <div class="section-badge">Temukan Kami</div>
      <h2 class="section-title">Lokasi <span class="accent">Pabrik</span></h2>
      <p class="section-sub">Kunjungi fasilitas produksi kami di kawasan industri Cikande, Serang, Banten — mudah diakses dari Tol Tangerang–Merak.</p>
    </div>
    <div class="peta-grid reveal">
      <div class="peta-info">
        <div class="peta-detail-card">
          <h3>Detail Lokasi</h3>
          <div class="peta-row">
            <svg viewBox="0 0 24 24"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
            <div class="peta-row-text">
              <strong>Alamat</strong>
              <?= htmlspecialchars($company['alamat']) ?>
            </div>
          </div>
          <div class="peta-row">
            <svg viewBox="0 0 24 24"><rect x="1" y="3" width="15" height="13" rx="1"/><path d="M16 8h4l3 3v5h-7V8z"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/></svg>
            <div class="peta-row-text">
              <strong>Akses Kendaraan</strong>
              Keluar Tol Cikande, ±2 km dari gerbang tol. Tersedia area parkir truk dan kontainer.
            </div>
          </div>
          <div class="peta-row">
            <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
            <div class="peta-row-text">
              <strong>Jam Kunjungan</strong>
              Senin – Jumat: 08.00 – 16.00 WIB (janji terlebih dahulu)
            </div>
          </div>
        </div>
        <a href="https://maps.google.com/?q=Jalan+Raya+Serang+Km+62+Cikande+Serang+Banten" target="_blank" class="btn-direction">
          <svg viewBox="0 0 24 24"><polygon points="3 11 22 2 13 21 11 13 3 11"/></svg>
          Buka di Google Maps
        </a>
      </div>
      <div class="peta-map">
        <?php
          $mapsUrl = $company['maps'] ?? '';
          if (!empty($mapsUrl) && strpos($mapsUrl, 'embed') !== false):
        ?>
          <iframe src="<?= htmlspecialchars($mapsUrl) ?>" allowfullscreen loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
        <?php else: ?>
          <iframe
            src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d15862.7!2d106.3872!3d-6.2441!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x2e41f9c88d4e4e5b%3A0x0!2sCikande%2C+Serang%2C+Banten!5e0!3m2!1sid!2sid!4v1625000000000!5m2!1sid!2sid"
            allowfullscreen loading="lazy" referrerpolicy="no-referrer-when-downgrade">
          </iframe>
        <?php endif; ?>
      </div>
    </div>
  </div>
</section>

<!-- FAQ -->
<section id="faq">
  <div class="faq-inner">
    <div class="faq-layout">
      <div class="faq-sticky reveal">
        <div class="section-badge">FAQ</div>
        <h2 class="section-title">Pertanyaan <span class="accent">Umum</span></h2>
        <p class="section-sub">Jawaban atas pertanyaan yang paling sering kami terima dari mitra bisnis baru maupun yang sudah lama bermitra.</p>
        <div style="margin-top:28px">
          <a href="kontak.php#kontak-form" class="btn-direction">
            <svg viewBox="0 0 24 24"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
            Masih ada pertanyaan?
          </a>
        </div>
      </div>
      <div class="faq-list reveal reveal-delay-2">
        <?php foreach ($faq_rows as $i => $faq): ?>
        <div class="faq-item" id="faq-<?= $i ?>">
          <div class="faq-q" onclick="toggleFaq(<?= $i ?>)">
            <div class="faq-q-text"><?= htmlspecialchars($faq['q']) ?></div>
            <div class="faq-toggle">
              <svg viewBox="0 0 24 24"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            </div>
          </div>
          <div class="faq-a">
            <div class="faq-a-inner"><?= htmlspecialchars($faq['a']) ?></div>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
</section>

<!-- CTA BANNER -->
<section id="cta">
  <div class="cta-accent"></div>
  <div class="cta-inner">
    <div>
      <div class="cta-text-badge">Mulai Bermitra</div>
      <h2 class="cta-title">Siap Menjadi Buyer THREADB2B?</h2>
      <p class="cta-sub">Daftarkan perusahaan Anda, dapatkan akses ke ratusan produk benang premium, harga khusus partner, dan layanan B2B eksklusif.</p>
    </div>
    <div class="cta-actions">
      <a href="register.php" class="btn-cta-primary">
        <svg viewBox="0 0 24 24"><path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="8.5" cy="7" r="4"/><line x1="20" y1="8" x2="20" y2="14"/><line x1="23" y1="11" x2="17" y2="11"/></svg>
        Daftar Sekarang
      </a>
      <a href="index.php#produk" class="btn-cta-secondary">
        <svg viewBox="0 0 24 24"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/></svg>
        Lihat Katalog Produk
      </a>
    </div>
  </div>
</section>

<!-- FOOTER -->
<footer>
  <div class="footer-grid">
    <div>
      <div class="footer-brand-name">
        THREADB2B
        <span>PT Sing Long Brothers Industrial</span>
      </div>
      <div class="footer-about">
        Produsen dan distributor benang jahit &amp; yarn dyeing premium di Cikande, Serang, Banten.
        Melayani industri tekstil domestik &amp; ekspor lebih dari 30 tahun.
      </div>
      <div class="footer-social">
        <a href="#" class="social-btn" title="LinkedIn"><svg viewBox="0 0 24 24"><path d="M16 8a6 6 0 0 1 6 6v7h-4v-7a2 2 0 0 0-2-2 2 2 0 0 0-2 2v7h-4v-7a6 6 0 0 1 6-6z"/><rect x="2" y="9" width="4" height="12"/><circle cx="4" cy="4" r="2"/></svg></a>
        <a href="#" class="social-btn" title="Instagram"><svg viewBox="0 0 24 24"><rect x="2" y="2" width="20" height="20" rx="5" ry="5"/><path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z"/><line x1="17.5" y1="6.5" x2="17.51" y2="6.5"/></svg></a>
        <a href="#" class="social-btn" title="WhatsApp"><svg viewBox="0 0 24 24"><path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"/></svg></a>
      </div>
    </div>
    <div>
      <div class="footer-col-title">Navigasi</div>
      <ul class="footer-links">
        <li><a href="index.php">Beranda</a></li>
        <li><a href="about.php">Profil Perusahaan</a></li>
        <li><a href="index.php#produk">Produk</a></li>
        <li><a href="index.php#sertifikasi">Sertifikasi</a></li>
        <li><a href="kontak.php">Kontak</a></li>
      </ul>
    </div>
    <div>
      <div class="footer-col-title">Layanan</div>
      <ul class="footer-links">
        <li><a href="#">Sewing Thread</a></li>
        <li><a href="#">Yarn Dyeing</a></li>
        <li><a href="#">Sample Request</a></li>
        <li><a href="#">B2B Portal</a></li>
        <li><a href="#">Ekspor</a></li>
      </ul>
    </div>
    <div>
      <div class="footer-col-title">Kontak</div>
      <div class="footer-contact-item">
        <svg class="footer-contact-icon" viewBox="0 0 24 24"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
        <div class="footer-contact-text"><?= htmlspecialchars($company['alamat']) ?></div>
      </div>
      <div class="footer-contact-item">
        <svg class="footer-contact-icon" viewBox="0 0 24 24"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07A19.5 19.5 0 0 1 4.69 12 19.79 19.79 0 0 1 1.61 3.18 2 2 0 0 1 3.6 1h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L8.09 8.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
        <div class="footer-contact-text"><?= htmlspecialchars($company['phone']) ?></div>
      </div>
      <div class="footer-contact-item">
        <svg class="footer-contact-icon" viewBox="0 0 24 24"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
        <div class="footer-contact-text"><?= htmlspecialchars($company['email']) ?></div>
      </div>
    </div>
  </div>
  <div class="footer-bottom">
    <div class="footer-copy">© <?= date('Y') ?> PT Sing Long Brothers Industrial. All rights reserved. | THREADB2B Platform</div>
    <div class="footer-copy">Cikande, Serang, Banten, Indonesia</div>
  </div>
</footer>

<script>
// ── LOADER ─────────────────────────────────────────────────────
window.addEventListener('load', () => {
  setTimeout(() => document.getElementById('loader').classList.add('hidden'), 1300);
});

// ── NAVBAR SCROLL ──────────────────────────────────────────────
window.addEventListener('scroll', () => {
  document.getElementById('navbar').classList.toggle('scrolled', window.scrollY > 60);
});

// ── MOBILE MENU ────────────────────────────────────────────────
function toggleMobileMenu() {
  document.getElementById('mobileMenu').classList.toggle('open');
  document.getElementById('mobileOverlay').classList.toggle('active');
  document.getElementById('hamburger').classList.toggle('open');
}
function closeMobileMenu() {
  document.getElementById('mobileMenu').classList.remove('open');
  document.getElementById('mobileOverlay').classList.remove('active');
  document.getElementById('hamburger').classList.remove('open');
}

// ── SCROLL REVEAL ──────────────────────────────────────────────
const revealObs = new IntersectionObserver(entries => {
  entries.forEach(e => {
    if (e.isIntersecting) { e.target.classList.add('visible'); revealObs.unobserve(e.target); }
  });
}, { threshold: 0.10, rootMargin: '0px 0px -40px 0px' });
document.querySelectorAll('.reveal').forEach(el => revealObs.observe(el));

// ── FAQ ACCORDION ──────────────────────────────────────────────
function toggleFaq(index) {
  const item = document.getElementById('faq-' + index);
  const isOpen = item.classList.contains('open');
  // tutup semua
  document.querySelectorAll('.faq-item.open').forEach(el => el.classList.remove('open'));
  // buka yg diklik jika sebelumnya tertutup
  if (!isOpen) item.classList.add('open');
}

// ── FORM KIRIM (AJAX) ──────────────────────────────────────────
function kirimPesan() {
  const btn      = document.getElementById('btnKirim');
  const nama     = document.getElementById('inputNama').value.trim();
  const email    = document.getElementById('inputEmail').value.trim();
  const perusahaan = document.getElementById('inputPerusahaan').value.trim();
  const telepon  = document.getElementById('inputTelepon').value.trim();
  const topik    = document.getElementById('inputTopik').value;
  const pesan    = document.getElementById('inputPesan').value.trim();

  if (!nama || !email || !perusahaan || !topik || !pesan) {
    showAlert('error',
      '<line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>',
      'Harap isi semua field yang wajib diisi (*).'
    );
    return;
  }
  // Validasi email sederhana
  if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
    showAlert('error',
      '<line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>',
      'Format email tidak valid.'
    );
    return;
  }

  btn.disabled = true;
  btn.innerHTML = `
    <svg viewBox="0 0 24 24" style="width:18px;height:18px;stroke:white;fill:none;stroke-width:2;animation:spin 1s linear infinite">
      <path d="M21 12a9 9 0 1 1-6.219-8.56"/>
    </svg>
    Mengirim…`;

  const fd = new FormData();
  fd.append('nama',        nama);
  fd.append('email',       email);
  fd.append('perusahaan',  perusahaan);
  fd.append('telepon',     telepon);
  fd.append('topik',       topik);
  fd.append('pesan',       pesan);

  fetch('submit_kontak.php', { method: 'POST', body: fd })
    .then(r => r.json())
    .then(res => {
      if (res.success) {
        showAlert('success',
          '<polyline points="20 6 9 17 4 12"/>',
          res.message || 'Pesan berhasil terkirim! Tim kami akan menghubungi Anda segera.'
        );
        ['inputNama','inputEmail','inputPerusahaan','inputTelepon','inputPesan'].forEach(id => {
          document.getElementById(id).value = '';
        });
        document.getElementById('inputTopik').selectedIndex = 0;
      } else {
        showAlert('error',
          '<line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>',
          res.message || 'Terjadi kesalahan. Silakan coba lagi.'
        );
      }
    })
    .catch(() => {
      showAlert('error',
        '<line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>',
        'Koneksi bermasalah. Silakan hubungi kami via WhatsApp.'
      );
    })
    .finally(() => {
      btn.disabled = false;
      btn.innerHTML = `
        <svg viewBox="0 0 24 24" style="width:18px;height:18px;stroke:white;fill:none;stroke-width:2">
          <line x1="22" y1="2" x2="11" y2="13"/>
          <polygon points="22 2 15 22 11 13 2 9 22 2"/>
        </svg>
        Kirim Pesan`;
    });
}

function showAlert(type, iconPath, msg) {
  const el   = document.getElementById('formAlert');
  const icon = document.getElementById('alertIcon');
  const text = document.getElementById('alertText');
  el.className   = 'form-alert ' + type;
  el.style.display = 'flex';
  icon.innerHTML = iconPath;
  text.textContent = msg;
  el.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
  setTimeout(() => { el.style.display = 'none'; }, 8000);
}

// ── SMOOTH SCROLL ──────────────────────────────────────────────
document.querySelectorAll('a[href^="#"]').forEach(a => {
  a.addEventListener('click', e => {
    const t = document.querySelector(a.getAttribute('href'));
    if (t) { e.preventDefault(); t.scrollIntoView({ behavior: 'smooth', block: 'start' }); }
  });
});

const style = document.createElement('style');
style.textContent = '@keyframes spin { to { transform: rotate(360deg); } }';
document.head.appendChild(style);
</script>
</body>
</html>
