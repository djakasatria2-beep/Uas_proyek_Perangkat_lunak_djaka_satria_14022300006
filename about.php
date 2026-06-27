<?php
// ============================================================
// about.php — Profil Perusahaan PT Sing Long Brothers Industrial
// Tabel pakai: company_profile, certificates, gallery
// ============================================================

require_once __DIR__ . '/assets/config.php';
$db = getDB();

// ── company_profile ───────────────────────────────────────────
$company = $db->query("SELECT * FROM company_profile LIMIT 1")->fetch_assoc();
$company = $company ?: [
    'nama_pt'         => 'PT Sing Long Brothers Industrial',
    'tentang_company' => 'PT Sing Long Brothers Industrial adalah produsen dan distributor benang jahit serta benang pewarna (Sewing Thread & Yarn Dyeing) berkualitas tinggi yang telah melayani industri tekstil nasional dan internasional selama puluhan tahun. Berlokasi di kawasan industri Serang, Banten, kami berkomitmen menghadirkan produk benang presisi tinggi dengan standar internasional.',
    'visi'            => 'Menjadi produsen benang jahit dan benang pewarna terpercaya dan terdepan di Asia Tenggara yang diakui atas kualitas, konsistensi, dan inovasi berkelanjutan.',
    'misi'            => "Menghasilkan produk benang dengan standar kualitas internasional menggunakan teknologi terkini.\nMemberikan layanan responsif dan profesional kepada seluruh mitra bisnis.\nMendukung pertumbuhan industri tekstil nasional melalui produk berkualitas dan harga kompetitif.\nBerkomitmen terhadap praktik bisnis yang ramah lingkungan dan berkelanjutan.",
    'sejarah'         => 'Berdiri sejak era industri tekstil Indonesia mulai berkembang pesat, PT Sing Long Brothers Industrial telah melewati berbagai generasi sebagai pemasok benang pilihan bagi industri garmen dan konveksi. Berawal dari usaha keluarga yang fokus pada benang jahit, perusahaan terus bertumbuh dan kini menjadi mitra strategis bagi ratusan pabrik tekstil di seluruh Indonesia dan Asia.',
    'alamat'          => 'Factory: Jalan Raya Serang Km. 62, Cikande, Serang, Banten, Indonesia',
    'email'           => 'info@singlongbrothers.co.id',
    'phone'           => '(0254) 401122, 401123, 401124',
    'maps'            => 'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3965.0!2d106.417!3d-6.244!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x0%3A0x0!2zNsKwMTQnMzguNCJTIDEwNsKwMjUnMDEuMiJF!5e0!3m2!1sid!2sid!4v1625000000000!5m2!1sid!2sid',
];

// ── certificates ──────────────────────────────────────────────
$cert_rows = $db->query("SELECT * FROM certificates ORDER BY tahun DESC")->fetch_all(MYSQLI_ASSOC);
if (empty($cert_rows)) {
    $cert_rows = [
        ['id_certificate' => 1, 'nama_sertifikat' => 'ISO 9001:2015 Quality Management',  'tahun' => 2024, 'gambar' => null],
        ['id_certificate' => 2, 'nama_sertifikat' => 'OEKO-TEX Standard 100',             'tahun' => 2025, 'gambar' => null],
        ['id_certificate' => 3, 'nama_sertifikat' => 'Global Recycled Standard (GRS)',    'tahun' => 2025, 'gambar' => null],
        ['id_certificate' => 4, 'nama_sertifikat' => 'SNI Sertifikat Nasional Indonesia', 'tahun' => 2023, 'gambar' => null],
    ];
}

// ── gallery ───────────────────────────────────────────────────
$gallery_rows = $db->query("SELECT * FROM gallery ORDER BY id_gallery ASC LIMIT 6")->fetch_all(MYSQLI_ASSOC);

// ── Tim / Struktur statis ─────────────────────────────────────
$tim_rows = [
    ['nama' => 'Director',           'dept' => 'Direksi',            'inisial' => 'D',  'grad' => 'linear-gradient(135deg,#0b3c91,#1a5ed6)'],
    ['nama' => 'General Manager',    'dept' => 'Manajemen Umum',     'inisial' => 'GM', 'grad' => 'linear-gradient(135deg,#E31E24,#b8181d)'],
    ['nama' => 'Production Manager', 'dept' => 'Produksi',           'inisial' => 'PM', 'grad' => 'linear-gradient(135deg,#16a085,#1abc9c)'],
    ['nama' => 'QC Manager',         'dept' => 'Quality Control',    'inisial' => 'QC', 'grad' => 'linear-gradient(135deg,#6a3093,#a044ff)'],
    ['nama' => 'Sales Manager',      'dept' => 'Penjualan & Ekspor', 'inisial' => 'SM', 'grad' => 'linear-gradient(135deg,#c0392b,#e67e22)'],
    ['nama' => 'R&D Manager',        'dept' => 'Riset & Pengembangan','inisial'=>'RD',  'grad' => 'linear-gradient(135deg,#2c3e50,#3d5a74)'],
];

// ── Statistik ─────────────────────────────────────────────────
$stat_rows = [
    ['label' => 'Tahun Berdiri',       'nilai' => 1992, 'satuan' => '',  'icon' => '<circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/>'],
    ['label' => 'Klien Aktif',         'nilai' => 300,  'satuan' => '+', 'icon' => '<path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/>'],
    ['label' => 'Jenis Produk',        'nilai' => 200,  'satuan' => '+', 'icon' => '<path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/>'],
    ['label' => 'Negara Tujuan Ekspor','nilai' => 15,   'satuan' => '+', 'icon' => '<circle cx="12" cy="12" r="10"/><line x1="2" y1="12" x2="22" y2="12"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/>'],
];
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Profil Perusahaan – PT Sing Long Brothers Industrial | THREADB2B</title>
<meta name="description" content="Mengenal PT Sing Long Brothers Industrial, produsen benang jahit & yarn dyeing premium di Serang, Banten, Indonesia.">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;500;600;700;800&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
<style>
/* ── DESIGN TOKENS (sama dengan index.php) ───────────────── */
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
html { scroll-behavior: smooth; font-size: 16px; }
body { font-family: var(--font-body); color: var(--text-dark); background: var(--white); overflow-x: hidden; line-height: 1.6; }

