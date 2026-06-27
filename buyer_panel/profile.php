<?php
// ============================================================
//  buyer_panel/profile.php
//  Ringkasan profil buyer (read-only) — detail edit ada di settings.php
// ============================================================

define('REQUIRED_ROLE', 'buyer');
require_once __DIR__ . '/../assets/verifyRoleRedirect.php';
require_once __DIR__ . '/partials/config.php';

$idBuyer   = (int) ($currentBuyer['id_buyer'] ?? 0);
$idUser    = (int) ($currentBuyer['id_user']  ?? 0);
$pageTitle = 'Profil Saya';

// ------------------------------------------------------------
// Profil + akun
// ------------------------------------------------------------
$stmt = $conn->prepare("
    SELECT bp.*, u.email, u.role, u.created_at AS akun_dibuat
    FROM buyer_profile bp
    JOIN users u ON u.id_user = bp.id_user
    WHERE bp.id_buyer = ?
");
$stmt->bind_param('i', $idBuyer);
$stmt->execute();
$profile = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$profile) {
    die('Data profil tidak ditemukan.');
}

// ------------------------------------------------------------
// Statistik ringkas
// ------------------------------------------------------------
$stats = ['total_pesanan' => 0, 'total_qty' => 0, 'total_return' => 0, 'total_sample' => 0];

$stmtO = $conn->prepare("SELECT COUNT(*) AS cnt, COALESCE(SUM(qty),0) AS qty FROM orders WHERE id_buyer = ?");
$stmtO->bind_param('i', $idBuyer);
$stmtO->execute();
$rowO = $stmtO->get_result()->fetch_assoc();
$stmtO->close();
$stats['total_pesanan'] = (int) ($rowO['cnt'] ?? 0);
$stats['total_qty']     = (float) ($rowO['qty'] ?? 0);

