<?php
// ============================================================
// index.php — Halaman Utama THREADB2B
// Tabel pakai: company_profile, certificates, gallery, products
// Testimoni & statistik: statis (tidak ada tabelnya di SQL)
// ============================================================

require_once __DIR__ . '/assets/config.php';

$db = getDB();

// ── company_profile (ambil baris pertama) ─────────────────────
$company = $db->query(
    "SELECT * FROM company_profile LIMIT 1"
)->fetch_assoc();

// Fallback jika tabel masih kosong
$company = $company ?: [
    'nama_pt'           => 'PT Benang Nusantara',
    'tentang_company'   => 'THREADB2B menghadirkan benang premium dengan standar internasional, teknologi modern, dan layanan profesional untuk kebutuhan industri tekstil global.',
    'visi'              => 'Menjadi produsen benang terdepan di Asia Tenggara yang diakui secara global atas kualitas, inovasi, dan keberlanjutan.',
    'misi'              => 'Menghadirkan produk benang berkualitas tinggi dengan teknologi modern, layanan prima, dan komitmen berkelanjutan kepada pelanggan.',
    'alamat'            => 'Jl. Industri Raya No. 88, Kawasan Industri Pulogadung, Jakarta Timur 13920',
    'email'             => 'info@threadb2b.co.id',
    'phone'             => '+62 21 4602 8800',
    'maps'              => 'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3966.521260322461!2d106.8450731507643!3d-6.2088397953476!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x2e69f3e945e34b9d%3A0x5371bf0fdad786a2!2sKawasan+Industri+Pulogadung!5e0!3m2!1sid!2sid!4v1625000000000!5m2!1sid!2sid',
];

// ── products (8 produk untuk preview di home) ─────────────────
$produk_rows = $db->query(
    "SELECT item_no, item_name, denier, colour_name, material_type, recycled, unit, price_idr
     FROM products
     ORDER BY item_no ASC
     LIMIT 8"
)->fetch_all(MYSQLI_ASSOC);

// ── certificates ──────────────────────────────────────────────
$cert_rows = $db->query(
    "SELECT * FROM certificates ORDER BY tahun DESC"
)->fetch_all(MYSQLI_ASSOC);

// Fallback statis jika tabel certificates kosong
if (empty($cert_rows)) {
    $cert_rows = [
        ['id_certificate' => 1, 'nama_sertifikat' => 'ISO 9001:2015', 'tahun' => 2022, 'gambar' => null],
        ['id_certificate' => 2, 'nama_sertifikat' => 'OEKO-TEX Standard', 'tahun' => 2023, 'gambar' => null],
        ['id_certificate' => 3, 'nama_sertifikat' => 'SNI', 'tahun' => 2021, 'gambar' => null],
        ['id_certificate' => 4, 'nama_sertifikat' => 'Sertifikasi Ekspor', 'tahun' => 2020, 'gambar' => null],
    ];
}

// ── gallery ───────────────────────────────────────────────────
$gallery_rows = $db->query(
    "SELECT * FROM gallery ORDER BY id_gallery ASC LIMIT 5"
)->fetch_all(MYSQLI_ASSOC);

// ── Statistik statis (tidak ada tabel di threadb2b.sql) ───────
$stat_rows = [
    ['label' => 'Tahun Pengalaman', 'nilai' => 20,  'satuan' => '+', 'icon' => '<circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/>'],
    ['label' => 'Klien Aktif',      'nilai' => 500, 'satuan' => '+', 'icon' => '<path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/>'],
    ['label' => 'Jenis Produk',     'nilai' => 50,  'satuan' => '+', 'icon' => '<path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/>'],
    ['label' => 'Kepuasan Klien',   'nilai' => 98,  'satuan' => '%', 'icon' => '<polyline points="20 6 9 17 4 12"/>'],
];

// ── Testimoni statis (tidak ada tabel di threadb2b.sql) ───────
$testimoni_rows = [
    ['nama' => 'Budi Santoso',   'perusahaan' => 'PT Garmen Makmur',  'kota' => 'Bandung',  'bintang' => 5, 'inisial' => 'BS', 'warna_avatar' => 'linear-gradient(135deg,#0b3c91,#1a5ed6)', 'teks' => 'Kualitas benang THREADB2B sangat konsisten dan sesuai spesifikasi. Pengiriman selalu tepat waktu, sangat mendukung proses produksi kami.'],
    ['nama' => 'Siti Rahma',     'perusahaan' => 'CV Tekstil Jaya',   'kota' => 'Surabaya', 'bintang' => 5, 'inisial' => 'SR', 'warna_avatar' => 'linear-gradient(135deg,#c0392b,#e74c3c)', 'teks' => 'Layanan responsif dan produk sangat berkualitas. Tim teknis siap membantu kami memilih jenis benang yang tepat untuk kebutuhan produksi.'],
    ['nama' => 'Ahmad Fauzi',    'perusahaan' => 'PT Nusantara Textile','kota' => 'Jakarta', 'bintang' => 5, 'inisial' => 'AF', 'warna_avatar' => 'linear-gradient(135deg,#16a085,#1abc9c)', 'teks' => 'Sudah 5 tahun bermitra dengan THREADB2B dan tidak ada keluhan. Konsistensi kualitas dan harga yang kompetitif menjadi nilai plus utama.'],
    ['nama' => 'Rina Dewi',      'perusahaan' => 'CV Sentosa Apparel', 'kota' => 'Semarang', 'bintang' => 4, 'inisial' => 'RD', 'warna_avatar' => 'linear-gradient(135deg,#6a3093,#a044ff)', 'teks' => 'Produk benang recycle dari THREADB2B sangat membantu kami dalam memenuhi standar sustainability klien internasional kami.'],
];

// ── Helper: warna gradient per material_type ──────────────────
function produkGradient(string $mat): string {
    $mat = strtolower($mat);
    if (str_contains($mat, 'nylon') || str_contains($mat, 'polyamide'))
        return 'linear-gradient(135deg,#0b3c91 0%,#1952c0 100%)';
    if (str_contains($mat, 'polyester'))
        return 'linear-gradient(135deg,#c0392b 0%,#e74c3c 100%)';
    if (str_contains($mat, 'cotton') || str_contains($mat, 'kapas'))
        return 'linear-gradient(135deg,#16a085 0%,#1abc9c 100%)';
    return 'linear-gradient(135deg,#2c3e50 0%,#3d5a74 100%)';
}

// ── Helper: slugify untuk URL produk ─────────────────────────
function slugify(string $s): string {
    return preg_replace('/[^a-z0-9]+/', '-', strtolower(trim($s)));
}