/* ── LOADER ─────────────────────────────────────────────────── */
#loader { position: fixed; inset: 0; background: var(--navy-deeper); z-index: 9999; display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 24px; transition: opacity 0.5s ease, visibility 0.5s ease; }
#loader.hidden { opacity: 0; visibility: hidden; }
.loader-bar { width: 200px; height: 3px; background: rgba(255,255,255,0.15); border-radius: 99px; overflow: hidden; }
.loader-bar-fill { height: 100%; width: 0%; background: linear-gradient(90deg, var(--red), var(--navy)); border-radius: 99px; animation: loadFill 1.4s ease forwards; }
@keyframes loadFill { to { width: 100%; } }
.loader-text { color: rgba(255,255,255,0.5); font-family: var(--font-body); font-size: 0.8rem; letter-spacing: 0.15em; text-transform: uppercase; }
.loader-logo-text { font-family: var(--font-head); font-size: 1.8rem; font-weight: 800; color: white; }

/* ── REVEAL ─────────────────────────────────────────────────── */
.reveal { opacity: 0; transform: translateY(32px); transition: opacity 0.7s ease, transform 0.7s ease; }
.reveal.visible { opacity: 1; transform: translateY(0); }
.reveal-delay-1 { transition-delay: 0.1s; }
.reveal-delay-2 { transition-delay: 0.2s; }
.reveal-delay-3 { transition-delay: 0.3s; }
.reveal-delay-4 { transition-delay: 0.4s; }

/* ── NAVBAR ─────────────────────────────────────────────────── */
#navbar { position: fixed; top: 0; left: 0; right: 0; z-index: 1000; padding: 0 48px; height: 72px; display: flex; align-items: center; justify-content: space-between; transition: var(--transition); background: transparent; }
#navbar.scrolled { background: rgba(255,255,255,0.97); backdrop-filter: blur(16px); box-shadow: 0 2px 20px rgba(11,60,145,0.10); height: 64px; }
#navbar.scrolled .nav-logo-text { color: var(--navy); }
#navbar.scrolled .nav-link { color: var(--text-dark); }
#navbar.scrolled .nav-link:hover { color: var(--navy); }
#navbar.scrolled .hamburger span { background: var(--text-dark); }
.nav-logo { display: flex; align-items: center; gap: 12px; text-decoration: none; }
.nav-logo-box { width: 38px; height: 38px; background: var(--red); border-radius: 9px; display: flex; align-items: center; justify-content: center; }
.nav-logo-box svg { width: 20px; height: 20px; stroke: white; fill: none; stroke-width: 2; }
.nav-logo-text { font-family: var(--font-head); font-weight: 700; font-size: 1.1rem; color: var(--white); transition: var(--transition); line-height: 1.1; }
.nav-logo-text span { display: block; font-size: 0.65rem; font-weight: 400; letter-spacing: 0.1em; opacity: 0.75; font-family: var(--font-body); }
.nav-menu { display: flex; align-items: center; gap: 4px; list-style: none; }
.nav-link { color: rgba(255,255,255,0.9); text-decoration: none; font-size: 0.875rem; font-weight: 500; padding: 8px 14px; border-radius: 8px; transition: var(--transition); position: relative; }
.nav-link:hover { color: var(--white); background: rgba(255,255,255,0.12); }
.nav-link.active { color: var(--white); }
.nav-link.active::after { content:''; position: absolute; bottom: 4px; left: 50%; transform: translateX(-50%); width: 20px; height: 2px; background: var(--red); border-radius: 99px; }
#navbar.scrolled .nav-link.active { color: var(--navy); }
#navbar.scrolled .nav-link.active::after { background: var(--red); }
.nav-actions { display: flex; align-items: center; gap: 10px; }
.btn-nav-login { background: transparent; border: 1.5px solid rgba(255,255,255,0.6); color: var(--white); font-family: var(--font-body); font-size: 0.85rem; font-weight: 500; padding: 8px 18px; border-radius: 8px; cursor: pointer; transition: var(--transition); text-decoration: none; }
.btn-nav-login:hover { background: rgba(255,255,255,0.15); }
#navbar.scrolled .btn-nav-login { border-color: var(--navy); color: var(--navy); }
#navbar.scrolled .btn-nav-login:hover { background: var(--navy); color: white; }
.btn-nav-register { background: var(--red); border: none; color: var(--white); font-family: var(--font-body); font-size: 0.85rem; font-weight: 600; padding: 8px 20px; border-radius: 8px; cursor: pointer; transition: var(--transition); text-decoration: none; }
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
.mobile-menu .mobile-actions { margin-top: 24px; display: flex; flex-direction: column; gap: 10px; }