$stmtR = $conn->prepare("
    SELECT COUNT(*) AS cnt
    FROM order_returns r
    JOIN orders o ON o.id_order = r.id_order
    WHERE o.id_buyer = ?
");
$stmtR->bind_param('i', $idBuyer);
$stmtR->execute();
$stats['total_return'] = (int) ($stmtR->get_result()->fetch_assoc()['cnt'] ?? 0);
$stmtR->close();

$stmtS = $conn->prepare("SELECT COUNT(*) AS cnt FROM sample_requests WHERE id_buyer = ?");
$stmtS->bind_param('i', $idBuyer);
$stmtS->execute();
$stats['total_sample'] = (int) ($stmtS->get_result()->fetch_assoc()['cnt'] ?? 0);
$stmtS->close();

// ------------------------------------------------------------
// Helpers
// ------------------------------------------------------------
function profVerifBadge(string $s): string {
    $map = [
        'pending'  => ['Menunggu Verifikasi', 'badge-pending'],
        'approved' => ['Terverifikasi',       'badge-done'],
        'rejected' => ['Ditolak',             'badge-cancelled'],
        'blocked'  => ['Diblokir',            'badge-cancelled'],
    ];
    $d = $map[$s] ?? [ucfirst($s), 'badge-pending'];
    return '<span class="bp-badge ' . $d[1] . '">' . $d[0] . '</span>';
}

function profDate(?string $dt, string $fmt = 'd M Y'): string {
    if (!$dt) return '—';
    $ts = strtotime($dt);
    return $ts ? date($fmt, $ts) : $dt;
}

function profInitials(string $name): string {
    $words = preg_split('/\s+/', trim($name));
    $words = array_filter($words, fn($w) => $w !== '');
    $first = mb_substr($words[0] ?? 'P', 0, 1);
    $last  = count($words) > 1 ? mb_substr(end($words), 0, 1) : '';
    return mb_strtoupper($first . $last);
}

function profValue(?string $v): string {
    $v = trim((string) $v);
    return $v !== '' ? htmlspecialchars($v) : '<span class="bp-no-value">—</span>';
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle) ?> — Buyer Panel</title>
</head>
<body>

<?php include __DIR__ . '/partials/_header.php'; ?>
<?php include __DIR__ . '/partials/_sidebar.php'; ?>
<?php include __DIR__ . '/partials/overdue-banner.php'; ?>

<div class="bp-content">
    <main class="bp-profile">

        <!-- ===== Profile Header ===== -->
        <div class="bp-card bp-profile-header">
            <div class="bp-profile-header__main">
                <div class="bp-avatar"><?= htmlspecialchars(profInitials($profile['nama_perusahaan'])) ?></div>
                <div class="bp-profile-header__info">
                    <div class="bp-profile-header__name-row">
                        <h1><?= htmlspecialchars($profile['nama_perusahaan']) ?></h1>
                        <?= profVerifBadge($profile['status_verifikasi']) ?>
                    </div>
                    <p class="bp-profile-header__sub">
                        <?= htmlspecialchars($profile['nama_pic']) ?>
                        <?php if ($profile['kode_pelanggan']): ?>
                            · Kode Pelanggan: <span class="bp-mono"><?= htmlspecialchars($profile['kode_pelanggan']) ?></span>
                        <?php endif; ?>
                    </p>
                    <p class="bp-profile-header__meta">Bergabung sejak <?= profDate($profile['akun_dibuat']) ?></p>
                </div>
            </div>
            <a href="<?= BUYER_URL ?>/settings.php" class="bp-btn-primary">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                     stroke-linecap="round" stroke-linejoin="round" width="15" height="15">
                    <path d="M12 20h9"></path>
                    <path d="M16.5 3.5a2.12 2.12 0 0 1 3 3L7 19l-4 1 1-4Z"></path>
                </svg>
                Edit Profil
            </a>
        </div>

        <!-- ===== Stat Cards ===== -->
        <div class="bp-stat-grid">
            <div class="bp-stat-card">
                <div class="bp-stat-card__value"><?= $stats['total_pesanan'] ?></div>
                <div class="bp-stat-card__label">Total Pesanan</div>
            </div>
            <div class="bp-stat-card">
                <div class="bp-stat-card__value"><?= number_format($stats['total_qty'], 0, ',', '.') ?> Kg</div>
                <div class="bp-stat-card__label">Total Qty Dipesan</div>
            </div>
            <div class="bp-stat-card">
                <div class="bp-stat-card__value"><?= $stats['total_return'] ?></div>
                <div class="bp-stat-card__label">Pengajuan Return</div>
            </div>
            <div class="bp-stat-card">
                <div class="bp-stat-card__value"><?= $stats['total_sample'] ?></div>
                <div class="bp-stat-card__label">Permintaan Sampel</div>
            </div>
        </div>

        <!-- ===== Info Grid ===== -->
        <div class="bp-info-grid">

            <!-- Informasi Perusahaan -->
            <div class="bp-card bp-info-card">
                <h2 class="bp-info-card__title">Informasi Perusahaan</h2>
                <dl class="bp-info-list">
                    <div class="bp-info-row">
                        <dt>Nama Perusahaan</dt>
                        <dd><?= profValue($profile['nama_perusahaan']) ?></dd>
                    </div>
                    <div class="bp-info-row">
                        <dt>Nama PIC</dt>
                        <dd><?= profValue($profile['nama_pic']) ?></dd>
                    </div>
                    <div class="bp-info-row">
                        <dt>Negara</dt>
                        <dd><?= profValue($profile['negara']) ?></dd>
                    </div>
                    <div class="bp-info-row">
                        <dt>No. WhatsApp</dt>
                        <dd><?= profValue($profile['no_whatsapp']) ?></dd>
                    </div>
                    <div class="bp-info-row">
                        <dt>No. Telepon</dt>
                        <dd><?= profValue($profile['no_telp']) ?></dd>
                    </div>
                    <div class="bp-info-row">
                        <dt>Contact Person</dt>
                        <dd><?= profValue($profile['contact_person']) ?></dd>
                    </div>
                    <div class="bp-info-row bp-info-row--full">
                        <dt>Alamat</dt>
                        <dd><?= profValue($profile['alamat']) ?></dd>
                    </div>
                </dl>
            </div>

            <!-- Legalitas & Pembayaran -->
            <div class="bp-info-col">
                <div class="bp-card bp-info-card">
                    <h2 class="bp-info-card__title">Legalitas &amp; Pembayaran</h2>
                    <dl class="bp-info-list">
                        <div class="bp-info-row">
                            <dt>NPWP</dt>
                            <dd><?= profValue($profile['npwp']) ?></dd>
                        </div>
                        <div class="bp-info-row">
                            <dt>NIB</dt>
                            <dd><?= profValue($profile['nib']) ?></dd>
                        </div>
                        <div class="bp-info-row">
                            <dt>Tenor Pembayaran</dt>
                            <dd><?= (int) $profile['tenor_hari'] ?> hari</dd>
                        </div>
                        <div class="bp-info-row">
                            <dt>Dokumen Legalitas</dt>
                            <dd>
                                <?php if ($profile['upload_dokumen']): ?>
                                    <span class="bp-doc-chip">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                             stroke-linecap="round" stroke-linejoin="round" width="13" height="13">
                                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                                            <polyline points="14 2 14 8 20 8"></polyline>
                                        </svg>
                                        <?= htmlspecialchars($profile['upload_dokumen']) ?>
                                    </span>
                                <?php else: ?>
                                    <span class="bp-no-value">Belum diunggah</span>
                                <?php endif; ?>
                            </dd>
                        </div>
                    </dl>
                </div>

                <!-- Akun -->
                <div class="bp-card bp-info-card">
                    <h2 class="bp-info-card__title">Akun</h2>
                    <dl class="bp-info-list">
                        <div class="bp-info-row">
                            <dt>Email</dt>
                            <dd><?= profValue($profile['email']) ?></dd>
                        </div>
                        <div class="bp-info-row">
                            <dt>Role</dt>
                            <dd><span class="bp-role-chip"><?= htmlspecialchars(ucfirst($profile['role'])) ?></span></dd>
                        </div>
                        <div class="bp-info-row">
                            <dt>Bergabung Sejak</dt>
                            <dd><?= profDate($profile['akun_dibuat'], 'd M Y, H:i') ?></dd>
                        </div>
                    </dl>
                </div>
            </div>

        </div>

        <div class="bp-profile-footer-note">
            Ingin mengubah data di atas? Buka halaman
            <a href="<?= BUYER_URL ?>/settings.php">Pengaturan Akun</a> untuk mengedit informasi perusahaan, email, atau password.
        </div>

    </main>
</div>

<?php include __DIR__ . '/partials/_footer.php'; ?>

<style>
    body {
        margin: 0;
        font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
        background: #f9fafb;
        color: #111827;
    }

    /* ===== Layout ===== */
    .bp-profile {
        padding: 24px;
        max-width: 1080px;
        margin: 0 auto;
        display: flex;
        flex-direction: column;
        gap: 18px;
    }

    .bp-card {
        background: #fff;
        border: 1px solid #e5e7eb;
        border-radius: 14px;
    }

    /* ===== Profile header ===== */
    .bp-profile-header {
        padding: 22px 24px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 16px;
    }
    .bp-profile-header__main { display: flex; align-items: center; gap: 16px; }

    .bp-avatar {
        width: 56px;
        height: 56px;
        flex-shrink: 0;
        border-radius: 14px;
        background: linear-gradient(135deg, #2563eb, #1d4ed8);
        color: #fff;
        font-size: 19px;
        font-weight: 700;
        display: flex;
        align-items: center;
        justify-content: center;
        letter-spacing: 0.02em;
    }

    .bp-profile-header__info { display: flex; flex-direction: column; gap: 3px; }
    .bp-profile-header__name-row {
        display: flex;
        align-items: center;
        gap: 10px;
        flex-wrap: wrap;
    }
    .bp-profile-header__name-row h1 { margin: 0; font-size: 19px; font-weight: 700; }
    .bp-profile-header__sub  { margin: 0; font-size: 13.5px; color: #4b5563; }
    .bp-profile-header__meta { margin: 0; font-size: 12.5px; color: #9ca3af; }

    /* ===== Stat Cards ===== */
    .bp-stat-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 12px;
    }
    .bp-stat-card {
        background: #fff;
        border: 1px solid #e5e7eb;
        border-radius: 12px;
        padding: 16px 18px;
    }
    .bp-stat-card__value {
        font-size: 22px;
        font-weight: 800;
        color: #111827;
        line-height: 1;
        margin-bottom: 4px;
    }
    .bp-stat-card__label { font-size: 12.5px; color: #6b7280; font-weight: 500; }

    /* ===== Info grid ===== */
    .bp-info-grid {
        display: grid;
        grid-template-columns: 1.1fr 1fr;
        gap: 16px;
        align-items: start;
    }
    .bp-info-col { display: flex; flex-direction: column; gap: 16px; }

    .bp-info-card { padding: 18px 20px; }
    .bp-info-card__title {
        margin: 0 0 14px;
        font-size: 14.5px;
        font-weight: 700;
        color: #111827;
        padding-bottom: 10px;
        border-bottom: 1px solid #f3f4f6;
    }

    .bp-info-list { margin: 0; display: flex; flex-direction: column; gap: 12px; }
    .bp-info-row {
        display: grid;
        grid-template-columns: 130px 1fr;
        gap: 10px;
        align-items: baseline;
    }
    .bp-info-row--full { grid-template-columns: 1fr; gap: 4px; }
    .bp-info-row dt {
        font-size: 12.5px;
        color: #9ca3af;
        font-weight: 600;
    }
    .bp-info-row dd {
        margin: 0;
        font-size: 13.5px;
        color: #111827;
        font-weight: 500;
        line-height: 1.5;
    }
    .bp-no-value { color: #d1d5db; font-weight: 400; }

    .bp-mono {
        font-family: 'SF Mono', 'Fira Code', 'Consolas', monospace;
        font-size: 12.5px;
        font-weight: 600;
        color: #374151;
    }

    .bp-doc-chip {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 4px 10px;
        background: #f3f4f6;
        border-radius: 8px;
        font-size: 12.5px;
        font-weight: 600;
        color: #374151;
    }

    .bp-role-chip {
        display: inline-flex;
        padding: 2px 10px;
        border-radius: 999px;
        background: #eff6ff;
        color: #1d4ed8;
        font-size: 12px;
        font-weight: 600;
    }

    /* ===== Badges ===== */
    .bp-badge {
        display: inline-flex;
        align-items: center;
        font-size: 12px;
        font-weight: 600;
        padding: 4px 10px;
        border-radius: 999px;
        white-space: nowrap;
    }
    .badge-pending   { background: #fffbeb; color: #b45309; }
    .badge-done      { background: #f0fdf4; color: #15803d; }
    .badge-cancelled { background: #fef2f2; color: #b91c1c; }

    /* ===== Buttons ===== */
    .bp-btn-primary {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        padding: 10px 18px;
        background: #2563eb;
        color: #fff;
        border: none;
        border-radius: 10px;
        font-size: 13.5px;
        font-weight: 600;
        cursor: pointer;
        text-decoration: none;
        transition: background 0.15s;
        white-space: nowrap;
    }
    .bp-btn-primary:hover { background: #1d4ed8; }

    /* ===== Footer note ===== */
    .bp-profile-footer-note {
        font-size: 13px;
        color: #6b7280;
        text-align: center;
        padding: 4px 0 8px;
    }
    .bp-profile-footer-note a { color: #2563eb; font-weight: 600; text-decoration: none; }
    .bp-profile-footer-note a:hover { text-decoration: underline; }

    /* ===== Responsive ===== */
    @media (max-width: 768px) {
        .bp-profile { padding: 16px; gap: 14px; }
        .bp-profile-header { flex-direction: column; align-items: flex-start; }
        .bp-stat-grid { grid-template-columns: repeat(2, 1fr); }
        .bp-info-grid { grid-template-columns: 1fr; }
        .bp-info-row { grid-template-columns: 1fr; gap: 3px; }
    }

    @media (max-width: 480px) {
        .bp-stat-grid { grid-template-columns: repeat(2, 1fr); }
    }
</style>

</body>
</html>