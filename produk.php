<?php
// ============================================================
// produk.php — Halaman Produk & Harga ThreadB2B
// Terhubung ke: config.php + tabel `products` (threadb2b.sql)
// Kolom: item_no, item_name, denier, colour_no, colour_name,
//        material_type, recycled, unit, price_idr
// ============================================================

require_once __DIR__ . '/assets/config.php';

$db = getDB();

// ── Ambil semua material_type unik sebagai "kategori" ─────────
$kategori_rows = [];
$res_kat = $db->query(
    "SELECT DISTINCT
        COALESCE(NULLIF(TRIM(material_type),''), 'Lainnya') AS material_type,
        COUNT(*) AS jumlah
     FROM products
     GROUP BY material_type
     ORDER BY material_type ASC"
);
while ($row = $res_kat->fetch_assoc()) {
    $kategori_rows[] = $row;
}

// ── Ambil semua produk ─────────────────────────────────────────
$produk_rows = [];
$res_prod = $db->query(
    "SELECT
        item_no, item_name, denier,
        colour_no, colour_name,
        COALESCE(NULLIF(TRIM(material_type),''), 'Lainnya') AS material_type,
        recycled, unit, price_idr, created_at
     FROM products
     ORDER BY material_type ASC, item_no ASC"
);
while ($row = $res_prod->fetch_assoc()) {
    $produk_rows[] = $row;
}

// ── Kelompokkan produk per material_type ───────────────────────
$produk_per_kat = [];
foreach ($produk_rows as $p) {
    $produk_per_kat[$p['material_type']][] = $p;
}

// ── Total produk ───────────────────────────────────────────────
$total_produk = count($produk_rows);

// ── Helper: slug aman untuk HTML id/class ─────────────────────
function slugify(string $s): string {
    return preg_replace('/[^a-z0-9]+/', '-', strtolower(trim($s)));
}