/* ── PAGE HERO ───────────────────────────────────────────────── */
.page-hero { min-height: 420px; background: linear-gradient(135deg, var(--navy-deeper) 0%, var(--navy) 60%, #1a4fa8 100%); position: relative; display: flex; align-items: center; overflow: hidden; }
.page-hero-pattern { position: absolute; inset: 0; background-image: url('data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNjAiIGhlaWdodD0iNjAiIHZpZXdCb3g9IjAgMCA2MCA2MCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48ZyBmaWxsPSJub25lIiBmaWxsLXJ1bGU9ImV2ZW5vZGQiPjxnIGZpbGw9IiNmZmZmZmYiIGZpbGwtb3BhY2l0eT0iMC4wMyI+PHBhdGggZD0iTTM2IDM0djZoNnYtNmgtNnptMCAwdi02aC02djZoNnptNiAwaDZ2LTZoLTZ2NnptLTEyIDBoLTZ2Nmg2di02eiIvPjwvZz48L2c+PC9zdmc+'); opacity: 0.5; }
.page-hero-accent { position: absolute; top: -80px; right: -80px; width: 480px; height: 480px; background: radial-gradient(circle, rgba(227,30,36,0.20) 0%, transparent 70%); pointer-events: none; }
.page-hero-content { position: relative; z-index: 2; max-width: 1200px; margin: 0 auto; padding: 120px 48px 80px; width: 100%; }
.page-hero-breadcrumb { display: flex; align-items: center; gap: 8px; margin-bottom: 20px; }
.breadcrumb-item { font-size: 0.8rem; color: rgba(255,255,255,0.55); text-decoration: none; transition: color 0.2s; }
.breadcrumb-item:hover { color: rgba(255,255,255,0.9); }
.breadcrumb-sep { color: rgba(255,255,255,0.3); font-size: 0.8rem; }
.breadcrumb-current { font-size: 0.8rem; color: rgba(255,255,255,0.9); font-weight: 500; }
.page-hero-badge { display: inline-flex; align-items: center; gap: 8px; background: rgba(227,30,36,0.18); border: 1px solid rgba(227,30,36,0.4); color: #ff8f91; font-size: 0.75rem; font-weight: 600; letter-spacing: 0.12em; text-transform: uppercase; padding: 7px 16px; border-radius: 99px; margin-bottom: 20px; }
.page-hero-badge-dot { width: 6px; height: 6px; background: var(--red); border-radius: 50%; animation: blink 1.4s ease-in-out infinite; }
@keyframes blink { 0%,100%{opacity:1}50%{opacity:0.2} }
.page-hero-title { font-family: var(--font-head); font-size: clamp(2rem, 4vw, 3.2rem); font-weight: 800; color: var(--white); line-height: 1.1; margin-bottom: 16px; letter-spacing: -0.02em; }
.page-hero-title .accent { color: var(--red); }
.page-hero-sub { font-size: 1rem; color: rgba(255,255,255,0.65); max-width: 560px; line-height: 1.7; }

/* ── SECTION SHARED ──────────────────────────────────────────── */
section { padding: 100px 48px; }
.section-inner { max-width: 1200px; margin: 0 auto; }
.section-badge { display: inline-flex; align-items: center; gap: 8px; background: rgba(11,60,145,0.07); border: 1px solid rgba(11,60,145,0.15); color: var(--navy); font-size: 0.73rem; font-weight: 700; letter-spacing: 0.13em; text-transform: uppercase; padding: 7px 16px; border-radius: 99px; margin-bottom: 16px; }
.section-title { font-family: var(--font-head); font-size: clamp(1.7rem, 3vw, 2.6rem); font-weight: 800; color: var(--text-dark); line-height: 1.15; letter-spacing: -0.02em; margin-bottom: 16px; }
.section-title .accent { color: var(--red); }
.section-sub { font-size: 0.95rem; color: var(--text-mid); max-width: 560px; line-height: 1.7; }
.divider-line { width: 52px; height: 3px; background: linear-gradient(90deg, var(--red), var(--navy)); border-radius: 99px; margin: 20px 0 48px; }

/* ── TENTANG SECTION ─────────────────────────────────────────── */
#tentang { background: var(--white); }
.tentang-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 80px; align-items: center; }
.tentang-visual { position: relative; }
.tentang-main-card { background: linear-gradient(135deg, var(--navy-deeper), var(--navy)); border-radius: var(--radius-lg); padding: 48px 40px; color: white; position: relative; overflow: hidden; }
.tentang-main-card::before { content:''; position: absolute; top: -60px; right: -60px; width: 220px; height: 220px; background: rgba(227,30,36,0.18); border-radius: 50%; }
.tentang-card-label { font-size: 0.72rem; font-weight: 600; letter-spacing: 0.14em; text-transform: uppercase; color: rgba(255,255,255,0.5); margin-bottom: 8px; }
.tentang-card-name { font-family: var(--font-head); font-size: 1.5rem; font-weight: 800; color: white; margin-bottom: 20px; line-height: 1.2; }
.tentang-card-divider { width: 40px; height: 2px; background: var(--red); border-radius: 99px; margin-bottom: 20px; }
.tentang-card-items { display: flex; flex-direction: column; gap: 10px; }
.tentang-card-item { display: flex; align-items: center; gap: 10px; font-size: 0.85rem; color: rgba(255,255,255,0.75); }
.tentang-card-item svg { width: 16px; height: 16px; stroke: var(--red); fill: none; stroke-width: 2.5; flex-shrink: 0; }
.tentang-badge-row { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-top: 16px; }
.tentang-badge-mini { background: white; border-radius: var(--radius); padding: 16px 18px; box-shadow: var(--shadow); }
.tentang-badge-mini-num { font-family: var(--font-head); font-size: 1.4rem; font-weight: 800; color: var(--navy); line-height: 1; }
.tentang-badge-mini-num .unit { color: var(--red); font-size: 1rem; }
.tentang-badge-mini-label { font-size: 0.73rem; color: var(--text-mid); margin-top: 4px; }
.tentang-text p { color: var(--text-mid); font-size: 0.95rem; line-height: 1.8; margin-bottom: 16px; }
.tentang-text p:last-of-type { margin-bottom: 0; }

/* ── VISI MISI ───────────────────────────────────────────────── */
#visi-misi { background: var(--off-white); }
.visimisi-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 32px; margin-top: 0; }
.visimisi-card { background: white; border-radius: var(--radius-lg); padding: 44px 40px; box-shadow: var(--shadow); position: relative; overflow: hidden; transition: var(--transition); }
.visimisi-card:hover { transform: translateY(-6px); box-shadow: var(--shadow-hover); }
.visimisi-card.visi { border-top: 4px solid var(--navy); }
.visimisi-card.misi { border-top: 4px solid var(--red); }
.visimisi-icon { width: 56px; height: 56px; border-radius: 14px; display: flex; align-items: center; justify-content: center; margin-bottom: 24px; }
.visimisi-card.visi .visimisi-icon { background: rgba(11,60,145,0.08); }
.visimisi-card.misi .visimisi-icon { background: rgba(227,30,36,0.08); }
.visimisi-icon svg { width: 26px; height: 26px; fill: none; stroke-width: 1.75; }
.visimisi-card.visi .visimisi-icon svg { stroke: var(--navy); }
.visimisi-card.misi .visimisi-icon svg { stroke: var(--red); }
.visimisi-label { font-size: 0.72rem; font-weight: 700; letter-spacing: 0.14em; text-transform: uppercase; margin-bottom: 12px; }
.visimisi-card.visi .visimisi-label { color: var(--navy); }
.visimisi-card.misi .visimisi-label { color: var(--red); }
.visimisi-title { font-family: var(--font-head); font-size: 1.4rem; font-weight: 800; color: var(--text-dark); margin-bottom: 20px; }
.visimisi-text { font-size: 0.9rem; color: var(--text-mid); line-height: 1.8; }
.misi-list { list-style: none; display: flex; flex-direction: column; gap: 12px; }
.misi-list li { display: flex; align-items: flex-start; gap: 12px; font-size: 0.9rem; color: var(--text-mid); line-height: 1.6; }
.misi-bullet { width: 20px; height: 20px; background: rgba(227,30,36,0.1); border-radius: 50%; display: flex; align-items: center; justify-content: center; flex-shrink: 0; margin-top: 2px; }
.misi-bullet svg { width: 10px; height: 10px; stroke: var(--red); fill: none; stroke-width: 3; }

/* ── SEJARAH TIMELINE ────────────────────────────────────────── */
#sejarah { background: var(--white); }
.sejarah-layout { display: grid; grid-template-columns: 1fr 1fr; gap: 80px; align-items: center; }
.sejarah-text p { font-size: 0.95rem; color: var(--text-mid); line-height: 1.8; margin-bottom: 16px; }
.timeline { position: relative; padding-left: 28px; }
.timeline::before { content: ''; position: absolute; left: 0; top: 0; bottom: 0; width: 2px; background: linear-gradient(180deg, var(--navy), var(--red)); border-radius: 99px; }
.timeline-item { position: relative; padding-bottom: 32px; padding-left: 20px; }
.timeline-item:last-child { padding-bottom: 0; }
.timeline-dot { position: absolute; left: -34px; top: 4px; width: 14px; height: 14px; border-radius: 50%; background: var(--navy); border: 3px solid white; box-shadow: 0 0 0 3px rgba(11,60,145,0.2); }
.timeline-item:nth-child(even) .timeline-dot { background: var(--red); box-shadow: 0 0 0 3px rgba(227,30,36,0.2); }
.timeline-year { font-family: var(--font-head); font-size: 0.8rem; font-weight: 700; color: var(--navy); letter-spacing: 0.1em; margin-bottom: 4px; }
.timeline-item:nth-child(even) .timeline-year { color: var(--red); }
.timeline-event { font-size: 0.88rem; color: var(--text-mid); line-height: 1.6; }
.timeline-event strong { color: var(--text-dark); display: block; margin-bottom: 2px; font-weight: 600; }

/* ── STATISTIK ───────────────────────────────────────────────── */
#statistik { background: linear-gradient(135deg, var(--navy-deeper) 0%, var(--navy) 100%); position: relative; overflow: hidden; }
#statistik::before { content: ''; position: absolute; inset: 0; background-image: url('data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNjAiIGhlaWdodD0iNjAiIHZpZXdCb3g9IjAgMCA2MCA2MCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48ZyBmaWxsPSJub25lIiBmaWxsLXJ1bGU9ImV2ZW5vZGQiPjxnIGZpbGw9IiNmZmZmZmYiIGZpbGwtb3BhY2l0eT0iMC4wMyI+PHBhdGggZD0iTTM2IDM0djZoNnYtNmgtNnptMCAwdi02aC02djZoNnptNiAwaDZ2LTZoLTZ2NnptLTEyIDBoLTZ2Nmg2di02eiIvPjwvZz48L2c+PC9zdmc+'); opacity: 0.4; }
#statistik .section-badge { background: rgba(255,255,255,0.1); border-color: rgba(255,255,255,0.2); color: rgba(255,255,255,0.8); }
#statistik .section-title { color: white; }
#statistik .section-sub { color: rgba(255,255,255,0.6); }
#statistik .divider-line { background: linear-gradient(90deg, var(--red), rgba(255,255,255,0.4)); }
.stat-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 24px; }
.stat-card { background: rgba(255,255,255,0.07); backdrop-filter: blur(12px); border: 1px solid rgba(255,255,255,0.12); border-radius: var(--radius-lg); padding: 36px 28px; text-align: center; transition: var(--transition); }
.stat-card:hover { background: rgba(255,255,255,0.13); transform: translateY(-6px); }
.stat-icon { width: 52px; height: 52px; background: rgba(255,255,255,0.1); border-radius: 14px; display: flex; align-items: center; justify-content: center; margin: 0 auto 20px; }
.stat-icon svg { width: 24px; height: 24px; stroke: rgba(255,255,255,0.8); fill: none; stroke-width: 1.75; }
.stat-num { font-family: var(--font-head); font-size: 2.6rem; font-weight: 800; color: white; line-height: 1; margin-bottom: 8px; }
.stat-num .unit { color: var(--red); font-size: 1.8rem; }
.stat-label { font-size: 0.82rem; color: rgba(255,255,255,0.55); letter-spacing: 0.05em; }