// ── Hitung total produk dari DB ───────────────────────────────
$total_produk_res = $db->query("SELECT COUNT(*) AS total FROM products")->fetch_assoc();
$total_produk = (int)($total_produk_res['total'] ?? 0);
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>THREADB2B – Premium Yarn Solutions | <?= htmlspecialchars($company['nama_pt']) ?></title>
<meta name="description" content="<?= htmlspecialchars(mb_substr($company['tentang_company'] ?? '', 0, 160)) ?>">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;500;600;700;800&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
<style>
  :root {
    --navy: #0B3C91; --navy-dark: #072d6e; --navy-deeper: #041e4f;
    --red: #E31E24; --red-dark: #b8181d;
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

  /* LOADER */
  #loader { position: fixed; inset: 0; background: var(--navy-deeper); z-index: 9999; display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 24px; transition: opacity 0.5s ease, visibility 0.5s ease; }
  #loader.hidden { opacity: 0; visibility: hidden; }
  .loader-bar { width: 200px; height: 3px; background: rgba(255,255,255,0.15); border-radius: 99px; overflow: hidden; }
  .loader-bar-fill { height: 100%; width: 0%; background: linear-gradient(90deg, var(--red), var(--navy)); border-radius: 99px; animation: loadFill 1.6s ease forwards; }
  @keyframes loadFill { to { width: 100%; } }
  .loader-text { color: rgba(255,255,255,0.5); font-family: var(--font-body); font-size: 0.8rem; letter-spacing: 0.15em; text-transform: uppercase; }
  .loader-logo-text { font-family: var(--font-head); font-size: 1.8rem; font-weight: 800; color: white; }

  /* SCROLL REVEAL */
  .reveal { opacity: 0; transform: translateY(32px); transition: opacity 0.7s ease, transform 0.7s ease; }
  .reveal.visible { opacity: 1; transform: translateY(0); }
  .reveal-delay-1 { transition-delay: 0.1s; }
  .reveal-delay-2 { transition-delay: 0.2s; }
  .reveal-delay-3 { transition-delay: 0.3s; }
  .reveal-delay-4 { transition-delay: 0.4s; }

  /* NAVBAR */
  #navbar { position: fixed; top: 0; left: 0; right: 0; z-index: 1000; padding: 0 48px; height: 72px; display: flex; align-items: center; justify-content: space-between; transition: var(--transition); background: transparent; }
  #navbar.scrolled { background: rgba(255,255,255,0.97); backdrop-filter: blur(16px); box-shadow: 0 2px 20px rgba(11,60,145,0.10); height: 64px; }
  #navbar.scrolled .nav-logo-text { color: var(--navy); }
  #navbar.scrolled .nav-link { color: var(--text-dark); }
  #navbar.scrolled .nav-link:hover { color: var(--navy); }
  #navbar.scrolled .lang-btn { color: var(--text-mid); border-color: var(--mid-gray); }
  #navbar.scrolled .hamburger span { background: var(--text-dark); }
  .nav-logo { display: flex; align-items: center; gap: 12px; text-decoration: none; flex-shrink: 0; }
  .nav-logo-text { font-family: var(--font-head); font-weight: 700; font-size: 1.1rem; color: var(--white); line-height: 1.1; transition: var(--transition); }
  .nav-logo-text span { display: block; font-size: 0.65rem; font-weight: 400; letter-spacing: 0.1em; opacity: 0.75; font-family: var(--font-body); }
  .nav-menu { display: flex; align-items: center; gap: 4px; list-style: none; }
  .nav-link { color: rgba(255,255,255,0.9); text-decoration: none; font-size: 0.875rem; font-weight: 500; padding: 8px 14px; border-radius: 8px; transition: var(--transition); position: relative; }
  .nav-link:hover { color: var(--white); background: rgba(255,255,255,0.12); }
  .nav-link.active { color: var(--white); }
  .nav-link.active::after { content: ''; position: absolute; bottom: 4px; left: 50%; transform: translateX(-50%); width: 20px; height: 2px; background: var(--red); border-radius: 99px; }
  #navbar.scrolled .nav-link.active::after { background: var(--red); }
  .nav-actions { display: flex; align-items: center; gap: 10px; }
  .lang-btn { background: none; border: 1px solid rgba(255,255,255,0.35); color: rgba(255,255,255,0.85); font-family: var(--font-body); font-size: 0.8rem; font-weight: 500; padding: 6px 12px; border-radius: 8px; cursor: pointer; transition: var(--transition); letter-spacing: 0.05em; display: flex; align-items: center; gap: 6px; }
  .lang-btn:hover { background: rgba(255,255,255,0.15); }
  .btn-nav-login { background: transparent; border: 1.5px solid rgba(255,255,255,0.6); color: var(--white); font-family: var(--font-body); font-size: 0.85rem; font-weight: 500; padding: 8px 18px; border-radius: 8px; cursor: pointer; transition: var(--transition); text-decoration: none; }
  .btn-nav-login:hover { background: rgba(255,255,255,0.15); border-color: white; }
  #navbar.scrolled .btn-nav-login { border-color: var(--navy); color: var(--navy); }
  #navbar.scrolled .btn-nav-login:hover { background: var(--navy); color: white; }
  .btn-nav-register { background: var(--red); border: none; color: var(--white); font-family: var(--font-body); font-size: 0.85rem; font-weight: 600; padding: 8px 20px; border-radius: 8px; cursor: pointer; transition: var(--transition); text-decoration: none; }
  .btn-nav-register:hover { background: var(--red-dark); transform: translateY(-1px); box-shadow: 0 4px 16px rgba(227,30,36,0.35); }
  .hamburger { display: none; flex-direction: column; gap: 5px; cursor: pointer; padding: 4px; background: none; border: none; }
  .hamburger span { display: block; width: 24px; height: 2px; background: white; border-radius: 99px; transition: var(--transition); }
  .hamburger.open span:nth-child(1) { transform: rotate(45deg) translate(5px, 5px); }
  .hamburger.open span:nth-child(2) { opacity: 0; }
  .hamburger.open span:nth-child(3) { transform: rotate(-45deg) translate(5px, -5px); }
  .mobile-menu { position: fixed; top: 0; right: 0; width: 300px; height: 100vh; background: var(--white); box-shadow: -8px 0 40px rgba(0,0,0,0.15); z-index: 1001; transform: translateX(100%); transition: transform 0.35s cubic-bezier(0.4,0,0.2,1); padding: 80px 32px 32px; display: flex; flex-direction: column; gap: 8px; }
  .mobile-menu.open { transform: translateX(0); }
  .mobile-overlay { display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.4); z-index: 1000; opacity: 0; transition: opacity 0.3s; }
  .mobile-overlay.active { display: block; opacity: 1; }
  .mobile-nav-link { display: block; color: var(--text-dark); text-decoration: none; font-size: 1rem; font-weight: 500; padding: 12px 0; border-bottom: 1px solid var(--light-gray); transition: color 0.2s; }
  .mobile-nav-link:hover { color: var(--navy); }
  .mobile-menu .mobile-actions { margin-top: 24px; display: flex; flex-direction: column; gap: 10px; }

  /* HERO */
  #hero { min-height: 100vh; position: relative; overflow: hidden; display: flex; align-items: center; justify-content: center; }
  .hero-bg { position: absolute; inset: 0; background: linear-gradient(135deg, rgba(4,30,79,0.92) 0%, rgba(11,60,145,0.78) 50%, rgba(20,20,20,0.85) 100%); }
  .hero-pattern { position: absolute; inset: 0; background-image: url('data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNjAiIGhlaWdodD0iNjAiIHZpZXdCb3g9IjAgMCA2MCA2MCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48ZyBmaWxsPSJub25lIiBmaWxsLXJ1bGU9ImV2ZW5vZGQiPjxnIGZpbGw9IiNmZmZmZmYiIGZpbGwtb3BhY2l0eT0iMC4wMyI+PHBhdGggZD0iTTM2IDM0djZoNnYtNmgtNnptMCAwdi02aC02djZoNnptNiAwaDZ2LTZoLTZ2NnptLTEyIDBoLTZ2Nmg2di02eiIvPjwvZz48L2c+PC9zdmc+'); opacity: 0.4; }
  .hero-accent { position: absolute; top: -100px; right: -100px; width: 600px; height: 600px; background: radial-gradient(circle, rgba(227,30,36,0.18) 0%, transparent 70%); pointer-events: none; }
  .hero-accent-2 { position: absolute; bottom: -150px; left: -150px; width: 500px; height: 500px; background: radial-gradient(circle, rgba(11,60,145,0.25) 0%, transparent 70%); pointer-events: none; }
  .hero-content { position: relative; z-index: 2; max-width: 1200px; width: 100%; padding: 0 48px; padding-top: 80px; display: grid; grid-template-columns: 1fr 1fr; gap: 80px; align-items: center; }
  .hero-badge { display: inline-flex; align-items: center; gap: 8px; background: rgba(227,30,36,0.18); border: 1px solid rgba(227,30,36,0.4); color: #ff6b6e; font-size: 0.75rem; font-weight: 600; letter-spacing: 0.12em; text-transform: uppercase; padding: 8px 16px; border-radius: 99px; margin-bottom: 24px; backdrop-filter: blur(8px); }
  .hero-badge-dot { width: 6px; height: 6px; background: var(--red); border-radius: 50%; animation: blink 1.4s ease-in-out infinite; }
  @keyframes blink { 0%,100%{opacity:1} 50%{opacity:0.2} }
  .hero-title { font-family: var(--font-head); font-size: clamp(2.2rem, 4vw, 3.8rem); font-weight: 800; color: var(--white); line-height: 1.1; margin-bottom: 24px; letter-spacing: -0.02em; }
  .hero-title .accent { color: var(--red); }
  .hero-subtitle { font-size: 1.05rem; color: rgba(255,255,255,0.7); line-height: 1.7; margin-bottom: 40px; max-width: 500px; }
  .hero-cta { display: flex; gap: 16px; flex-wrap: wrap; }
  .btn-primary { display: inline-flex; align-items: center; gap: 10px; background: var(--red); color: white; font-family: var(--font-body); font-size: 0.9rem; font-weight: 600; padding: 14px 28px; border-radius: var(--radius); text-decoration: none; border: none; cursor: pointer; transition: var(--transition); }
  .btn-primary:hover { background: var(--red-dark); transform: translateY(-2px); box-shadow: 0 8px 30px rgba(227,30,36,0.4); }
  .btn-secondary { display: inline-flex; align-items: center; gap: 10px; background: rgba(255,255,255,0.1); color: white; font-family: var(--font-body); font-size: 0.9rem; font-weight: 500; padding: 14px 28px; border-radius: var(--radius); text-decoration: none; border: 1.5px solid rgba(255,255,255,0.35); cursor: pointer; transition: var(--transition); backdrop-filter: blur(8px); }
  .btn-secondary:hover { background: rgba(255,255,255,0.2); border-color: white; transform: translateY(-2px); }
  .hero-visual { display: flex; flex-direction: column; gap: 16px; }
  .hero-stats-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
  .hero-stat-card { background: rgba(255,255,255,0.07); backdrop-filter: blur(16px); border: 1px solid rgba(255,255,255,0.12); border-radius: var(--radius-lg); padding: 24px; text-align: center; transition: var(--transition); }
  .hero-stat-card:hover { background: rgba(255,255,255,0.12); transform: translateY(-4px); }
  .hero-stat-num { font-family: var(--font-head); font-size: 2.4rem; font-weight: 800; color: white; line-height: 1; }
  .hero-stat-num .unit { color: var(--red); }
  .hero-stat-label { font-size: 0.8rem; color: rgba(255,255,255,0.6); margin-top: 6px; letter-spacing: 0.05em; }
  .hero-cert-badge { background: rgba(255,255,255,0.07); backdrop-filter: blur(16px); border: 1px solid rgba(255,255,255,0.12); border-radius: var(--radius-lg); padding: 20px 28px; display: flex; align-items: center; gap: 16px; }
  .hero-cert-icon { width: 44px; height: 44px; background: linear-gradient(135deg, var(--navy), #1a5ed6); border-radius: 10px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
  .hero-cert-text { color: white; }
  .hero-cert-text strong { display: block; font-family: var(--font-head); font-weight: 700; font-size: 0.95rem; }
  .hero-cert-text span { font-size: 0.78rem; color: rgba(255,255,255,0.55); }
  .hero-scroll-indicator { position: absolute; bottom: 40px; left: 50%; transform: translateX(-50%); display: flex; flex-direction: column; align-items: center; gap: 8px; color: rgba(255,255,255,0.45); font-size: 0.7rem; letter-spacing: 0.1em; text-transform: uppercase; animation: float 2.5s ease-in-out infinite; }
  @keyframes float { 0%,100%{transform:translateX(-50%) translateY(0)} 50%{transform:translateX(-50%) translateY(6px)} }
  .scroll-mouse { width: 24px; height: 38px; border: 2px solid rgba(255,255,255,0.3); border-radius: 99px; display: flex; justify-content: center; padding-top: 7px; }
  .scroll-wheel { width: 3px; height: 6px; background: rgba(255,255,255,0.4); border-radius: 99px; animation: scrollWheel 1.6s ease-in-out infinite; }
  @keyframes scrollWheel { 0%,100%{transform:translateY(0);opacity:1} 60%{transform:translateY(8px);opacity:0} }

  /* SECTIONS */
  section { padding: 100px 48px; }
  .container { max-width: 1200px; margin: 0 auto; }
  .section-eyebrow { font-size: 0.75rem; font-weight: 600; letter-spacing: 0.18em; text-transform: uppercase; color: var(--red); margin-bottom: 12px; display: flex; align-items: center; gap: 10px; }
  .section-eyebrow::before { content: ''; display: block; width: 28px; height: 2px; background: var(--red); border-radius: 99px; }
  .section-title { font-family: var(--font-head); font-size: clamp(1.8rem, 3vw, 2.8rem); font-weight: 700; color: var(--text-dark); line-height: 1.15; letter-spacing: -0.02em; margin-bottom: 16px; }
  .section-subtitle { font-size: 1rem; color: var(--text-mid); line-height: 1.7; max-width: 560px; }
  .section-header { margin-bottom: 60px; }
  .section-header.center { text-align: center; }
  .section-header.center .section-eyebrow { justify-content: center; }
  .section-header.center .section-eyebrow::before { display: none; }
  .section-header.center .section-eyebrow::after { content: ''; display: block; width: 28px; height: 2px; background: var(--red); border-radius: 99px; }
  .section-header.center .section-subtitle { margin: 0 auto; }
  .btn-outline { display: inline-flex; align-items: center; gap: 10px; background: transparent; color: var(--navy); font-family: var(--font-body); font-size: 0.9rem; font-weight: 600; padding: 13px 26px; border-radius: var(--radius); text-decoration: none; border: 2px solid var(--navy); cursor: pointer; transition: var(--transition); }
  .btn-outline:hover { background: var(--navy); color: white; transform: translateY(-2px); }
  .btn-outline svg { width: 18px; height: 18px; stroke: currentColor; fill: none; stroke-width: 2; transition: transform 0.3s ease; }
  .btn-outline:hover svg { transform: translateX(4px); }

  /* KEUNGGULAN */
  #keunggulan { background: var(--off-white); }
  .keunggulan-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 24px; }
  .keunggulan-card { background: var(--white); border-radius: var(--radius-lg); padding: 36px 28px; border: 1px solid rgba(11,60,145,0.06); transition: var(--transition); position: relative; overflow: hidden; }
  .keunggulan-card::before { content: ''; position: absolute; top: 0; left: 0; right: 0; height: 3px; background: linear-gradient(90deg, var(--navy), var(--red)); transform: scaleX(0); transform-origin: left; transition: transform 0.4s ease; }
  .keunggulan-card:hover { transform: translateY(-8px); box-shadow: var(--shadow-hover); }
  .keunggulan-card:hover::before { transform: scaleX(1); }
  .keunggulan-icon-wrap { width: 60px; height: 60px; background: linear-gradient(135deg, rgba(11,60,145,0.08), rgba(11,60,145,0.04)); border-radius: 16px; display: flex; align-items: center; justify-content: center; margin-bottom: 24px; transition: var(--transition); }
  .keunggulan-card:hover .keunggulan-icon-wrap { background: var(--navy); }
  .keunggulan-card:hover .keunggulan-icon-wrap svg { stroke: white; }
  .keunggulan-icon-wrap svg { width: 28px; height: 28px; stroke: var(--navy); stroke-width: 1.5; fill: none; transition: var(--transition); }
  .keunggulan-card h3 { font-family: var(--font-head); font-size: 1.05rem; font-weight: 700; color: var(--text-dark); margin-bottom: 10px; }
  .keunggulan-card p { font-size: 0.875rem; color: var(--text-mid); line-height: 1.6; }

  /* TENTANG */
  #tentang { background: var(--white); }
  .tentang-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 80px; align-items: center; }
  .tentang-visi-misi { margin-top: 28px; display: flex; flex-direction: column; gap: 16px; }
  .vm-item { display: flex; gap: 16px; padding: 20px; background: var(--off-white); border-radius: var(--radius); border-left: 3px solid var(--navy); transition: var(--transition); }
  .vm-item:hover { background: rgba(11,60,145,0.04); transform: translateX(4px); }
  .vm-item-icon { width: 36px; height: 36px; background: var(--navy); border-radius: 8px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
  .vm-item-icon svg { width: 18px; height: 18px; stroke: white; fill: none; stroke-width: 2; }
  .vm-item-content strong { display: block; font-family: var(--font-head); font-size: 0.85rem; font-weight: 700; color: var(--text-dark); margin-bottom: 4px; }
  .vm-item-content p { font-size: 0.82rem; color: var(--text-mid); line-height: 1.5; }
  .tentang-image-wrap { position: relative; }
  .tentang-img-placeholder { width: 100%; height: 480px; border-radius: var(--radius-lg); box-shadow: 0 20px 60px rgba(11,60,145,0.15); background: linear-gradient(135deg, #0d2a5e 0%, #1a4aa8 50%, #0b3c91 100%); display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 16px; color: white; position: relative; overflow: hidden; }
  .tentang-float-badge { position: absolute; bottom: 28px; left: -24px; background: white; border-radius: var(--radius); padding: 16px 20px; box-shadow: 0 8px 30px rgba(0,0,0,0.12); display: flex; align-items: center; gap: 12px; }
  .float-badge-icon { width: 40px; height: 40px; background: var(--red); border-radius: 10px; display: flex; align-items: center; justify-content: center; }
  .float-badge-icon svg { width: 20px; height: 20px; stroke: white; fill: none; stroke-width: 2; }
  .float-badge-text strong { display: block; font-family: var(--font-head); font-size: 1.1rem; font-weight: 800; color: var(--text-dark); }
  .float-badge-text span { font-size: 0.75rem; color: var(--text-mid); }
  .tentang-float-badge-2 { position: absolute; top: 28px; right: -24px; background: var(--navy); border-radius: var(--radius); padding: 14px 18px; box-shadow: 0 8px 30px rgba(11,60,145,0.25); color: white; text-align: center; }
  .float-badge-2-num { font-family: var(--font-head); font-size: 1.8rem; font-weight: 800; color: white; line-height: 1; }
  .float-badge-2-num span { color: var(--red); }
  .float-badge-2-text { font-size: 0.7rem; color: rgba(255,255,255,0.65); margin-top: 4px; }

  /* PRODUK */
  #produk { background: var(--off-white); }
  .produk-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 24px; }
  .produk-card { background: var(--white); border-radius: var(--radius-lg); overflow: hidden; border: 1px solid rgba(11,60,145,0.06); transition: var(--transition); }
  .produk-card:hover { transform: translateY(-8px); box-shadow: var(--shadow-hover); }
  .produk-img { height: 180px; overflow: hidden; position: relative; display: flex; align-items: center; justify-content: center; }
  .produk-img-inner { width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; transition: transform 0.5s ease; }
  .produk-card:hover .produk-img-inner { transform: scale(1.06); }
  .produk-img-overlay { position: absolute; inset: 0; background: linear-gradient(0deg, rgba(4,30,79,0.5) 0%, transparent 60%); }
  .produk-tag { position: absolute; top: 14px; left: 14px; background: var(--red); color: white; font-size: 0.7rem; font-weight: 600; letter-spacing: 0.08em; padding: 4px 10px; border-radius: 99px; }
  .produk-recycled-tag { position: absolute; top: 14px; right: 14px; background: #19c97a; color: white; font-size: 0.65rem; font-weight: 700; padding: 4px 10px; border-radius: 99px; }
  .produk-body { padding: 22px; }
  .produk-body h3 { font-family: var(--font-head); font-size: 0.92rem; font-weight: 700; color: var(--text-dark); margin-bottom: 4px; }
  .produk-item-no { font-size: 0.72rem; color: var(--text-light); font-family: monospace; margin-bottom: 6px; letter-spacing: 0.04em; }
  .produk-body p { font-size: 0.8rem; color: var(--text-mid); line-height: 1.5; margin-bottom: 14px; }
  .produk-price { font-family: var(--font-head); font-size: 0.95rem; font-weight: 800; color: var(--red); display: flex; align-items: baseline; gap: 4px; margin-bottom: 14px; }
  .produk-price .unit { font-size: 0.68rem; font-weight: 400; color: var(--text-light); }
  .btn-produk-detail { display: inline-flex; align-items: center; gap: 8px; font-size: 0.83rem; font-weight: 600; color: var(--navy); text-decoration: none; transition: gap 0.3s ease; }
  .btn-produk-detail svg { width: 16px; height: 16px; stroke: var(--navy); fill: none; stroke-width: 2; }
  .btn-produk-detail:hover { gap: 12px; color: var(--red); }
  .btn-produk-detail:hover svg { stroke: var(--red); }

  /* STATISTIK */
  #statistik { background: linear-gradient(135deg, var(--navy-deeper) 0%, var(--navy) 60%, #1a4ab8 100%); position: relative; overflow: hidden; padding: 0; }
  .statistik-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 2px; }
  .stat-item { text-align: center; padding: 60px 40px; border-right: 1px solid rgba(255,255,255,0.08); transition: var(--transition); }
  .stat-item:last-child { border-right: none; }
  .stat-item:hover { background: rgba(255,255,255,0.04); }
  .stat-icon { width: 50px; height: 50px; background: rgba(255,255,255,0.08); border-radius: 14px; display: flex; align-items: center; justify-content: center; margin: 0 auto 20px; }
  .stat-icon svg { width: 24px; height: 24px; stroke: rgba(255,255,255,0.75); fill: none; stroke-width: 1.5; }
  .stat-num { font-family: var(--font-head); font-size: clamp(2.5rem, 4vw, 3.5rem); font-weight: 800; color: white; line-height: 1; margin-bottom: 8px; }
  .stat-num .plus { color: var(--red); }
  .stat-label { font-size: 0.875rem; color: rgba(255,255,255,0.55); font-weight: 400; }

  /* GALLERY */
  #galeri { background: var(--white); padding-bottom: 80px; }
  .galeri-grid { display: grid; grid-template-columns: repeat(3, 1fr); grid-template-rows: auto auto; gap: 16px; }
  .galeri-item { border-radius: var(--radius-lg); overflow: hidden; position: relative; cursor: pointer; }
  .galeri-item:nth-child(1) { grid-row: span 2; }
  .galeri-inner { width: 100%; height: 100%; min-height: 200px; transition: transform 0.5s ease; display: flex; align-items: center; justify-content: center; padding: 20px; }
  .galeri-item:hover .galeri-inner { transform: scale(1.04); }
  .galeri-overlay { position: absolute; inset: 0; background: linear-gradient(0deg, rgba(4,30,79,0.75) 0%, transparent 50%); opacity: 0; transition: opacity 0.35s ease; display: flex; align-items: flex-end; padding: 20px; }
  .galeri-item:hover .galeri-overlay { opacity: 1; }
  .galeri-overlay span { color: white; font-size: 0.85rem; font-weight: 600; font-family: var(--font-head); }
  .galeri-bg-1 { background: linear-gradient(135deg, #0b3c91 0%, #1a5ed6 100%); }
  .galeri-bg-2 { background: linear-gradient(135deg, #b21010 0%, #e31e24 100%); }
  .galeri-bg-3 { background: linear-gradient(135deg, #0d5e4a 0%, #16a085 100%); }
  .galeri-bg-4 { background: linear-gradient(135deg, #2c3e50 0%, #4a6fa5 100%); }
  .galeri-bg-5 { background: linear-gradient(135deg, #6a3093 0%, #a044ff 100%); }
  .galeri-item:nth-child(1) .galeri-inner { min-height: 432px; }
  .galeri-icon { width: 60px; height: 60px; opacity: 0.2; }

  /* SERTIFIKASI */
  #sertifikasi { background: var(--off-white); }
  .sertif-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 24px; }
  .sertif-card { background: var(--white); border-radius: var(--radius-lg); padding: 36px 28px; text-align: center; border: 1px solid rgba(11,60,145,0.06); transition: var(--transition); }
  .sertif-card:hover { transform: translateY(-6px); box-shadow: var(--shadow-hover); }
  .sertif-badge-wrap { width: 80px; height: 80px; border-radius: 50%; background: linear-gradient(135deg,var(--navy),#1a5ed6); display: flex; align-items: center; justify-content: center; margin: 0 auto 20px; box-shadow: 0 8px 24px rgba(11,60,145,0.25); transition: var(--transition); }
  .sertif-card:hover .sertif-badge-wrap { transform: rotate(-5deg) scale(1.05); }
  .sertif-badge-wrap svg { width: 36px; height: 36px; stroke: white; fill: none; stroke-width: 1.5; }
  .sertif-name { font-family: var(--font-head); font-size: 1.05rem; font-weight: 800; color: var(--navy); margin-bottom: 8px; }
  .sertif-year { display: inline-block; margin-bottom: 10px; background: rgba(11,60,145,0.08); color: var(--navy); font-size: 0.72rem; font-weight: 700; letter-spacing: 0.08em; text-transform: uppercase; padding: 4px 12px; border-radius: 99px; }

  /* TESTIMONI */
  #testimoni { background: var(--white); }
  .testimoni-slider { position: relative; overflow: hidden; }
  .testimoni-track { display: flex; gap: 24px; transition: transform 0.5s cubic-bezier(0.4,0,0.2,1); }
  .testimoni-card { flex: 0 0 calc(33.333% - 16px); background: var(--off-white); border-radius: var(--radius-lg); padding: 36px 32px; border: 1px solid rgba(11,60,145,0.06); transition: var(--transition); }
  .testimoni-card:hover { box-shadow: var(--shadow-hover); transform: translateY(-4px); }
  .testimoni-quote { width: 36px; height: 36px; background: var(--navy); border-radius: 8px; display: flex; align-items: center; justify-content: center; margin-bottom: 20px; }
  .testimoni-quote svg { width: 18px; height: 18px; fill: rgba(255,255,255,0.7); }
  .testimoni-text { font-size: 0.9rem; color: var(--text-mid); line-height: 1.75; margin-bottom: 24px; font-style: italic; }
  .testimoni-stars { display: flex; gap: 4px; margin-bottom: 20px; }
  .star { width: 16px; height: 16px; }
  .star-filled { fill: #f5a623; }
  .star-empty { fill: var(--mid-gray); }
  .testimoni-author { display: flex; align-items: center; gap: 14px; }
  .author-avatar { width: 48px; height: 48px; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-family: var(--font-head); font-size: 1rem; font-weight: 700; flex-shrink: 0; }
  .author-info strong { display: block; font-family: var(--font-head); font-size: 0.9rem; font-weight: 700; color: var(--text-dark); }
  .author-info span { font-size: 0.78rem; color: var(--text-light); }
  .slider-controls { display: flex; justify-content: center; gap: 12px; margin-top: 40px; }
  .slider-btn { width: 44px; height: 44px; border-radius: 50%; border: 2px solid var(--light-gray); background: white; cursor: pointer; display: flex; align-items: center; justify-content: center; transition: var(--transition); }
  .slider-btn:hover { border-color: var(--navy); background: var(--navy); }
  .slider-btn svg { width: 18px; height: 18px; stroke: var(--text-mid); fill: none; stroke-width: 2; transition: var(--transition); }
  .slider-btn:hover svg { stroke: white; }
  .slider-dots { display: flex; gap: 8px; align-items: center; }
  .slider-dot { width: 8px; height: 8px; border-radius: 99px; background: var(--light-gray); border: none; cursor: pointer; transition: var(--transition); }
  .slider-dot.active { width: 24px; background: var(--navy); }

  /* KONTAK */
  #kontak { background: var(--off-white); }
  .kontak-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 60px; align-items: start; }
  .kontak-form-wrap { background: white; border-radius: var(--radius-lg); padding: 48px; box-shadow: var(--shadow); }
  .form-group { margin-bottom: 20px; }
  .form-label { display: block; font-size: 0.82rem; font-weight: 600; color: var(--text-dark); margin-bottom: 8px; }
  .form-input, .form-textarea { width: 100%; padding: 13px 16px; border: 1.5px solid var(--light-gray); border-radius: var(--radius); font-family: var(--font-body); font-size: 0.9rem; color: var(--text-dark); background: var(--off-white); transition: border-color 0.25s ease, box-shadow 0.25s ease; outline: none; resize: none; }
  .form-input:focus, .form-textarea:focus { border-color: var(--navy); box-shadow: 0 0 0 3px rgba(11,60,145,0.1); background: white; }
  .form-textarea { height: 130px; }
  .btn-submit { width: 100%; background: linear-gradient(135deg, var(--navy), #1a5ed6); color: white; border: none; padding: 15px; border-radius: var(--radius); font-family: var(--font-head); font-size: 0.95rem; font-weight: 700; cursor: pointer; transition: var(--transition); display: flex; align-items: center; justify-content: center; gap: 10px; }
  .btn-submit:hover { transform: translateY(-2px); box-shadow: 0 8px 30px rgba(11,60,145,0.35); }
  .form-alert { padding: 12px 16px; border-radius: 10px; font-size: .875rem; font-weight: 500; margin-bottom: 16px; display: none; }
  .form-alert.success { background: #e8f8f1; border: 1px solid #a3e4c3; color: #0d6e42; }
  .form-alert.error { background: #fff0f0; border: 1px solid #f5c6c6; color: #c0392b; }
  .kontak-info { display: flex; flex-direction: column; gap: 20px; }
  .kontak-info-title { font-family: var(--font-head); font-size: 1.6rem; font-weight: 700; color: var(--text-dark); margin-bottom: 8px; }
  .kontak-info-sub { font-size: 0.9rem; color: var(--text-mid); line-height: 1.6; margin-bottom: 8px; }
  .kontak-item { display: flex; align-items: flex-start; gap: 16px; padding: 20px; background: white; border-radius: var(--radius); border: 1px solid rgba(11,60,145,0.06); transition: var(--transition); }
  .kontak-item:hover { transform: translateX(4px); box-shadow: var(--shadow); }
  .kontak-item-icon { width: 44px; height: 44px; background: rgba(11,60,145,0.08); border-radius: 10px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
  .kontak-item-icon svg { width: 20px; height: 20px; stroke: var(--navy); fill: none; stroke-width: 1.75; }
  .kontak-item-text strong { display: block; font-size: 0.82rem; font-weight: 700; color: var(--text-dark); margin-bottom: 3px; }
  .kontak-item-text span { font-size: 0.85rem; color: var(--text-mid); }
  .btn-whatsapp { display: inline-flex; align-items: center; gap: 12px; background: #25D366; color: white; font-family: var(--font-head); font-size: 0.9rem; font-weight: 700; padding: 14px 28px; border-radius: var(--radius); text-decoration: none; transition: var(--transition); }
  .btn-whatsapp:hover { background: #1ebe5c; transform: translateY(-2px); box-shadow: 0 8px 24px rgba(37,211,102,0.35); }
  .btn-whatsapp svg { width: 20px; height: 20px; fill: white; }
  .map-embed { width: 100%; height: 200px; border-radius: var(--radius); overflow: hidden; border: 1px solid rgba(11,60,145,0.08); }
  .map-embed iframe { width: 100%; height: 100%; border: none; }

  /* FOOTER */
  footer { background: var(--navy-deeper); color: white; padding: 80px 48px 40px; }
  .footer-grid { display: grid; grid-template-columns: 2fr 1fr 1fr 1.5fr; gap: 60px; margin-bottom: 60px; }
  .footer-brand-name { font-family: var(--font-head); font-weight: 700; font-size: 1.05rem; color: white; line-height: 1.1; margin-bottom: 20px; }
  .footer-brand-name span { display: block; font-size: 0.62rem; font-weight: 400; opacity: 0.55; letter-spacing: 0.08em; font-family: var(--font-body); }
  .footer-about { font-size: 0.85rem; color: rgba(255,255,255,0.5); line-height: 1.7; margin-bottom: 28px; }
  .footer-social { display: flex; gap: 12px; }
  .social-btn { width: 38px; height: 38px; border-radius: 9px; background: rgba(255,255,255,0.07); border: 1px solid rgba(255,255,255,0.1); display: flex; align-items: center; justify-content: center; cursor: pointer; transition: var(--transition); text-decoration: none; }
  .social-btn:hover { background: var(--red); border-color: var(--red); transform: translateY(-2px); }
  .social-btn svg { width: 16px; height: 16px; stroke: rgba(255,255,255,0.7); fill: none; stroke-width: 1.75; }
  .footer-col-title { font-family: var(--font-head); font-size: 0.85rem; font-weight: 700; letter-spacing: 0.1em; text-transform: uppercase; color: white; margin-bottom: 20px; padding-bottom: 12px; border-bottom: 1px solid rgba(255,255,255,0.07); }
  .footer-links { list-style: none; display: flex; flex-direction: column; gap: 10px; }
  .footer-links a { color: rgba(255,255,255,0.5); text-decoration: none; font-size: 0.875rem; transition: color 0.2s; display: flex; align-items: center; gap: 8px; }
  .footer-links a:hover { color: rgba(255,255,255,0.9); }
  .footer-contact-item { display: flex; align-items: flex-start; gap: 12px; margin-bottom: 14px; }
  .footer-contact-icon { width: 18px; height: 18px; stroke: rgba(255,255,255,0.35); fill: none; stroke-width: 1.75; flex-shrink: 0; margin-top: 2px; }
  .footer-contact-text { font-size: 0.85rem; color: rgba(255,255,255,0.5); line-height: 1.5; }
  .footer-contact-text strong { display: block; color: rgba(255,255,255,0.8); font-size: 0.78rem; margin-bottom: 2px; }
  .footer-bottom { padding-top: 28px; border-top: 1px solid rgba(255,255,255,0.07); display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px; }
  .footer-bottom p { font-size: 0.8rem; color: rgba(255,255,255,0.35); }
  .footer-bottom-links { display: flex; gap: 24px; list-style: none; }
  .footer-bottom-links a { font-size: 0.78rem; color: rgba(255,255,255,0.35); text-decoration: none; transition: color 0.2s; }
  .footer-bottom-links a:hover { color: rgba(255,255,255,0.75); }

  /* RESPONSIVE */
  @media (max-width: 1100px) {
    #navbar, section, footer { padding-left: 32px; padding-right: 32px; }
    .hero-content { padding: 80px 32px 0; gap: 48px; }
    .keunggulan-grid, .produk-grid, .sertif-grid { grid-template-columns: repeat(2,1fr); }
    .statistik-grid { grid-template-columns: repeat(2,1fr); }
    .stat-item { border-right: none; border-bottom: 1px solid rgba(255,255,255,0.08); }
    .stat-item:nth-child(1),.stat-item:nth-child(2) { border-right: 1px solid rgba(255,255,255,0.08); }
    .stat-item:last-child { border-bottom: none; }
    .footer-grid { grid-template-columns: 1fr 1fr; }
  }
  @media (max-width: 900px) {
    .tentang-grid, .kontak-grid { grid-template-columns: 1fr; }
    .tentang-float-badge { left: 0; }
    .tentang-float-badge-2 { right: 0; }
  }
  @media (max-width: 768px) {
    #navbar, section, footer { padding-left: 20px; padding-right: 20px; }
    .nav-menu, .nav-actions { display: none; }
    .hamburger { display: flex; }
    .hero-content { grid-template-columns: 1fr; padding: 100px 20px 0; gap: 40px; }
    .hero-visual { display: none; }
    .keunggulan-grid, .produk-grid { grid-template-columns: 1fr; }
    .sertif-grid { grid-template-columns: 1fr 1fr; }
    .galeri-grid { grid-template-columns: 1fr 1fr; }
    .galeri-item:nth-child(1) { grid-column: span 2; }
    .footer-grid { grid-template-columns: 1fr; gap: 32px; }
    .testimoni-card { flex: 0 0 100%; }
    .footer-bottom { flex-direction: column; text-align: center; }
    .kontak-form-wrap { padding: 28px; }
  }
  @media (max-width: 480px) {
    .sertif-grid { grid-template-columns: 1fr; }
    .hero-cta { flex-direction: column; }
    .btn-primary,.btn-secondary { justify-content: center; }
  }
</style>
</head>
<body>

<!-- LOADER -->
<div id="loader">
  <div class="loader-logo-text">THREADB2B</div>
  <div class="loader-bar"><div class="loader-bar-fill"></div></div>
  <p class="loader-text">Memuat halaman</p>
</div>

<!-- MOBILE OVERLAY & MENU -->
<div class="mobile-overlay" id="mobileOverlay" onclick="closeMobileMenu()"></div>
<div class="mobile-menu" id="mobileMenu">
  <a href="#hero"         class="mobile-nav-link" onclick="closeMobileMenu()">Beranda</a>
  <a href="tentang.php"      class="mobile-nav-link" onclick="closeMobileMenu()">Profil Perusahaan</a>
  <a href="produk.php"       class="mobile-nav-link" onclick="closeMobileMenu()">Produk</a>
  <a href="#sertifikasi"  class="mobile-nav-link" onclick="closeMobileMenu()">Sertifikasi</a>
  <a href="kontak.php"       class="mobile-nav-link" onclick="closeMobileMenu()">Kontak</a>
  <div class="mobile-actions">
    <a href="admin/login.php" class="btn-outline" style="justify-content:center;">Login Admin</a>
  </div>
</div>

<!-- NAVBAR -->
<nav id="navbar">
  <a href="#hero" class="nav-logo">
    <div class="nav-logo-text">
      THREADB2B
      <span>Premium Yarn Solutions</span>
    </div>
  </a>
  <ul class="nav-menu">
    <li><a href="#hero"         class="nav-link active">Beranda</a></li>
    <li><a href="about.php"      class="nav-link">Profil Perusahaan</a></li>
    <li><a href="produk.php"    class="nav-link">Produk</a></li>
    <li><a href="galeri.php"       class="nav-link">Galeri</a></li>
    <li><a href="sertifikasi.php"  class="nav-link">Sertifikasi</a></li>
  </ul>
  <div class="nav-actions">
    <button class="lang-btn" onclick="toggleLang(this)">
      <svg style="width:14px;height:14px" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="2" y1="12" x2="22" y2="12"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/></svg>
      <span>ID</span>
    </button>
    <a href="admin/login.php" class="btn-nav-login">Login Admin</a>
  </div>
  <button class="hamburger" id="hamburger" onclick="toggleMobileMenu()">
    <span></span><span></span><span></span>
  </button>
</nav>

<!-- HERO -->
<section id="hero">
  <div class="hero-bg"></div>
  <div class="hero-pattern"></div>
  <div class="hero-accent"></div>
  <div class="hero-accent-2"></div>
  <div class="hero-content">
    <div class="hero-left">
      <div class="hero-badge">
        <div class="hero-badge-dot"></div>
        Produsen Benang Premium Indonesia
      </div>
      <h1 class="hero-title">
        Solusi Benang<br>Berkualitas untuk<br>Industri <span class="accent">Modern</span>
      </h1>
      <p class="hero-subtitle">
        <?= htmlspecialchars(mb_substr($company['tentang_company'] ?? 'THREADB2B menghadirkan benang premium dengan standar internasional untuk kebutuhan industri tekstil global.', 0, 180)) ?>
      </p>
      <div class="hero-cta">
        <a href="produk.php" class="btn-primary">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:18px;height:18px"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/></svg>
          Jelajahi Produk
        </a>
        <a href="#kontak" class="btn-secondary">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:18px;height:18px"><path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg>
          Ajukan Sampel
        </a>
      </div>
    </div>
    <div class="hero-visual">
      <div class="hero-stats-grid">
        <?php foreach ($stat_rows as $s): ?>
        <div class="hero-stat-card">
          <div class="hero-stat-num">
            <span class="hero-counter" data-target="<?= $s['nilai'] ?>">0</span>
            <span class="unit"><?= htmlspecialchars($s['satuan']) ?></span>
          </div>
          <div class="hero-stat-label"><?= htmlspecialchars($s['label']) ?></div>
        </div>
        <?php endforeach; ?>
      </div>
      <div class="hero-cert-badge">
        <div class="hero-cert-icon">
          <svg viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" style="width:22px;height:22px"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
        </div>
        <div class="hero-cert-text">
          <?php if (!empty($cert_rows)): ?>
            <strong><?= htmlspecialchars($cert_rows[0]['nama_sertifikat']) ?></strong>
            <span>Standar Kualitas Internasional Terverifikasi</span>
          <?php else: ?>
            <strong>ISO 9001 Certified</strong>
            <span>Standar Kualitas Internasional Terverifikasi</span>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>
  <div class="hero-scroll-indicator">
    <div class="scroll-mouse"><div class="scroll-wheel"></div></div>
    <span>Gulir ke bawah</span>
  </div>
</section>

<!-- KEUNGGULAN -->
<section id="keunggulan">
  <div class="container">
    <div class="section-header center reveal">
      <div class="section-eyebrow">Mengapa Kami</div>
      <h2 class="section-title">Keunggulan Kami</h2>
      <p class="section-subtitle">Kami berkomitmen menghadirkan produk dan layanan terbaik dengan standar yang tidak pernah kompromi.</p>
    </div>
    <div class="keunggulan-grid">
      <div class="keunggulan-card reveal reveal-delay-1">
        <div class="keunggulan-icon-wrap"><svg viewBox="0 0 24 24"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg></div>
        <h3>Kualitas Premium</h3>
        <p>Bahan baku terpilih dengan kontrol kualitas ketat memastikan setiap produk memenuhi standar internasional tertinggi.</p>
      </div>
      <div class="keunggulan-card reveal reveal-delay-2">
        <div class="keunggulan-icon-wrap"><svg viewBox="0 0 24 24"><rect x="2" y="3" width="20" height="14" rx="2"/><path d="M8 21h8M12 17v4"/></svg></div>
        <h3>Produksi Modern</h3>
        <p>Fasilitas produksi berteknologi mutakhir dengan mesin terbaru untuk menghasilkan benang berkualitas konsisten.</p>
      </div>
      <div class="keunggulan-card reveal reveal-delay-3">
        <div class="keunggulan-icon-wrap"><svg viewBox="0 0 24 24"><rect x="1" y="3" width="15" height="13" rx="1"/><path d="M16 8h4l3 3v5h-7V8z"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/></svg></div>
        <h3>Pengiriman Tepat Waktu</h3>
        <p>Distribusi cepat dan andal ke seluruh Indonesia dan mancanegara, didukung jaringan logistik yang kuat.</p>
      </div>
      <div class="keunggulan-card reveal reveal-delay-4">
        <div class="keunggulan-icon-wrap"><svg viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/></svg></div>
        <h3>Layanan Profesional</h3>
        <p>Tim berpengalaman siap memberikan solusi terbaik, konsultasi teknis, dan dukungan penuh untuk bisnis Anda.</p>
      </div>
    </div>
  </div>
</section>

<!-- TENTANG — dari tabel company_profile -->
<section id="tentang">
  <div class="container">
    <div class="tentang-grid">
      <div class="tentang-left">
        <div class="reveal">
          <div class="section-eyebrow">Tentang Kami</div>
          <h2 class="section-title">Mitra Terpercaya Industri Tekstil</h2>
          <p style="color:var(--text-mid);line-height:1.75;margin-bottom:8px;">
            <?= nl2br(htmlspecialchars($company['tentang_company'] ?? '')) ?>
          </p>
          <?php if ($company['sejarah']): ?>
          <p style="color:var(--text-mid);line-height:1.75;margin-top:10px;">
            <?= nl2br(htmlspecialchars(mb_substr($company['sejarah'], 0, 300))) ?>
          </p>
          <?php endif; ?>
        </div>
        <div class="tentang-visi-misi reveal">
          <?php if ($company['visi']): ?>
          <div class="vm-item">
            <div class="vm-item-icon"><svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><circle cx="12" cy="12" r="3"/></svg></div>
            <div class="vm-item-content">
              <strong>Visi</strong>
              <p><?= htmlspecialchars($company['visi']) ?></p>
            </div>
          </div>
          <?php endif; ?>
          <?php if ($company['misi']): ?>
          <div class="vm-item">
            <div class="vm-item-icon"><svg viewBox="0 0 24 24"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg></div>
            <div class="vm-item-content">
              <strong>Misi</strong>
              <p><?= htmlspecialchars($company['misi']) ?></p>
            </div>
          </div>
          <?php endif; ?>
        </div>
        <a href="#kontak" class="btn-outline reveal">
          Hubungi Kami
          <svg viewBox="0 0 24 24"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
        </a>
      </div>
      <div class="tentang-image-wrap reveal">
        <div class="tentang-img-placeholder">
          <?php if (!empty($company['logo'])): ?>
            <img src="<?= htmlspecialchars($company['logo']) ?>" alt="<?= htmlspecialchars($company['nama_pt']) ?>" style="max-width:200px;max-height:200px;object-fit:contain">
          <?php else: ?>
            <svg style="width:80px;height:80px;opacity:0.15;stroke:white;fill:none;stroke-width:1;" viewBox="0 0 24 24"><rect x="3" y="3" width="18" height="18" rx="2"/><path d="M3 9h18M9 21V9"/></svg>
            <span style="color:rgba(255,255,255,0.3);font-size:0.8rem;"><?= htmlspecialchars($company['nama_pt']) ?></span>
          <?php endif; ?>
        </div>
        <div class="tentang-float-badge">
          <div class="float-badge-icon"><svg viewBox="0 0 24 24"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg></div>
          <div class="float-badge-text">
            <strong><?= !empty($cert_rows) ? htmlspecialchars($cert_rows[0]['nama_sertifikat']) : 'ISO 9001:2015' ?></strong>
            <span>Tersertifikasi Internasional</span>
          </div>
        </div>
        <div class="tentang-float-badge-2">
          <div class="float-badge-2-num">20<span>+</span></div>
          <div class="float-badge-2-text">Tahun Pengalaman</div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- PRODUK — dari tabel products -->
<section id="produk">
  <div class="container">
    <div class="section-header center reveal">
      <div class="section-eyebrow">Katalog Kami</div>
      <h2 class="section-title">Produk Unggulan</h2>
      <p class="section-subtitle">
        <?= $total_produk ?> item produk benang berkualitas tinggi untuk berbagai kebutuhan industri tekstil.
      </p>
    </div>

    <?php if (!empty($produk_rows)): ?>
    <div class="produk-grid">
      <?php foreach ($produk_rows as $i => $p):
        $delay    = $i < 4 ? 'reveal-delay-' . ($i + 1) : '';
        $gradient = produkGradient($p['material_type'] ?? '');
        $slug_url = slugify($p['item_no']);
      ?>
      <div class="produk-card reveal <?= $delay ?>">
        <div class="produk-img">
          <div class="produk-img-inner" style="<?= 'background:' . $gradient ?>">
            <svg style="width:56px;height:56px;opacity:.2;stroke:white;fill:none;stroke-width:1.5" viewBox="0 0 24 24">
              <circle cx="12" cy="12" r="10"/><circle cx="12" cy="12" r="4"/>
              <line x1="12" y1="2" x2="12" y2="6"/><line x1="12" y1="18" x2="12" y2="22"/>
              <line x1="2" y1="12" x2="6" y2="12"/><line x1="18" y1="12" x2="22" y2="12"/>
            </svg>
          </div>
          <div class="produk-img-overlay"></div>
          <span class="produk-tag"><?= htmlspecialchars($p['material_type'] ?? 'Produk') ?></span>
          <?php if ($p['recycled']): ?>
            <span class="produk-recycled-tag">♻ ECO</span>
          <?php endif; ?>
        </div>
        <div class="produk-body">
          <div class="produk-item-no"><?= htmlspecialchars($p['item_no']) ?></div>
          <h3><?= htmlspecialchars($p['item_name']) ?></h3>
          <p><?= htmlspecialchars($p['denier'] ?? '') ?><?= ($p['colour_name'] ? ' · ' . $p['colour_name'] : '') ?></p>
          <?php if ((float)$p['price_idr'] > 0): ?>
          <div class="produk-price">
            Rp <?= number_format((float)$p['price_idr'], 0, ',', '.') ?>
            <span class="unit">/ <?= htmlspecialchars($p['unit']) ?></span>
          </div>
          <?php else: ?>
          <div class="produk-price" style="color:var(--text-light);font-size:.8rem;font-weight:400">Hubungi kami untuk harga</div>
          <?php endif; ?>
          <a href="produk.php#<?= $slug_url ?>" class="btn-produk-detail">
            Lihat Detail
            <svg viewBox="0 0 24 24"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
          </a>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
    <?php else: ?>
    <div style="text-align:center;padding:60px 20px;color:var(--text-light)">
      <p>Belum ada produk tersedia. Tambahkan produk melalui panel admin.</p>
    </div>
    <?php endif; ?>

    <div style="text-align:center;margin-top:48px;" class="reveal">
      <a href="produk.php" class="btn-outline" style="display:inline-flex;">
        Lihat Semua <?= $total_produk ?> Produk
        <svg viewBox="0 0 24 24"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
      </a>
    </div>
  </div>
</section>

<!-- STATISTIK — statis -->
<section id="statistik">
  <div class="statistik-grid">
    <?php foreach ($stat_rows as $i => $s):
      $delay = $i > 0 ? 'reveal-delay-' . $i : '';
    ?>
    <div class="stat-item reveal <?= $delay ?>">
      <div class="stat-icon">
        <svg viewBox="0 0 24 24"><?= $s['icon'] ?></svg>
      </div>
      <div class="stat-num">
        <span class="counter" data-target="<?= (int)$s['nilai'] ?>">0</span>
        <span class="plus"><?= htmlspecialchars($s['satuan']) ?></span>
      </div>
      <div class="stat-label"><?= htmlspecialchars($s['label']) ?></div>
    </div>
    <?php endforeach; ?>
  </div>
</section>

<!-- GALERI — dari tabel gallery -->
<section id="galeri">
  <div class="container">
    <div class="section-header center reveal">
      <div class="section-eyebrow">Galeri</div>
      <h2 class="section-title">Fasilitas & Produk</h2>
      <p class="section-subtitle">Teknologi modern dan fasilitas produksi berstandar internasional untuk menghasilkan benang terbaik.</p>
    </div>
    <?php
    // Siapkan 5 slot galeri — isi dari DB jika ada, sisanya statis
    $gallery_bg   = ['galeri-bg-1','galeri-bg-2','galeri-bg-3','galeri-bg-4','galeri-bg-5'];
    $gallery_label = ['Mesin Produksi Utama','Gudang Penyimpanan','Quality Control','Area Pengiriman','Benang Koleksi'];
    $gallery_icons = [
        '<rect x="10" y="10" width="80" height="80" rx="4"/><path d="M10 40h80M10 60h80M40 10v80"/>',
        '<circle cx="50" cy="50" r="30"/><path d="M50 20v60M20 50h60"/>',
        '<path d="M50 10l40 70H10z"/><line x1="50" y1="40" x2="50" y2="65"/>',
        '<rect x="10" y="30" width="50" height="40" rx="4"/><path d="M60 40l30 10v20H60"/>',
        '<circle cx="30" cy="50" r="18"/><circle cx="60" cy="50" r="18"/><circle cx="45" cy="30" r="18"/>',
    ];
    ?>
    <div class="galeri-grid">
      <?php for ($gi = 0; $gi < 5; $gi++):
        $g     = $gallery_rows[$gi] ?? null;
        $label = $g ? $g['judul'] : $gallery_label[$gi];
        $bg    = $gallery_bg[$gi];
        $icon  = $gallery_icons[$gi];
        $delay = $gi > 0 ? 'reveal-delay-' . $gi : '';
      ?>
      <div class="galeri-item reveal <?= $delay ?>">
        <div class="galeri-inner <?= $bg ?>">
          <?php if ($g && $g['gambar']): ?>
            <img src="<?= htmlspecialchars($g['gambar']) ?>" alt="<?= htmlspecialchars($g['judul']) ?>"
                 style="width:100%;height:100%;object-fit:cover;position:absolute;inset:0">
          <?php else: ?>
            <svg class="galeri-icon" viewBox="0 0 100 100" fill="none" stroke="white" stroke-width="1.5">
              <?= $icon ?>
            </svg>
          <?php endif; ?>
        </div>
        <div class="galeri-overlay"><span><?= htmlspecialchars($label) ?></span></div>
      </div>
      <?php endfor; ?>
    </div>
  </div>
</section>

<!-- SERTIFIKASI — dari tabel certificates -->
<section id="sertifikasi">
  <div class="container">
    <div class="section-header center reveal">
      <div class="section-eyebrow">Sertifikasi</div>
      <h2 class="section-title">Diakui Secara Internasional</h2>
      <p class="section-subtitle">Komitmen kami terhadap kualitas dibuktikan dengan sertifikasi bergengsi dari lembaga independen terpercaya.</p>
    </div>
    <?php
    // Palet warna badge sertifikasi
    $cert_colors = [
        'linear-gradient(135deg,#0b3c91,#1a5ed6)',
        'linear-gradient(135deg,#16a085,#1abc9c)',
        'linear-gradient(135deg,#c0392b,#e74c3c)',
        'linear-gradient(135deg,#6a3093,#a044ff)',
    ];
    ?>
    <div class="sertif-grid">
      <?php foreach ($cert_rows as $i => $c):
        $delay = 'reveal-delay-' . (($i % 4) + 1);
        $color = $cert_colors[$i % count($cert_colors)];
      ?>
      <div class="sertif-card reveal <?= $delay ?>">
        <div class="sertif-badge-wrap" style="background:<?= $color ?>">
          <?php if ($c['gambar']): ?>
            <img src="<?= htmlspecialchars($c['gambar']) ?>" alt="<?= htmlspecialchars($c['nama_sertifikat']) ?>"
                 style="width:48px;height:48px;object-fit:contain;border-radius:4px">
          <?php else: ?>
            <svg viewBox="0 0 24 24"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
          <?php endif; ?>
        </div>
        <div class="sertif-name"><?= htmlspecialchars($c['nama_sertifikat']) ?></div>
        <?php if ($c['tahun']): ?>
          <div class="sertif-year">Sejak <?= htmlspecialchars($c['tahun']) ?></div>
        <?php endif; ?>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- TESTIMONI — statis -->
<section id="testimoni">
  <div class="container">
    <div class="section-header center reveal">
      <div class="section-eyebrow">Testimoni</div>
      <h2 class="section-title">Kata Klien Kami</h2>
      <p class="section-subtitle">Kepercayaan dari ratusan klien adalah motivasi terbesar kami untuk terus berinovasi.</p>
    </div>
    <div class="testimoni-slider reveal">
      <div class="testimoni-track" id="testimoniTrack">
        <?php foreach ($testimoni_rows as $t): ?>
        <div class="testimoni-card">
          <div class="testimoni-quote">
            <svg viewBox="0 0 24 24"><path d="M14.017 21v-7.391c0-5.704 3.731-9.57 8.983-10.609l.995 2.151c-2.432.917-3.995 3.638-3.995 5.849h4v10h-9.983zm-14.017 0v-7.391c0-5.704 3.748-9.57 9-10.609l.996 2.151c-2.433.917-3.996 3.638-3.996 5.849h3.983v10h-9.983z" fill="rgba(255,255,255,0.7)"/></svg>
          </div>
          <p class="testimoni-text"><?= htmlspecialchars($t['teks']) ?></p>
          <div class="testimoni-stars">
            <?php for ($s = 1; $s <= 5; $s++): ?>
              <svg class="star <?= $s <= $t['bintang'] ? 'star-filled' : 'star-empty' ?>" viewBox="0 0 24 24">
                <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/>
              </svg>
            <?php endfor; ?>
          </div>
          <div class="testimoni-author">
            <div class="author-avatar" style="background:<?= htmlspecialchars($t['warna_avatar']) ?>">
              <?= htmlspecialchars($t['inisial']) ?>
            </div>
            <div class="author-info">
              <strong><?= htmlspecialchars($t['nama']) ?></strong>
              <span><?= htmlspecialchars($t['perusahaan']) ?>, <?= htmlspecialchars($t['kota']) ?></span>
            </div>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
      <div class="slider-controls">
        <button class="slider-btn" id="prevBtn"><svg viewBox="0 0 24 24"><polyline points="15 18 9 12 15 6"/></svg></button>
        <div class="slider-dots" id="sliderDots">
          <?php foreach ($testimoni_rows as $i => $_): ?>
            <button class="slider-dot <?= $i === 0 ? 'active' : '' ?>" data-index="<?= $i ?>"></button>
          <?php endforeach; ?>
        </div>
        <button class="slider-btn" id="nextBtn"><svg viewBox="0 0 24 24"><polyline points="9 18 15 12 9 6"/></svg></button>
      </div>
    </div>
  </div>
</section>

<!-- KONTAK — dari company_profile -->
<section id="kontak">
  <div class="container">
    <div class="section-header center reveal">
      <div class="section-eyebrow">Hubungi Kami</div>
      <h2 class="section-title">Mari Berkolaborasi</h2>
      <p class="section-subtitle">Tim kami siap membantu kebutuhan benang industri Anda. Hubungi kami sekarang untuk konsultasi gratis.</p>
    </div>
    <div class="kontak-grid">
      <div class="kontak-form-wrap reveal">
        <h3 style="font-family:var(--font-head);font-size:1.25rem;font-weight:700;margin-bottom:6px;">Kirim Pesan</h3>
        <p style="font-size:0.85rem;color:var(--text-mid);margin-bottom:20px;">Isi formulir di bawah ini dan kami akan menghubungi Anda segera.</p>
        <div class="form-alert" id="formAlert"></div>
        <div class="form-group">
          <label class="form-label">Nama Lengkap <span style="color:var(--red)">*</span></label>
          <input type="text" class="form-input" id="inputNama" placeholder="Nama Anda">
        </div>
        <div class="form-group">
          <label class="form-label">Email <span style="color:var(--red)">*</span></label>
          <input type="email" class="form-input" id="inputEmail" placeholder="email@perusahaan.com">
        </div>
        <div class="form-group">
          <label class="form-label">Nama Perusahaan <span style="color:var(--red)">*</span></label>
          <input type="text" class="form-input" id="inputPerusahaan" placeholder="PT / CV Perusahaan Anda">
        </div>
        <div class="form-group">
          <label class="form-label">Pesan <span style="color:var(--red)">*</span></label>
          <textarea class="form-textarea" id="inputPesan" placeholder="Ceritakan kebutuhan benang Anda..."></textarea>
        </div>
        <button class="btn-submit" id="btnKirim" onclick="kirimPesan()">
          <svg viewBox="0 0 24 24" style="width:18px;height:18px;stroke:white;fill:none;stroke-width:2"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
          Kirim Pesan
        </button>
      </div>

      <div class="kontak-info reveal">
        <div>
          <h3 class="kontak-info-title">Informasi Kontak</h3>
          <p class="kontak-info-sub">Jangan ragu untuk menghubungi kami melalui berbagai saluran yang tersedia.</p>
        </div>
        <?php if ($company['alamat']): ?>
        <div class="kontak-item">
          <div class="kontak-item-icon"><svg viewBox="0 0 24 24"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg></div>
          <div class="kontak-item-text">
            <strong>Alamat Kantor</strong>
            <span><?= nl2br(htmlspecialchars($company['alamat'])) ?></span>
          </div>
        </div>
        <?php endif; ?>
        <?php if ($company['phone']): ?>
        <div class="kontak-item">
          <div class="kontak-item-icon"><svg viewBox="0 0 24 24"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07A19.5 19.5 0 0 1 4.69 12 19.79 19.79 0 0 1 1.61 3.38C1.6 2.3 2.4 1.4 3.5 1.4h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 8.91a16 16 0 0 0 6 6l.75-.75a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg></div>
          <div class="kontak-item-text">
            <strong>Telepon</strong>
            <span><?= htmlspecialchars($company['phone']) ?></span>
          </div>
        </div>
        <?php endif; ?>
        <?php if ($company['email']): ?>
        <div class="kontak-item">
          <div class="kontak-item-icon"><svg viewBox="0 0 24 24"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg></div>
          <div class="kontak-item-text">
            <strong>Email</strong>
            <span><?= htmlspecialchars($company['email']) ?></span>
          </div>
        </div>
        <?php endif; ?>
        <?php if ($company['maps']): ?>
        <div class="map-embed">
          <iframe src="<?= htmlspecialchars($company['maps']) ?>" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
        </div>
        <?php endif; ?>
        <?php if ($company['phone']): ?>
        <a href="https://wa.me/<?= preg_replace('/[^0-9]/', '', $company['phone']) ?>" class="btn-whatsapp">
          <svg viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 0 1-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 0 1-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 0 1 2.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0 0 12.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 0 0 5.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 0 0-3.48-8.413z"/></svg>
          Chat via WhatsApp
        </a>
        <?php endif; ?>
      </div>
    </div>
  </div>
</section>

<!-- FOOTER -->
<footer>
  <div class="container">
    <div class="footer-grid">
      <div>
        <div class="footer-brand-name">
          THREADB2B
          <span><?= htmlspecialchars($company['nama_pt']) ?></span>
        </div>
        <p class="footer-about"><?= htmlspecialchars(mb_substr($company['tentang_company'] ?? '', 0, 200)) ?></p>
        <div class="footer-social">
          <a href="#" class="social-btn" aria-label="Instagram"><svg viewBox="0 0 24 24"><rect x="2" y="2" width="20" height="20" rx="5"/><path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z"/><line x1="17.5" y1="6.5" x2="17.51" y2="6.5"/></svg></a>
          <a href="#" class="social-btn" aria-label="LinkedIn"><svg viewBox="0 0 24 24"><path d="M16 8a6 6 0 0 1 6 6v7h-4v-7a2 2 0 0 0-4 0v7h-4v-7a6 6 0 0 1 6-6z"/><rect x="2" y="9" width="4" height="12"/><circle cx="4" cy="4" r="2"/></svg></a>
          <a href="#" class="social-btn" aria-label="Facebook"><svg viewBox="0 0 24 24"><path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z"/></svg></a>
        </div>
      </div>
      <div>
        <div class="footer-col-title">Navigasi</div>
        <ul class="footer-links">
          <li><a href="#hero">Beranda</a></li>
          <li><a href="#tentang">Profil Perusahaan</a></li>
          <li><a href="produk.php">Produk</a></li>
          <li><a href="#galeri">Galeri</a></li>
          <li><a href="#sertifikasi">Sertifikasi</a></li>
          <li><a href="#kontak">Kontak</a></li>
        </ul>
      </div>
      <div>
        <div class="footer-col-title">Sertifikasi</div>
        <ul class="footer-links">
          <?php foreach ($cert_rows as $c): ?>
          <li><a href="#sertifikasi"><?= htmlspecialchars($c['nama_sertifikat']) ?><?= $c['tahun'] ? ' (' . $c['tahun'] . ')' : '' ?></a></li>
          <?php endforeach; ?>
        </ul>
      </div>
      <div>
        <div class="footer-col-title">Kontak</div>
        <?php if ($company['alamat']): ?>
        <div class="footer-contact-item">
          <svg class="footer-contact-icon" viewBox="0 0 24 24"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
          <div class="footer-contact-text"><strong>Alamat</strong><?= htmlspecialchars($company['alamat']) ?></div>
        </div>
        <?php endif; ?>
        <?php if ($company['phone']): ?>
        <div class="footer-contact-item">
          <svg class="footer-contact-icon" viewBox="0 0 24 24"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07A19.5 19.5 0 0 1 4.69 12 19.79 19.79 0 0 1 1.61 3.38C1.6 2.3 2.4 1.4 3.5 1.4h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L8.09 8.91a16 16 0 0 0 6 6l.75-.75a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
          <div class="footer-contact-text"><strong>Telepon</strong><?= htmlspecialchars($company['phone']) ?></div>
        </div>
        <?php endif; ?>
        <?php if ($company['email']): ?>
        <div class="footer-contact-item">
          <svg class="footer-contact-icon" viewBox="0 0 24 24"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
          <div class="footer-contact-text"><strong>Email</strong><?= htmlspecialchars($company['email']) ?></div>
        </div>
        <?php endif; ?>
      </div>
    </div>
    <div class="footer-bottom">
      <p>&copy; <?= date('Y') ?> <?= htmlspecialchars($company['nama_pt']) ?> (THREADB2B). Seluruh hak cipta dilindungi.</p>
      <ul class="footer-bottom-links">
        <li><a href="#">Kebijakan Privasi</a></li>
        <li><a href="#">Syarat &amp; Ketentuan</a></li>
      </ul>
    </div>
  </div>
</footer>

<script>
// ── LOADER ───────────────────────────────────────────────────
window.addEventListener('load', () => {
  setTimeout(() => document.getElementById('loader').classList.add('hidden'), 1600);
});

// ── NAVBAR SCROLL ────────────────────────────────────────────
const navbar = document.getElementById('navbar');
window.addEventListener('scroll', () => {
  navbar.classList.toggle('scrolled', window.scrollY > 60);
  updateActiveNav();
});
function updateActiveNav() {
  const ids = ['hero','keunggulan','tentang','produk','statistik','galeri','sertifikasi','testimoni','kontak'];
  let current = '';
  ids.forEach(id => {
    const el = document.getElementById(id);
    if (el && window.scrollY >= el.offsetTop - 120) current = id;
  });
  document.querySelectorAll('.nav-link').forEach(link => {
    const href = link.getAttribute('href');
    link.classList.toggle('active', href === '#' + current || (href === '#hero' && current === ''));
  });
}

// ── MOBILE MENU ──────────────────────────────────────────────
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

// ── LANG TOGGLE ──────────────────────────────────────────────
function toggleLang(btn) {
  const s = btn.querySelector('span');
  s.textContent = s.textContent === 'ID' ? 'EN' : 'ID';
}

// ── SCROLL REVEAL ────────────────────────────────────────────
const revealObs = new IntersectionObserver(entries => {
  entries.forEach(e => { if (e.isIntersecting) { e.target.classList.add('visible'); revealObs.unobserve(e.target); } });
}, { threshold: 0.12, rootMargin: '0px 0px -40px 0px' });
document.querySelectorAll('.reveal').forEach(el => revealObs.observe(el));

// ── COUNTER ANIMATION ────────────────────────────────────────
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

setTimeout(() => {
  document.querySelectorAll('.hero-counter').forEach(c => animateCounter(c, parseInt(c.dataset.target), 2000));
}, 1800);

// ── TESTIMONI SLIDER ─────────────────────────────────────────
let currentSlide  = 0;
const track       = document.getElementById('testimoniTrack');
const dots        = document.querySelectorAll('.slider-dot');
const totalSlides = <?= count($testimoni_rows) ?>;

function updateSlider() {
  const isMobile = window.innerWidth <= 768;
  const pct = isMobile ? currentSlide * 100 : currentSlide * (100 / 3);
  track.style.transform = `translateX(-${pct}%)`;
  dots.forEach((d, i) => d.classList.toggle('active', i === currentSlide));
}
document.getElementById('nextBtn').addEventListener('click', () => {
  const max = window.innerWidth <= 768 ? totalSlides - 1 : Math.max(0, totalSlides - 3);
  currentSlide = currentSlide >= max ? 0 : currentSlide + 1;
  updateSlider();
});
document.getElementById('prevBtn').addEventListener('click', () => {
  const max = window.innerWidth <= 768 ? totalSlides - 1 : Math.max(0, totalSlides - 3);
  currentSlide = currentSlide <= 0 ? max : currentSlide - 1;
  updateSlider();
});
dots.forEach((dot, i) => dot.addEventListener('click', () => { currentSlide = i; updateSlider(); }));
setInterval(() => {
  const max = window.innerWidth <= 768 ? totalSlides - 1 : Math.max(0, totalSlides - 3);
  currentSlide = currentSlide >= max ? 0 : currentSlide + 1;
  updateSlider();
}, 5000);

// ── AJAX FORM KONTAK ─────────────────────────────────────────
function kirimPesan() {
  const btn  = document.getElementById('btnKirim');
  const data = {
    nama:       document.getElementById('inputNama').value,
    email:      document.getElementById('inputEmail').value,
    perusahaan: document.getElementById('inputPerusahaan').value,
    pesan:      document.getElementById('inputPesan').value,
  };
  if (!data.nama || !data.email || !data.perusahaan || !data.pesan) {
    showAlert('error', 'Semua field wajib diisi.'); return;
  }
  btn.disabled  = true;
  btn.innerHTML = '<svg viewBox="0 0 24 24" style="width:18px;height:18px;stroke:white;fill:none;stroke-width:2;animation:spin 1s linear infinite"><path d="M21 12a9 9 0 1 1-6.219-8.56"/></svg> Mengirim...';
  const fd = new FormData();
  Object.entries(data).forEach(([k, v]) => fd.append(k, v));
  fetch('submit_kontak.php', { method: 'POST', body: fd })
    .then(r => r.json())
    .then(res => {
      if (res.success) {
        showAlert('success', res.message);
        ['inputNama','inputEmail','inputPerusahaan','inputPesan'].forEach(id => document.getElementById(id).value = '');
      } else showAlert('error', res.message);
    })
    .catch(() => showAlert('error', 'Terjadi kesalahan. Silakan coba lagi.'))
    .finally(() => {
      btn.disabled  = false;
      btn.innerHTML = '<svg viewBox="0 0 24 24" style="width:18px;height:18px;stroke:white;fill:none;stroke-width:2"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg> Kirim Pesan';
    });
}
function showAlert(type, msg) {
  const el = document.getElementById('formAlert');
  el.className = 'form-alert ' + type;
  el.style.display = 'block';
  el.textContent = msg;
  el.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
  setTimeout(() => { el.style.display = 'none'; }, 7000);
}

// ── SMOOTH SCROLL ────────────────────────────────────────────
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