// ── Palet warna per kategori (auto-assign) ────────────────────
$palettes = [
    ['dot' => '#3a7bd5', 'badge_bg' => 'rgba(58,123,213,.12)', 'badge_fg' => '#2b60c4'],
    ['dot' => '#e8923b', 'badge_bg' => 'rgba(232,146,59,.12)', 'badge_fg' => '#c47620'],
    ['dot' => '#19c97a', 'badge_bg' => 'rgba(25,201,122,.12)', 'badge_fg' => '#0e9e5c'],
    ['dot' => '#b45de2', 'badge_bg' => 'rgba(180,93,226,.12)', 'badge_fg' => '#8c2fd4'],
    ['dot' => '#e23b5d', 'badge_bg' => 'rgba(226,59,93,.12)',  'badge_fg' => '#c0213f'],
];
$kat_palette = [];
foreach (array_keys($produk_per_kat) as $i => $kat) {
    $kat_palette[$kat] = $palettes[$i % count($palettes)];
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Produk & Harga — ThreadB2B</title>
<meta name="description" content="Katalog produk benang ThreadB2B: daftar lengkap item, denier, warna, dan harga per satuan.">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;500;600;700;800&family=DM+Sans:ital,wght@0,300;0,400;0,500;0,600;1,400&display=swap" rel="stylesheet">
<style>
:root {
  --navy:       #0B3C91;
  --navy-dark:  #072d6e;
  --navy-deep:  #041e4f;
  --red:        #E31E24;
  --red-dark:   #b8181d;
  --white:      #ffffff;
  --off-white:  #f7f8fc;
  --light:      #eef0f6;
  --mid:        #c8ccd8;
  --text:       #0d1b3e;
  --text-mid:   #4a5470;
  --text-light: #8891ab;
  --fh: 'Syne', sans-serif;
  --fb: 'DM Sans', sans-serif;
  --r:  12px;
  --rl: 20px;
  --sh: 0 4px 24px rgba(11,60,145,.10);
  --sh2:0 16px 48px rgba(11,60,145,.18);
  --tr: all .3s cubic-bezier(.4,0,.2,1);
}
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
html{scroll-behavior:smooth;font-size:16px}
body{font-family:var(--fb);color:var(--text);background:var(--white);overflow-x:hidden;line-height:1.6}

/* ─── TOPBAR ─── */
.topbar{
  background:var(--navy-deep);
  padding:10px 48px;
  display:flex;align-items:center;justify-content:space-between;
  font-size:.75rem;color:rgba(255,255,255,.55);letter-spacing:.05em;
}
.topbar-left{display:flex;align-items:center;gap:24px}
.topbar-badge{
  display:inline-flex;align-items:center;gap:6px;
  background:rgba(227,30,36,.2);color:#ff8a8e;
  padding:4px 12px;border-radius:99px;font-weight:600;font-size:.7rem;
}
.topbar-badge-dot{width:5px;height:5px;background:var(--red);border-radius:50%;animation:blink 1.4s ease-in-out infinite}
@keyframes blink{0%,100%{opacity:1}50%{opacity:.2}}

/* ─── NAVBAR ─── */
nav{
  position:sticky;top:0;z-index:100;
  background:rgba(255,255,255,.96);backdrop-filter:blur(16px);
  border-bottom:1px solid rgba(11,60,145,.08);
  padding:0 48px;height:68px;
  display:flex;align-items:center;justify-content:space-between;
  box-shadow:0 2px 16px rgba(11,60,145,.07);
}
.nav-logo{display:flex;align-items:center;gap:10px;text-decoration:none}
.nav-logo-mark{width:36px;height:36px;background:var(--navy);border-radius:8px;display:flex;align-items:center;justify-content:center}
.nav-logo-mark svg{width:20px;height:20px;stroke:white;fill:none;stroke-width:2}
.nav-brand{font-family:var(--fh);font-weight:800;font-size:.95rem;color:var(--navy);line-height:1.1}
.nav-brand span{display:block;font-size:.58rem;font-weight:400;color:var(--text-light);letter-spacing:.08em;font-family:var(--fb)}
.nav-links{display:flex;align-items:center;gap:4px;list-style:none}
.nav-links a{color:var(--text-mid);text-decoration:none;font-size:.85rem;font-weight:500;padding:7px 14px;border-radius:8px;transition:var(--tr)}
.nav-links a:hover{color:var(--navy);background:rgba(11,60,145,.06)}
.nav-links a.active{color:var(--navy);background:rgba(11,60,145,.08);font-weight:600}
.nav-cta{background:var(--red);color:white;font-family:var(--fb);font-size:.82rem;font-weight:600;padding:9px 20px;border-radius:var(--r);text-decoration:none;transition:var(--tr);border:none;cursor:pointer}
.nav-cta:hover{background:var(--red-dark);transform:translateY(-1px);box-shadow:0 4px 16px rgba(227,30,36,.35)}

/* ─── HERO ─── */
.hero{
  background:linear-gradient(130deg,var(--navy-deep) 0%,var(--navy) 60%,#1a4ab8 100%);
  padding:80px 48px 64px;position:relative;overflow:hidden;
}
.hero::before{content:'';position:absolute;inset:0;background:repeating-linear-gradient(0deg,rgba(255,255,255,.025) 0,rgba(255,255,255,.025) 1px,transparent 1px,transparent 72px),repeating-linear-gradient(90deg,rgba(255,255,255,.025) 0,rgba(255,255,255,.025) 1px,transparent 1px,transparent 72px)}
.hero-glow{position:absolute;top:-100px;right:-80px;width:520px;height:520px;background:radial-gradient(circle,rgba(227,30,36,.22) 0%,transparent 68%);pointer-events:none}
.hero-inner{max-width:1200px;margin:0 auto;position:relative;z-index:1;display:flex;align-items:center;justify-content:space-between;gap:48px;flex-wrap:wrap}
.hero-eyebrow{display:inline-flex;align-items:center;gap:8px;background:rgba(255,255,255,.1);border:1px solid rgba(255,255,255,.2);color:rgba(255,255,255,.85);font-size:.7rem;font-weight:600;letter-spacing:.14em;text-transform:uppercase;padding:6px 14px;border-radius:99px;margin-bottom:18px}
.hero-eyebrow-dot{width:5px;height:5px;background:var(--red);border-radius:50%}
.hero-title{font-family:var(--fh);font-size:clamp(2rem,3.5vw,3.2rem);font-weight:800;color:white;line-height:1.1;letter-spacing:-.02em;margin-bottom:16px}
.hero-title .accent{color:#ff8a8e}
.hero-sub{font-size:.95rem;color:rgba(255,255,255,.65);max-width:480px;line-height:1.7;margin-bottom:32px}
.hero-pills{display:flex;gap:10px;flex-wrap:wrap}
.pill{display:inline-flex;align-items:center;gap:6px;background:rgba(255,255,255,.08);border:1px solid rgba(255,255,255,.15);color:rgba(255,255,255,.8);font-size:.78rem;font-weight:500;padding:7px 14px;border-radius:99px}
.pill svg{width:14px;height:14px;stroke:currentColor;fill:none;stroke-width:2}
.hero-stats{display:grid;grid-template-columns:repeat(3,1fr);gap:16px;min-width:300px}
.hero-stat{background:rgba(255,255,255,.07);border:1px solid rgba(255,255,255,.12);border-radius:var(--rl);padding:20px 16px;text-align:center;backdrop-filter:blur(8px);transition:var(--tr)}
.hero-stat:hover{background:rgba(255,255,255,.12);transform:translateY(-3px)}
.hero-stat-num{font-family:var(--fh);font-size:2rem;font-weight:800;color:white;line-height:1}
.hero-stat-num .u{color:var(--red);font-size:1.2rem}
.hero-stat-label{font-size:.7rem;color:rgba(255,255,255,.5);margin-top:5px;letter-spacing:.04em;text-transform:uppercase}

/* ─── MAIN LAYOUT ─── */
main{max-width:1200px;margin:0 auto;padding:64px 48px 80px}

/* ─── FILTER BAR ─── */
.filter-bar{
  display:flex;align-items:center;gap:10px;margin-bottom:40px;
  background:var(--off-white);border:1px solid rgba(11,60,145,.07);
  border-radius:var(--rl);padding:12px 16px;flex-wrap:wrap;
}
.filter-label{font-size:.72rem;font-weight:700;letter-spacing:.12em;text-transform:uppercase;color:var(--text-light);padding-right:8px;border-right:1px solid var(--mid);white-space:nowrap}
.filter-btn{
  display:inline-flex;align-items:center;gap:7px;
  padding:9px 18px;border-radius:99px;
  border:2px solid var(--mid);background:var(--white);
  font-family:var(--fb);font-size:.83rem;font-weight:600;
  color:var(--text-mid);cursor:pointer;transition:var(--tr);white-space:nowrap;
}
.filter-btn .cat-dot{width:8px;height:8px;border-radius:50%;flex-shrink:0;background:var(--mid)}
.filter-btn:hover{border-color:var(--navy);color:var(--navy)}
.filter-btn.active{background:var(--navy);border-color:var(--navy);color:white}
.filter-btn.active .cat-dot{opacity:.7}
.filter-count{font-size:.68rem;padding:2px 8px;border-radius:99px}
.filter-btn.active .filter-count{background:rgba(255,255,255,.2)}
.filter-btn:not(.active) .filter-count{background:rgba(11,60,145,.08);color:var(--navy)}
.filter-search{
  margin-left:auto;display:flex;align-items:center;gap:8px;
  background:var(--white);border:1.5px solid var(--mid);
  border-radius:99px;padding:8px 16px;transition:var(--tr);
}
.filter-search:focus-within{border-color:var(--navy);box-shadow:0 0 0 3px rgba(11,60,145,.08)}
.filter-search svg{width:15px;height:15px;stroke:var(--text-light);fill:none;stroke-width:2;flex-shrink:0}
.filter-search input{border:none;outline:none;background:transparent;font-family:var(--fb);font-size:.83rem;color:var(--text);width:180px}
.filter-search input::placeholder{color:var(--text-light)}

/* ─── SECTION HEADER ─── */
.sec-head{margin-bottom:20px;display:flex;align-items:flex-end;justify-content:space-between;gap:16px;flex-wrap:wrap}
.sec-title{font-family:var(--fh);font-size:1.4rem;font-weight:800;color:var(--text);display:flex;align-items:center;gap:12px}
.sec-badge{display:inline-flex;align-items:center;gap:6px;font-size:.7rem;font-weight:700;letter-spacing:.1em;text-transform:uppercase;padding:5px 12px;border-radius:99px}
.sec-count{font-size:.82rem;color:var(--text-light);font-style:italic}

/* ─── CATEGORY PANEL ─── */
.cat-panel{display:none;animation:panelIn .35s ease;margin-bottom:56px}
.cat-panel.active{display:block}
@keyframes panelIn{from{opacity:0;transform:translateY(12px)}to{opacity:1;transform:translateY(0)}}

/* ─── TABLE ─── */
.table-wrap{border-radius:var(--rl);overflow:hidden;border:1px solid rgba(11,60,145,.08);box-shadow:var(--sh)}

/* Header & Row Grid:  Item No | Item Name | Denier | Colour | Material | Recycled | Unit | Harga */
.t-head{
  display:grid;
  grid-template-columns: 160px 1fr 120px 180px 100px 80px 160px;
  padding:14px 24px;
  background:linear-gradient(90deg,var(--navy-deep),var(--navy));
  font-family:var(--fh);font-size:.7rem;font-weight:700;
  letter-spacing:.12em;text-transform:uppercase;color:rgba(255,255,255,.65);gap:8px;
}
.t-row{
  display:grid;
  grid-template-columns: 160px 1fr 120px 180px 100px 80px 160px;
  padding:0 24px;align-items:stretch;gap:8px;
  border-bottom:1px solid rgba(11,60,145,.05);
  transition:background .18s;cursor:default;
}
.t-row:last-child{border-bottom:none}
.t-row:hover{background:rgba(11,60,145,.025)}
.t-row:nth-child(even){background:rgba(247,248,252,.5)}
.t-row:nth-child(even):hover{background:rgba(11,60,145,.03)}
.t-row>div{padding:14px 0;display:flex;align-items:center}

/* Cell styles */
.c-itemno{font-family:var(--fh);font-size:.82rem;font-weight:700;color:var(--navy);word-break:break-all}
.c-name{font-size:.83rem;color:var(--text);font-weight:500}
.c-denier{font-size:.82rem;color:var(--text-mid);font-family:monospace;letter-spacing:.03em}
.c-colour{display:flex;align-items:center;gap:8px;font-size:.82rem;color:var(--text-mid)}
.c-colour .cno{font-size:.7rem;color:var(--text-light);font-family:monospace}
.c-material{font-size:.75rem;font-weight:600;color:var(--text-mid)}
.c-recycled{display:flex;align-items:center}
.recycled-badge{
  display:inline-flex;align-items:center;gap:4px;
  background:rgba(25,201,122,.1);color:#0e9e5c;
  font-size:.65rem;font-weight:700;padding:3px 8px;border-radius:99px;letter-spacing:.05em;
}
.c-unit{font-size:.78rem;color:var(--text-light);font-weight:600;letter-spacing:.05em}
.c-price{
  font-family:var(--fh);font-size:1rem;font-weight:800;color:var(--red);
  justify-content:flex-end;
}

.hidden-row{display:none!important}

/* ─── STATS BAR ─── */
.stats-bar{
  display:grid;grid-template-columns:repeat(4,1fr);gap:16px;
  margin-bottom:48px;
}
.stat-card{
  background:var(--off-white);border:1px solid rgba(11,60,145,.07);
  border-radius:var(--rl);padding:22px 20px;display:flex;align-items:center;gap:14px;
  transition:var(--tr);
}
.stat-card:hover{transform:translateY(-3px);box-shadow:var(--sh)}
.stat-icon{width:44px;height:44px;background:linear-gradient(135deg,var(--navy),#1a5ed6);border-radius:12px;display:flex;align-items:center;justify-content:center;flex-shrink:0}
.stat-icon svg{width:20px;height:20px;stroke:white;fill:none;stroke-width:2}
.stat-num{font-family:var(--fh);font-size:1.6rem;font-weight:800;color:var(--text);line-height:1}
.stat-label{font-size:.74rem;color:var(--text-light);margin-top:3px}

/* ─── EMPTY STATE ─── */
.empty-state{text-align:center;padding:64px 32px;color:var(--text-light)}
.empty-state svg{width:48px;height:48px;stroke:var(--mid);fill:none;stroke-width:1;margin:0 auto 16px;display:block}
.empty-state p{font-size:.9rem}

/* ─── CTA STRIP ─── */
.cta-strip{
  background:linear-gradient(130deg,var(--navy-deep),var(--navy));
  border-radius:var(--rl);padding:48px;position:relative;overflow:hidden;
  display:flex;align-items:center;justify-content:space-between;gap:32px;flex-wrap:wrap;
  margin-top:32px;
}
.cta-strip::before{content:'';position:absolute;inset:0;background:repeating-linear-gradient(45deg,rgba(255,255,255,.02) 0,rgba(255,255,255,.02) 1px,transparent 1px,transparent 28px)}
.cta-glow{position:absolute;right:-60px;top:-60px;width:300px;height:300px;background:radial-gradient(circle,rgba(227,30,36,.25),transparent 70%);pointer-events:none}
.cta-text{position:relative;z-index:1}
.cta-text h2{font-family:var(--fh);font-size:1.6rem;font-weight:800;color:white;margin-bottom:8px}
.cta-text p{font-size:.9rem;color:rgba(255,255,255,.6);max-width:420px}
.cta-actions{display:flex;gap:12px;flex-wrap:wrap;position:relative;z-index:1}
.btn-wa{display:inline-flex;align-items:center;gap:9px;background:#25D366;color:white;font-family:var(--fb);font-size:.88rem;font-weight:600;padding:13px 24px;border-radius:var(--r);text-decoration:none;transition:var(--tr)}
.btn-wa:hover{background:#128C7E;transform:translateY(-2px);box-shadow:0 6px 20px rgba(37,211,102,.35)}
.btn-wa svg{width:18px;height:18px;fill:white}
.btn-white{display:inline-flex;align-items:center;gap:9px;background:rgba(255,255,255,.1);color:white;font-family:var(--fb);font-size:.88rem;font-weight:500;padding:13px 24px;border-radius:var(--r);text-decoration:none;border:1.5px solid rgba(255,255,255,.3);transition:var(--tr)}
.btn-white:hover{background:rgba(255,255,255,.2);border-color:white;transform:translateY(-2px)}
.btn-white svg{width:16px;height:16px;stroke:white;fill:none;stroke-width:2}

/* ─── FOOTER ─── */
footer{background:var(--navy-deep);color:white;padding:32px 48px;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:16px}
.foot-brand{font-family:var(--fh);font-weight:700;font-size:.9rem}
.foot-brand span{color:var(--red)}
.foot-note{font-size:.75rem;color:rgba(255,255,255,.35)}
.foot-links{display:flex;gap:20px}
.foot-links a{color:rgba(255,255,255,.4);text-decoration:none;font-size:.78rem;transition:color .2s}
.foot-links a:hover{color:rgba(255,255,255,.8)}

/* ─── SCROLL REVEAL ─── */
.fade{opacity:0;transform:translateY(24px);transition:opacity .6s ease,transform .6s ease}
.fade.vis{opacity:1;transform:translateY(0)}

/* ─── RESPONSIVE ─── */
@media(max-width:1200px){
  .t-head,.t-row{grid-template-columns:140px 1fr 100px 160px 90px 60px 130px}
}
@media(max-width:1024px){
  main,.topbar,nav,footer,.hero{padding-left:32px;padding-right:32px}
  .stats-bar{grid-template-columns:1fr 1fr}
  /* hide material & recycled on tablet */
  .t-head,.t-row{grid-template-columns:130px 1fr 100px 150px 120px}
  .col-material,.col-recycled{display:none}
}
@media(max-width:768px){
  main,.topbar,nav,footer,.hero{padding-left:20px;padding-right:20px}
  .topbar{display:none}
  nav{height:60px}
  .nav-links{display:none}
  .filter-search{display:none}
  .stats-bar{grid-template-columns:1fr 1fr}
  /* compact mobile: Item No | Name | Harga */
  .t-head,.t-row{grid-template-columns:110px 1fr 100px}
  .col-denier,.col-colour,.col-material,.col-recycled,.col-unit{display:none}
  .cta-strip{padding:32px 20px}
}
</style>
</head>
<body>

<!-- TOPBAR -->
<div class="topbar">
  <div class="topbar-left">
    <div class="topbar-badge"><div class="topbar-badge-dot"></div>Katalog Aktif <?= date('Y') ?></div>
    <span>Harga dalam Rupiah (IDR) per satuan</span>
  </div>
  <span>ThreadB2B · PT Benang Nusantara</span>
</div>

<!-- NAVBAR -->
<nav>
  <a href="index.php" class="nav-logo">
    <div class="nav-logo-mark">
      <svg viewBox="0 0 24 24"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/></svg>
    </div>
    <div class="nav-brand">THREAD<span style="color:var(--red)">B2B</span><span>Platform Tekstil B2B</span></div>
  </a>
  <ul class="nav-links">
    <li><a href="index.php">Beranda</a></li>
    <li><a href="produk.php" class="active">Produk &amp; Harga</a></li>
    <li><a href="orders.php">Pesanan</a></li>
    <li><a href="kontak.php">Kontak</a></li>
  </ul>
  <a href="kontak.php" class="nav-cta">Minta Penawaran</a>
</nav>

<!-- HERO -->
<section class="hero">
  <div class="hero-glow"></div>
  <div class="hero-inner">
    <div class="hero-text">
      <div class="hero-eyebrow"><div class="hero-eyebrow-dot"></div>Katalog Resmi — ThreadB2B</div>
      <h1 class="hero-title">Produk &amp;<br><span class="accent">Harga</span></h1>
      <p class="hero-sub">
        <?= $total_produk ?> item produk dari
        <?= count($kategori_rows) ?> kategori material —
        <?= implode(', ', array_column($kategori_rows, 'material_type')) ?>.
      </p>
      <div class="hero-pills">
        <div class="pill">
          <svg viewBox="0 0 24 24"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
          Kualitas Terverifikasi
        </div>
        <div class="pill">
          <svg viewBox="0 0 24 24"><rect x="2" y="5" width="20" height="14" rx="2"/><line x1="2" y1="10" x2="22" y2="10"/></svg>
          Harga Kompetitif
        </div>
        <div class="pill">
          <svg viewBox="0 0 24 24"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
          Ready Stock
        </div>
      </div>
    </div>

    <!-- Stats: total produk, kategori, recycled -->
    <div class="hero-stats">
      <div class="hero-stat">
        <div class="hero-stat-num"><?= $total_produk ?><span class="u">+</span></div>
        <div class="hero-stat-label">Total Item</div>
      </div>
      <div class="hero-stat">
        <div class="hero-stat-num"><?= count($kategori_rows) ?></div>
        <div class="hero-stat-label">Kategori</div>
      </div>
      <div class="hero-stat">
        <?php
          $recycled_count = count(array_filter($produk_rows, fn($p) => $p['recycled'] == 1));
        ?>
        <div class="hero-stat-num"><?= $recycled_count ?></div>
        <div class="hero-stat-label">Recycled</div>
      </div>
    </div>
  </div>
</section>

<!-- MAIN CONTENT -->
<main>

  <!-- STATS BAR -->
  <div class="stats-bar fade">
    <div class="stat-card">
      <div class="stat-icon"><svg viewBox="0 0 24 24"><path d="M20 7H4a2 2 0 0 0-2 2v6a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V9a2 2 0 0 0-2-2z"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/></svg></div>
      <div>
        <div class="stat-num"><?= $total_produk ?></div>
        <div class="stat-label">Total Produk</div>
      </div>
    </div>
    <div class="stat-card">
      <div class="stat-icon"><svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="2" y1="12" x2="22" y2="12"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/></svg></div>
      <div>
        <div class="stat-num"><?= count($kategori_rows) ?></div>
        <div class="stat-label">Kategori Material</div>
      </div>
    </div>
    <div class="stat-card">
      <div class="stat-icon" style="background:linear-gradient(135deg,#19c97a,#0e9e5c)"><svg viewBox="0 0 24 24"><polyline points="23 6 13.5 15.5 8.5 10.5 1 18"/><polyline points="17 6 23 6 23 12"/></svg></div>
      <div>
        <div class="stat-num"><?= $recycled_count ?></div>
        <div class="stat-label">Produk Recycled</div>
      </div>
    </div>
    <div class="stat-card">
      <?php
        $units = array_unique(array_column($produk_rows, 'unit'));
      ?>
      <div class="stat-icon" style="background:linear-gradient(135deg,#e8923b,#c47620)"><svg viewBox="0 0 24 24"><line x1="8" y1="6" x2="21" y2="6"/><line x1="8" y1="12" x2="21" y2="12"/><line x1="8" y1="18" x2="21" y2="18"/><line x1="3" y1="6" x2="3.01" y2="6"/><line x1="3" y1="12" x2="3.01" y2="12"/><line x1="3" y1="18" x2="3.01" y2="18"/></svg></div>
      <div>
        <div class="stat-num"><?= implode(', ', $units) ?></div>
        <div class="stat-label">Satuan Tersedia</div>
      </div>
    </div>
  </div>

  <!-- FILTER BAR -->
  <div class="filter-bar fade">
    <span class="filter-label">Kategori</span>

    <button class="filter-btn active" onclick="setFilter('all', this)">
      <span class="cat-dot"></span>
      Semua
      <span class="filter-count"><?= $total_produk ?></span>
    </button>

    <?php foreach ($kategori_rows as $k):
      $slug = slugify($k['material_type']);
      $pal  = $kat_palette[$k['material_type']] ?? $palettes[0];
    ?>
    <button class="filter-btn"
            onclick="setFilter('<?= htmlspecialchars($slug) ?>', this)"
            data-dot="<?= htmlspecialchars($pal['dot']) ?>">
      <span class="cat-dot" style="background:<?= htmlspecialchars($pal['dot']) ?>"></span>
      <?= htmlspecialchars($k['material_type']) ?>
      <span class="filter-count"><?= (int)$k['jumlah'] ?></span>
    </button>
    <?php endforeach; ?>

    <div class="filter-search">
      <svg viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
      <input type="text" placeholder="Cari item no, nama, denier, warna..." oninput="searchTable(this.value)">
    </div>
  </div>

  <!-- ══════════ PANEL PER KATEGORI MATERIAL ══════════ -->
  <?php foreach ($kategori_rows as $idx_k => $k):
    $slug   = slugify($k['material_type']);
    $rows   = $produk_per_kat[$k['material_type']] ?? [];
    $pal    = $kat_palette[$k['material_type']] ?? $palettes[0];
    $is_first = ($idx_k === 0);
  ?>
  <div class="cat-panel <?= $is_first ? 'active' : '' ?>" id="panel-<?= htmlspecialchars($slug) ?>" data-slug="<?= htmlspecialchars($slug) ?>">

    <!-- Section header -->
    <div class="sec-head fade">
      <div class="sec-title">
        <span class="sec-badge" style="background:<?= htmlspecialchars($pal['badge_bg']) ?>;color:<?= htmlspecialchars($pal['badge_fg']) ?>">
          <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/></svg>
          <?= htmlspecialchars($k['material_type']) ?>
        </span>
        <?= htmlspecialchars($k['material_type']) ?>
      </div>
      <div class="sec-count"><?= (int)$k['jumlah'] ?> item</div>
    </div>

    <!-- Tabel produk -->
    <div class="table-wrap fade">
      <div class="t-head">
        <div>Item No</div>
        <div>Nama Produk</div>
        <div>Denier</div>
        <div>Warna</div>
        <div class="col-material">Material</div>
        <div class="col-recycled">Eco</div>
        <div style="text-align:right">Harga / Unit</div>
      </div>

      <?php foreach ($rows as $p): ?>
      <div class="t-row" data-slug="<?= htmlspecialchars($slug) ?>">
        <!-- Item No -->
        <div class="c-itemno"><?= htmlspecialchars($p['item_no']) ?></div>

        <!-- Item Name -->
        <div class="c-name"><?= htmlspecialchars($p['item_name']) ?></div>

        <!-- Denier -->
        <div class="c-denier col-denier"><?= htmlspecialchars($p['denier'] ?? '—') ?></div>

        <!-- Colour -->
        <div class="c-colour col-colour">
          <?php if ($p['colour_no'] || $p['colour_name']): ?>
            <span style="width:9px;height:9px;border-radius:50%;background:conic-gradient(#e31e24 0 33%,#0b3c91 33% 66%,#16a085 66%);flex-shrink:0;display:inline-block"></span>
            <span><?= htmlspecialchars($p['colour_name'] ?? '') ?></span>
            <?php if ($p['colour_no']): ?>
              <span class="cno">#<?= htmlspecialchars($p['colour_no']) ?></span>
            <?php endif; ?>
          <?php else: ?>
            <span style="color:var(--text-light)">—</span>
          <?php endif; ?>
        </div>

        <!-- Material type -->
        <div class="c-material col-material"><?= htmlspecialchars($p['material_type']) ?></div>

        <!-- Recycled badge -->
        <div class="c-recycled col-recycled">
          <?php if ($p['recycled']): ?>
            <span class="recycled-badge">♻ ECO</span>
          <?php else: ?>
            <span style="color:var(--text-light);font-size:.75rem">—</span>
          <?php endif; ?>
        </div>

        <!-- Price -->
        <div class="c-price col-unit">
          <?php if ((float)$p['price_idr'] > 0): ?>
            Rp <?= number_format((float)$p['price_idr'], 0, ',', '.') ?>
            <span style="font-size:.68rem;font-weight:400;color:var(--text-light);margin-left:4px">/ <?= htmlspecialchars($p['unit']) ?></span>
          <?php else: ?>
            <span style="color:var(--text-light);font-size:.82rem;font-weight:400">On Request</span>
          <?php endif; ?>
        </div>
      </div>
      <?php endforeach; ?>
    </div><!-- /.table-wrap -->

  </div><!-- /.cat-panel -->
  <?php endforeach; ?>

  <!-- EMPTY STATE -->
  <div id="empty-state" style="display:none">
    <div class="empty-state">
      <svg viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
      <p>Tidak ada produk yang cocok dengan pencarian Anda.</p>
    </div>
  </div>

  <!-- CTA STRIP -->
  <div class="cta-strip fade">
    <div class="cta-glow"></div>
    <div class="cta-text">
      <h2>Butuh Penawaran Khusus?</h2>
      <p>Tim kami siap membantu — sampaikan kebutuhan material, denier, warna, dan volume untuk mendapatkan harga terbaik.</p>
    </div>
    <div class="cta-actions">
      <a href="https://wa.me/62" class="btn-wa">
        <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 0 1-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 0 1-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 0 1 2.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0 0 12.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 0 0 5.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 0 0-3.48-8.413z"/></svg>
        Chat via WhatsApp
      </a>
      <a href="index.php#kontak" class="btn-white">
        <svg viewBox="0 0 24 24"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
        Isi Formulir
      </a>
    </div>
  </div>

</main>

<!-- FOOTER -->
<footer>
  <div class="foot-brand">THREAD<span>B2B</span> · PT Benang Nusantara</div>
  <div class="foot-note">&copy; <?= date('Y') ?> · Harga dapat berubah sewaktu-waktu</div>
  <div class="foot-links">
    <a href="index.php">Beranda</a>
    <a href="produk.php">Produk</a>
    <a href="index.php#kontak">Kontak</a>
  </div>
</footer>

<script>
// ── Inject slug list dari PHP ──────────────────────────────────
const PANEL_SLUGS = <?= json_encode(
    array_map(fn($k) => slugify($k['material_type']), $kategori_rows)
) ?>;

let activeFilter = 'all';

// ── FILTER ────────────────────────────────────────────────────
function setFilter(cat, btn) {
  activeFilter = cat;
  document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
  btn.classList.add('active');

  if (cat === 'all') {
    PANEL_SLUGS.forEach(slug => {
      const el = document.getElementById('panel-' + slug);
      if (el) el.classList.add('active');
    });
  } else {
    PANEL_SLUGS.forEach(slug => {
      const el = document.getElementById('panel-' + slug);
      if (el) el.classList.toggle('active', slug === cat);
    });
  }
  observeFades();
}

// ── SEARCH ────────────────────────────────────────────────────
function searchTable(q) {
  const term = q.toLowerCase().trim();
  let anyVisible = false;

  document.querySelectorAll('.t-row').forEach(row => {
    if (!term) { row.classList.remove('hidden-row'); anyVisible = true; return; }
    const text = row.textContent.toLowerCase();
    const show = text.includes(term);
    row.classList.toggle('hidden-row', !show);
    if (show) anyVisible = true;
  });

  document.getElementById('empty-state').style.display = anyVisible ? 'none' : 'block';

  if (term) {
    document.querySelectorAll('.cat-panel').forEach(p => p.classList.add('active'));
  } else {
    setFilter(activeFilter, document.querySelector('.filter-btn.active'));
  }
}

// ── SCROLL REVEAL ─────────────────────────────────────────────
function observeFades() {
  const io = new IntersectionObserver(entries => {
    entries.forEach(e => {
      if (e.isIntersecting) { e.target.classList.add('vis'); io.unobserve(e.target); }
    });
  }, { threshold: 0.06, rootMargin: '0px 0px -24px 0px' });
  document.querySelectorAll('.fade:not(.vis)').forEach(el => io.observe(el));
}
observeFades();
</script>
</body>
</html>