/* ── TIM ─────────────────────────────────────────────────────── */
#tim { background: var(--off-white); }
.tim-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 24px; }
.tim-card { background: white; border-radius: var(--radius-lg); padding: 36px 28px; text-align: center; box-shadow: var(--shadow); transition: var(--transition); }
.tim-card:hover { transform: translateY(-6px); box-shadow: var(--shadow-hover); }
.tim-avatar { width: 72px; height: 72px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 20px; color: white; font-family: var(--font-head); font-size: 1.1rem; font-weight: 700; }
.tim-name { font-family: var(--font-head); font-size: 1rem; font-weight: 700; color: var(--text-dark); margin-bottom: 6px; }
.tim-dept { font-size: 0.82rem; color: var(--text-light); background: var(--off-white); padding: 4px 12px; border-radius: 99px; display: inline-block; }

/* ── SERTIFIKASI ─────────────────────────────────────────────── */
#sertifikasi { background: var(--white); }
.cert-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px; }
.cert-card { background: var(--off-white); border-radius: var(--radius-lg); padding: 36px 24px; text-align: center; border: 1px solid var(--light-gray); transition: var(--transition); }
.cert-card:hover { border-color: var(--navy); box-shadow: var(--shadow); transform: translateY(-4px); }
.cert-icon { width: 64px; height: 64px; background: linear-gradient(135deg, var(--navy), #1a5ed6); border-radius: 16px; display: flex; align-items: center; justify-content: center; margin: 0 auto 20px; }
.cert-icon svg { width: 30px; height: 30px; stroke: white; fill: none; stroke-width: 1.75; }
.cert-name { font-family: var(--font-head); font-size: 0.92rem; font-weight: 700; color: var(--text-dark); margin-bottom: 8px; line-height: 1.3; }
.cert-year { font-size: 0.8rem; color: var(--text-light); background: rgba(11,60,145,0.07); border: 1px solid rgba(11,60,145,0.12); color: var(--navy); padding: 4px 12px; border-radius: 99px; display: inline-block; font-weight: 600; }

/* ── LOKASI ──────────────────────────────────────────────────── */
#lokasi { background: var(--off-white); }
.lokasi-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 60px; align-items: start; }
.lokasi-info { display: flex; flex-direction: column; gap: 16px; }
.lokasi-item { display: flex; align-items: flex-start; gap: 16px; padding: 20px; background: white; border-radius: var(--radius); border: 1px solid rgba(11,60,145,0.07); transition: var(--transition); }
.lokasi-item:hover { transform: translateX(4px); box-shadow: var(--shadow); }
.lokasi-item-icon { width: 44px; height: 44px; background: rgba(11,60,145,0.08); border-radius: 10px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.lokasi-item-icon svg { width: 20px; height: 20px; stroke: var(--navy); fill: none; stroke-width: 1.75; }
.lokasi-item-text strong { display: block; font-size: 0.82rem; font-weight: 700; color: var(--text-dark); margin-bottom: 4px; }
.lokasi-item-text span { font-size: 0.85rem; color: var(--text-mid); line-height: 1.5; }
.lokasi-map { border-radius: var(--radius-lg); overflow: hidden; box-shadow: var(--shadow); border: 1px solid rgba(11,60,145,0.08); }
.lokasi-map iframe { display: block; width: 100%; height: 420px; border: none; }
.btn-whatsapp { display: inline-flex; align-items: center; gap: 12px; background: #25D366; color: white; font-family: var(--font-head); font-size: 0.9rem; font-weight: 700; padding: 14px 28px; border-radius: var(--radius); text-decoration: none; transition: var(--transition); margin-top: 8px; }
.btn-whatsapp:hover { background: #1ebe5c; transform: translateY(-2px); box-shadow: 0 8px 24px rgba(37,211,102,0.35); }
.btn-whatsapp svg { width: 20px; height: 20px; fill: white; }

/* ── FOOTER ──────────────────────────────────────────────────── */
footer { background: var(--navy-deeper); color: white; padding: 80px 48px 40px; }
.footer-grid { display: grid; grid-template-columns: 2fr 1fr 1fr 1.5fr; gap: 60px; margin-bottom: 60px; }
.footer-brand-name { font-family: var(--font-head); font-weight: 700; font-size: 1.05rem; color: white; line-height: 1.1; margin-bottom: 20px; }
.footer-brand-name span { display: block; font-size: 0.62rem; font-weight: 400; opacity: 0.55; letter-spacing: 0.08em; font-family: var(--font-body); }
.footer-about { font-size: 0.85rem; color: rgba(255,255,255,0.5); line-height: 1.7; margin-bottom: 28px; }
.footer-social { display: flex; gap: 12px; }
.social-btn { width: 38px; height: 38px; border-radius: 9px; background: rgba(255,255,255,0.07); border: 1px solid rgba(255,255,255,0.1); display: flex; align-items: center; justify-content: center; cursor: pointer; transition: var(--transition); text-decoration: none; }
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

/* ── RESPONSIVE ──────────────────────────────────────────────── */
@media (max-width: 1024px) {
  .tentang-grid, .sejarah-layout, .lokasi-grid { grid-template-columns: 1fr; gap: 48px; }
  .visimisi-grid { grid-template-columns: 1fr; }
  .cert-grid, .tim-grid { grid-template-columns: repeat(2, 1fr); }
  .stat-grid { grid-template-columns: repeat(2, 1fr); }
  .footer-grid { grid-template-columns: 1fr 1fr; gap: 40px; }
}
@media (max-width: 768px) {
  #navbar { padding: 0 24px; }
  .nav-menu, .nav-actions { display: none; }
  .hamburger { display: flex; }
  section { padding: 72px 24px; }
  .page-hero-content { padding: 100px 24px 64px; }
  .stat-grid { grid-template-columns: 1fr 1fr; }
  .cert-grid, .tim-grid { grid-template-columns: 1fr 1fr; }
  .footer-grid { grid-template-columns: 1fr; gap: 32px; }
  footer { padding: 60px 24px 32px; }
}
@media (max-width: 480px) {
  .cert-grid, .tim-grid, .stat-grid { grid-template-columns: 1fr; }
  .tentang-badge-row { grid-template-columns: 1fr 1fr; }
}
</style>
</head>
<body>

<!-- LOADER -->
<div id="loader">
  <div class="loader-logo-text">THREADB2B</div>
  <div class="loader-bar"><div class="loader-bar-fill"></div></div>
  <div class="loader-text">Memuat Profil Perusahaan…</div>
</div>

<!-- MOBILE OVERLAY -->
<div class="mobile-overlay" id="mobileOverlay" onclick="closeMobileMenu()"></div>

<!-- MOBILE MENU -->
<div class="mobile-menu" id="mobileMenu">
  <a href="index.php"       class="mobile-nav-link">Beranda</a>
  <a href="about.php"       class="mobile-nav-link" style="color:var(--navy)">Profil Perusahaan</a>
  <a href="index.php#produk" class="mobile-nav-link">Produk</a>
  <a href="index.php#kontak" class="mobile-nav-link">Kontak</a>
  <div class="mobile-actions">
    <a href="login.php"    class="btn-nav-login"    style="text-align:center">Masuk</a>
    <a href="register.php" class="btn-nav-register" style="text-align:center">Daftar</a>
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
    <li><a href="index.php"        class="nav-link">Beranda</a></li>
    <li><a href="about.php"        class="nav-link active">Profil Perusahaan</a></li>
    <li><a href="index.php#produk" class="nav-link">Produk</a></li>
    <li><a href="index.php#kontak" class="nav-link">Kontak</a></li>
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
  <div class="page-hero-content">
    <div class="page-hero-breadcrumb">
      <a href="index.php" class="breadcrumb-item">Beranda</a>
      <span class="breadcrumb-sep">/</span>
      <span class="breadcrumb-current">Profil Perusahaan</span>
    </div>
    <div class="page-hero-badge">
      <span class="page-hero-badge-dot"></span>
      Sewing Thread &amp; Yarn Dyeing
    </div>
    <h1 class="page-hero-title">
      PT <span class="accent">Sing Long Brothers</span><br>Industrial
    </h1>
    <p class="page-hero-sub">
      Produsen &amp; distributor benang jahit dan benang pewarna berkualitas premium,
      berlokasi di Cikande, Serang, Banten — melayani industri tekstil sejak lebih dari 3 dekade.
    </p>
  </div>
</div>

<!-- ── TENTANG ─────────────────────────────────────────────── -->
<section id="tentang">
  <div class="section-inner">
    <div class="tentang-grid">
      <div class="tentang-visual reveal">
        <div class="tentang-main-card">
          <div class="tentang-card-label">Tentang Kami</div>
          <div class="tentang-card-name">PT Sing Long Brothers Industrial</div>
          <div class="tentang-card-divider"></div>
          <div class="tentang-card-items">
            <div class="tentang-card-item">
              <svg viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
              Sewing Thread &amp; Yarn Dyeing
            </div>
            <div class="tentang-card-item">
              <svg viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
              Cikande, Serang, Banten, Indonesia
            </div>
            <div class="tentang-card-item">
              <svg viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
              Ekspor ke 15+ Negara
            </div>
            <div class="tentang-card-item">
              <svg viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
              ISO 9001 &amp; OEKO-TEX Bersertifikat
            </div>
          </div>
        </div>
        <div class="tentang-badge-row">
          <div class="tentang-badge-mini">
            <div class="tentang-badge-mini-num">30<span class="unit">+</span></div>
            <div class="tentang-badge-mini-label">Tahun Pengalaman</div>
          </div>
          <div class="tentang-badge-mini">
            <div class="tentang-badge-mini-num">300<span class="unit">+</span></div>
            <div class="tentang-badge-mini-label">Klien Aktif</div>
          </div>
          <div class="tentang-badge-mini">
            <div class="tentang-badge-mini-num">200<span class="unit">+</span></div>
            <div class="tentang-badge-mini-label">Jenis Produk</div>
          </div>
          <div class="tentang-badge-mini">
            <div class="tentang-badge-mini-num">15<span class="unit">+</span></div>
            <div class="tentang-badge-mini-label">Negara Ekspor</div>
          </div>
        </div>
      </div>
      <div class="tentang-text reveal reveal-delay-2">
        <div class="section-badge">Siapa Kami</div>
        <h2 class="section-title">Keunggulan Benang <span class="accent">Terpercaya</span></h2>
        <div class="divider-line"></div>
        <?php
          $paragraphs = array_filter(array_map('trim', explode("\n", $company['tentang_company'])));
          foreach ($paragraphs as $p):
        ?>
          <p><?= htmlspecialchars($p) ?></p>
        <?php endforeach; ?>
        <?php if (count($paragraphs) < 2): ?>
        <p>Sebagai bagian dari ekosistem industri tekstil Indonesia, PT Sing Long Brothers Industrial
           terus berinovasi dalam teknologi produksi dan pewarnaan benang untuk memenuhi tuntutan
           pasar domestik dan internasional yang terus berkembang.</p>
        <?php endif; ?>
      </div>
    </div>
  </div>
</section>

<!-- ── VISI MISI ────────────────────────────────────────────── -->
<section id="visi-misi">
  <div class="section-inner">
    <div class="reveal" style="text-align:center; max-width: 600px; margin: 0 auto 52px;">
      <div class="section-badge">Arah &amp; Tujuan</div>
      <h2 class="section-title">Visi &amp; <span class="accent">Misi</span></h2>
      <div class="divider-line" style="margin: 16px auto 0;"></div>
    </div>
    <div class="visimisi-grid">
      <!-- VISI -->
      <div class="visimisi-card visi reveal reveal-delay-1">
        <div class="visimisi-icon">
          <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><circle cx="12" cy="12" r="3"/><line x1="12" y1="2" x2="12" y2="5"/><line x1="12" y1="19" x2="12" y2="22"/><line x1="2" y1="12" x2="5" y2="12"/><line x1="19" y1="12" x2="22" y2="12"/></svg>
        </div>
        <div class="visimisi-label">Visi Kami</div>
        <div class="visimisi-title">Menjadi Produsen Benang Terdepan di Asia Tenggara</div>
        <div class="visimisi-text"><?= htmlspecialchars($company['visi']) ?></div>
      </div>
      <!-- MISI -->
      <div class="visimisi-card misi reveal reveal-delay-2">
        <div class="visimisi-icon">
          <svg viewBox="0 0 24 24"><polyline points="9 11 12 14 22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg>
        </div>
        <div class="visimisi-label">Misi Kami</div>
        <div class="visimisi-title">Komitmen Kami untuk Industri Tekstil</div>
        <?php
          $misi_items = array_filter(array_map('trim', explode("\n", $company['misi'])));
        ?>
        <?php if (count($misi_items) > 1): ?>
        <ul class="misi-list">
          <?php foreach ($misi_items as $m): ?>
          <li>
            <span class="misi-bullet">
              <svg viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
            </span>
            <?= htmlspecialchars($m) ?>
          </li>
          <?php endforeach; ?>
        </ul>
        <?php else: ?>
        <div class="visimisi-text"><?= htmlspecialchars($company['misi']) ?></div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</section>

<!-- ── SEJARAH ──────────────────────────────────────────────── -->
<section id="sejarah">
  <div class="section-inner">
    <div class="sejarah-layout">
      <div class="reveal">
        <div class="section-badge">Jejak Perjalanan</div>
        <h2 class="section-title">Sejarah <span class="accent">Perusahaan</span></h2>
        <div class="divider-line"></div>
        <div class="sejarah-text">
          <?php
            $sejarah_paras = array_filter(array_map('trim', explode("\n", $company['sejarah'])));
            foreach ($sejarah_paras as $sp):
          ?>
            <p><?= htmlspecialchars($sp) ?></p>
          <?php endforeach; ?>
          <?php if (empty($sejarah_paras)): ?>
          <p>PT Sing Long Brothers Industrial telah berdiri dan berkembang melayani industri tekstil Indonesia selama lebih dari 3 dekade, menjadi mitra terpercaya ratusan pabrik garmen di seluruh nusantara.</p>
          <?php endif; ?>
        </div>
      </div>
      <div class="reveal reveal-delay-2">
        <div class="timeline">
          <div class="timeline-item">
            <div class="timeline-dot"></div>
            <div class="timeline-year">1992</div>
            <div class="timeline-event"><strong>Pendirian Perusahaan</strong>Berdiri sebagai usaha produksi benang jahit skala menengah di Cikande, Serang, Banten.</div>
          </div>
          <div class="timeline-item">
            <div class="timeline-dot"></div>
            <div class="timeline-year">1998</div>
            <div class="timeline-event"><strong>Ekspansi Produksi</strong>Penambahan lini mesin twisting dan winding untuk meningkatkan kapasitas produksi nasional.</div>
          </div>
          <div class="timeline-item">
            <div class="timeline-dot"></div>
            <div class="timeline-year">2005</div>
            <div class="timeline-event"><strong>Divisi Yarn Dyeing</strong>Pembukaan unit pewarnaan benang (yarn dyeing) dengan teknologi presisi tinggi.</div>
          </div>
          <div class="timeline-item">
            <div class="timeline-dot"></div>
            <div class="timeline-year">2010</div>
            <div class="timeline-event"><strong>Sertifikasi ISO 9001</strong>Mendapatkan sertifikasi manajemen mutu internasional dan mulai ekspansi ke pasar ekspor.</div>
          </div>
          <div class="timeline-item">
            <div class="timeline-dot"></div>
            <div class="timeline-year">2018</div>
            <div class="timeline-event"><strong>Sertifikasi OEKO-TEX</strong>Produk benang mendapatkan sertifikat ramah lingkungan OEKO-TEX Standard 100.</div>
          </div>
          <div class="timeline-item">
            <div class="timeline-dot"></div>
            <div class="timeline-year">2024</div>
            <div class="timeline-event"><strong>Platform Digital B2B</strong>Peluncuran THREADB2B untuk mendukung pemesanan dan layanan pelanggan secara digital.</div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- ── STATISTIK ─────────────────────────────────────────────── -->
<section id="statistik">
  <div class="section-inner">
    <div class="reveal" style="text-align:center; max-width: 560px; margin: 0 auto 52px;">
      <div class="section-badge">Angka Bicara</div>
      <h2 class="section-title">Pencapaian Kami</h2>
      <div class="divider-line" style="margin: 16px auto 0;"></div>
    </div>
    <div class="stat-grid">
      <?php foreach ($stat_rows as $i => $s): ?>
      <div class="stat-card reveal reveal-delay-<?= $i+1 ?>">
        <div class="stat-icon">
          <svg viewBox="0 0 24 24"><?= $s['icon'] ?></svg>
        </div>
        <div class="stat-num">
          <span class="counter" data-target="<?= $s['nilai'] ?>"><?= $s['nilai'] ?></span><span class="unit"><?= $s['satuan'] ?></span>
        </div>
        <div class="stat-label"><?= htmlspecialchars($s['label']) ?></div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- ── TIM MANAJEMEN ─────────────────────────────────────────── -->
<section id="tim">
  <div class="section-inner">
    <div class="reveal" style="text-align:center; max-width: 560px; margin: 0 auto 52px;">
      <div class="section-badge">Orang-orang Hebat</div>
      <h2 class="section-title">Tim <span class="accent">Manajemen</span></h2>
      <div class="divider-line" style="margin: 16px auto 0;"></div>
    </div>
    <div class="tim-grid">
      <?php foreach ($tim_rows as $i => $t): ?>
      <div class="tim-card reveal reveal-delay-<?= ($i % 3) + 1 ?>">
        <div class="tim-avatar" style="background: <?= $t['grad'] ?>"><?= $t['inisial'] ?></div>
        <div class="tim-name"><?= htmlspecialchars($t['nama']) ?></div>
        <div class="tim-dept"><?= htmlspecialchars($t['dept']) ?></div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- ── SERTIFIKASI ───────────────────────────────────────────── -->
<section id="sertifikasi">
  <div class="section-inner">
    <div class="reveal" style="text-align:center; max-width: 560px; margin: 0 auto 52px;">
      <div class="section-badge">Standar Internasional</div>
      <h2 class="section-title">Sertifikasi &amp; <span class="accent">Penghargaan</span></h2>
      <div class="divider-line" style="margin: 16px auto 0;"></div>
    </div>
    <div class="cert-grid">
      <?php foreach ($cert_rows as $i => $c): ?>
      <div class="cert-card reveal reveal-delay-<?= ($i % 4) + 1 ?>">
        <div class="cert-icon">
          <svg viewBox="0 0 24 24"><circle cx="12" cy="8" r="7"/><polyline points="8.21 13.89 7 23 12 20 17 23 15.79 13.88"/></svg>
        </div>
        <div class="cert-name"><?= htmlspecialchars($c['nama_sertifikat']) ?></div>
        <?php if ($c['tahun']): ?>
        <div class="cert-year"><?= htmlspecialchars($c['tahun']) ?></div>
        <?php endif; ?>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- ── LOKASI ────────────────────────────────────────────────── -->
<section id="lokasi">
  <div class="section-inner">
    <div class="reveal" style="max-width: 560px; margin-bottom: 52px;">
      <div class="section-badge">Temukan Kami</div>
      <h2 class="section-title">Lokasi &amp; <span class="accent">Kontak</span></h2>
      <div class="divider-line"></div>
    </div>
    <div class="lokasi-grid">
      <div class="lokasi-info reveal">
        <div class="lokasi-item">
          <div class="lokasi-item-icon">
            <svg viewBox="0 0 24 24"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
          </div>
          <div class="lokasi-item-text">
            <strong>Alamat Pabrik</strong>
            <span><?= htmlspecialchars($company['alamat']) ?></span>
          </div>
        </div>
        <div class="lokasi-item">
          <div class="lokasi-item-icon">
            <svg viewBox="0 0 24 24"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07A19.5 19.5 0 0 1 4.69 12 19.79 19.79 0 0 1 1.61 3.18 2 2 0 0 1 3.6 1h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 8.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
          </div>
          <div class="lokasi-item-text">
            <strong>Telepon</strong>
            <span><?= htmlspecialchars($company['phone']) ?></span>
          </div>
        </div>
        <div class="lokasi-item">
          <div class="lokasi-item-icon">
            <svg viewBox="0 0 24 24"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
          </div>
          <div class="lokasi-item-text">
            <strong>Email</strong>
            <span><?= htmlspecialchars($company['email']) ?></span>
          </div>
        </div>
        <div class="lokasi-item">
          <div class="lokasi-item-icon">
            <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
          </div>
          <div class="lokasi-item-text">
            <strong>Jam Operasional</strong>
            <span>Senin – Jumat: 08.00 – 17.00 WIB<br>Sabtu: 08.00 – 13.00 WIB</span>
          </div>
        </div>
        <a href="https://wa.me/6225440112" target="_blank" class="btn-whatsapp">
          <svg viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 0 1-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 0 1-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 0 1 2.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0 0 12.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 0 0 5.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 0 0-3.48-8.413z"/></svg>
          Hubungi via WhatsApp
        </a>
      </div>
      <div class="lokasi-map reveal reveal-delay-2">
        <?php if (!empty($company['maps'])): ?>
          <?php if (strpos($company['maps'], 'embed') !== false): ?>
            <iframe src="<?= htmlspecialchars($company['maps']) ?>" allowfullscreen loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
          <?php else: ?>
            <!-- fallback: Google Maps embed berdasarkan kata kunci alamat -->
            <iframe src="https://www.google.com/maps/embed/v1/place?key=&q=Cikande+Serang+Banten+Indonesia" allowfullscreen loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
          <?php endif; ?>
        <?php else: ?>
          <iframe src="https://www.google.com/maps?q=Cikande+Serang+Banten+Indonesia&output=embed" allowfullscreen loading="lazy"></iframe>
        <?php endif; ?>
      </div>
    </div>
  </div>
</section>

<!-- ── FOOTER ─────────────────────────────────────────────────── -->
<footer>
  <div class="footer-grid">
    <div>
      <div class="footer-brand-name">
        THREADB2B
        <span>PT Sing Long Brothers Industrial</span>
      </div>
      <div class="footer-about">
        Produsen dan distributor benang jahit &amp; yarn dyeing premium di Cikande, Serang, Banten.
        Melayani industri tekstil domestik &amp; ekspor selama lebih dari 30 tahun.
      </div>
      <div class="footer-social">
        <a href="#" class="social-btn" title="LinkedIn">
          <svg viewBox="0 0 24 24"><path d="M16 8a6 6 0 0 1 6 6v7h-4v-7a2 2 0 0 0-2-2 2 2 0 0 0-2 2v7h-4v-7a6 6 0 0 1 6-6z"/><rect x="2" y="9" width="4" height="12"/><circle cx="4" cy="4" r="2"/></svg>
        </a>
        <a href="#" class="social-btn" title="Instagram">
          <svg viewBox="0 0 24 24"><rect x="2" y="2" width="20" height="20" rx="5" ry="5"/><path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z"/><line x1="17.5" y1="6.5" x2="17.51" y2="6.5"/></svg>
        </a>
        <a href="#" class="social-btn" title="WhatsApp">
          <svg viewBox="0 0 24 24"><path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"/></svg>
        </a>
      </div>
    </div>
    <div>
      <div class="footer-col-title">Navigasi</div>
      <ul class="footer-links">
        <li><a href="index.php">Beranda</a></li>
        <li><a href="about.php">Profil Perusahaan</a></li>
        <li><a href="index.php#produk">Produk</a></li>
        <li><a href="index.php#sertifikasi">Sertifikasi</a></li>
        <li><a href="index.php#kontak">Kontak</a></li>
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
  setTimeout(() => document.getElementById('loader').classList.add('hidden'), 1400);
});

// ── NAVBAR SCROLL ──────────────────────────────────────────────
const navbar = document.getElementById('navbar');
window.addEventListener('scroll', () => {
  navbar.classList.toggle('scrolled', window.scrollY > 60);
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

// ── COUNTER ANIMATION ──────────────────────────────────────────
function animateCounter(el, target, duration = 1800) {
  let start = null;
  const step = ts => {
    if (!start) start = ts;
    const p = Math.min((ts - start) / duration, 1);
    el.textContent = Math.floor((1 - Math.pow(1 - p, 3)) * target);
    if (p < 1) requestAnimationFrame(step); else el.textContent = target;
  };
  requestAnimationFrame(step);
}
new IntersectionObserver(entries => {
  if (entries[0].isIntersecting)
    document.querySelectorAll('.counter').forEach(c => animateCounter(c, parseInt(c.dataset.target)));
}, { threshold: 0.3 }).observe(document.getElementById('statistik'));
</script>
</body>
</html>
