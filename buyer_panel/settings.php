<?php
// ============================================================
//  buyer_panel/settings.php
//  Pengaturan akun: informasi perusahaan, status verifikasi,
//  ubah email, dan ubah password milik buyer yang login.
// ============================================================

define('REQUIRED_ROLE', 'buyer');
require_once __DIR__ . '/../assets/verifyRoleRedirect.php';
require_once __DIR__ . '/partials/config.php';

$idBuyer   = (int) ($currentBuyer['id_buyer'] ?? 0);
$idUser    = (int) ($currentBuyer['id_user']  ?? 0);
$pageTitle = 'Pengaturan Akun';

$errors  = ['profile' => [], 'email' => [], 'password' => []];
$success = ['profile' => false, 'email' => false, 'password' => false];

// ------------------------------------------------------------
// Helper: ambil profil + email terkini
// ------------------------------------------------------------
function getBuyerSettings(mysqli $conn, int $idBuyer): ?array {
    $stmt = $conn->prepare("
        SELECT bp.*, u.email
        FROM buyer_profile bp
        JOIN users u ON u.id_user = bp.id_user
        WHERE bp.id_buyer = ?
    ");
    $stmt->bind_param('i', $idBuyer);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    return $row ?: null;
}

$profile = getBuyerSettings($conn, $idBuyer);
if (!$profile) {
    die('Data profil tidak ditemukan.');
}

// ------------------------------------------------------------
// Handle: Update Informasi Perusahaan
// ------------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $namaPerusahaan = trim($_POST['nama_perusahaan'] ?? '');
    $namaPic        = trim($_POST['nama_pic'] ?? '');
    $noWhatsapp     = trim($_POST['no_whatsapp'] ?? '');
    $alamat         = trim($_POST['alamat'] ?? '');
    $negara         = trim($_POST['negara'] ?? '');
    $contactPerson  = trim($_POST['contact_person'] ?? '');
    $noTelp         = trim($_POST['no_telp'] ?? '');
    $npwp           = trim($_POST['npwp'] ?? '');
    $nib            = trim($_POST['nib'] ?? '');

    if ($namaPerusahaan === '') $errors['profile'][] = 'Nama perusahaan wajib diisi.';
    if ($namaPic === '')        $errors['profile'][] = 'Nama PIC wajib diisi.';
    if ($noWhatsapp === '')     $errors['profile'][] = 'No. WhatsApp wajib diisi.';
    if ($alamat === '')         $errors['profile'][] = 'Alamat wajib diisi.';
    if ($negara === '')         $errors['profile'][] = 'Negara wajib diisi.';

    // Upload dokumen legalitas baru (opsional)
    // NOTE: sesuaikan $uploadDir & cara menyajikan file ini dengan konvensi
    // upload yang sudah dipakai di bagian lain aplikasi Anda.
    $uploadDokumen = $profile['upload_dokumen'];
    if (!empty($_FILES['upload_dokumen']['name'])) {
        $file       = $_FILES['upload_dokumen'];
        $allowedExt = ['pdf', 'jpg', 'jpeg', 'png'];
        $ext        = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

        if ($file['error'] !== UPLOAD_ERR_OK) {
            $errors['profile'][] = 'Gagal mengunggah dokumen.';
        } elseif (!in_array($ext, $allowedExt)) {
            $errors['profile'][] = 'Format dokumen harus PDF, JPG, atau PNG.';
        } elseif ($file['size'] > 2 * 1024 * 1024) {
            $errors['profile'][] = 'Ukuran dokumen maksimal 2MB.';
        } else {
            $uploadDir = __DIR__ . '/../uploads/dokumen_legalitas/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            $newName = 'doc_' . $idBuyer . '_' . time() . '.' . $ext;
            if (move_uploaded_file($file['tmp_name'], $uploadDir . $newName)) {
                $uploadDokumen = $newName;
            } else {
                $errors['profile'][] = 'Gagal menyimpan dokumen ke server.';
            }
        }
    }

    if (empty($errors['profile'])) {
        $stmt = $conn->prepare("
            UPDATE buyer_profile
            SET nama_perusahaan = ?, nama_pic = ?, no_whatsapp = ?, alamat = ?, negara = ?,
                contact_person = ?, no_telp = ?, npwp = ?, nib = ?, upload_dokumen = ?
            WHERE id_buyer = ?
        ");
        $stmt->bind_param(
            'ssssssssssi',
            $namaPerusahaan, $namaPic, $noWhatsapp, $alamat, $negara,
            $contactPerson, $noTelp, $npwp, $nib, $uploadDokumen, $idBuyer
        );
        $stmt->execute();
        $stmt->close();

        $success['profile'] = true;
        $profile = getBuyerSettings($conn, $idBuyer);
    }
}

// ------------------------------------------------------------
// Handle: Ubah Email
// ------------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_email'])) {
    $newEmail = trim($_POST['email'] ?? '');
    $currPass = $_POST['current_password_email'] ?? '';

    if ($newEmail === '' || !filter_var($newEmail, FILTER_VALIDATE_EMAIL)) {
        $errors['email'][] = 'Format email tidak valid.';
    }
    if ($currPass === '') {
        $errors['email'][] = 'Masukkan password Anda untuk konfirmasi.';
    }

    if (empty($errors['email'])) {
        $stmtU = $conn->prepare("SELECT password FROM users WHERE id_user = ?");
        $stmtU->bind_param('i', $idUser);
        $stmtU->execute();
        $userRow = $stmtU->get_result()->fetch_assoc();
        $stmtU->close();

        if (!$userRow || !password_verify($currPass, $userRow['password'])) {
            $errors['email'][] = 'Password yang Anda masukkan salah.';
        } elseif ($newEmail === $profile['email']) {
            $errors['email'][] = 'Email baru sama dengan email saat ini.';
        } else {
            $stmtChk = $conn->prepare("SELECT id_user FROM users WHERE email = ? AND id_user != ?");
            $stmtChk->bind_param('si', $newEmail, $idUser);
            $stmtChk->execute();
            $dupe = $stmtChk->get_result()->fetch_assoc();
            $stmtChk->close();

            if ($dupe) {
                $errors['email'][] = 'Email tersebut sudah digunakan akun lain.';
            } else {
                $stmtUp = $conn->prepare("UPDATE users SET email = ? WHERE id_user = ?");
                $stmtUp->bind_param('si', $newEmail, $idUser);
                $stmtUp->execute();
                $stmtUp->close();

                $success['email'] = true;
                $profile = getBuyerSettings($conn, $idBuyer);
            }
        }
    }
}

// ------------------------------------------------------------
// Handle: Ubah Password
// ------------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_password'])) {
    $currPass = $_POST['current_password'] ?? '';
    $newPass  = $_POST['new_password'] ?? '';
    $confPass = $_POST['confirm_password'] ?? '';

    if ($currPass === '' || $newPass === '' || $confPass === '') {
        $errors['password'][] = 'Semua field password wajib diisi.';
    } elseif (strlen($newPass) < 8) {
        $errors['password'][] = 'Password baru minimal 8 karakter.';
    } elseif ($newPass !== $confPass) {
        $errors['password'][] = 'Konfirmasi password baru tidak cocok.';
    }

    if (empty($errors['password'])) {
        $stmtU = $conn->prepare("SELECT password FROM users WHERE id_user = ?");
        $stmtU->bind_param('i', $idUser);
        $stmtU->execute();
        $userRow = $stmtU->get_result()->fetch_assoc();
        $stmtU->close();

        if (!$userRow || !password_verify($currPass, $userRow['password'])) {
            $errors['password'][] = 'Password lama yang Anda masukkan salah.';
        } else {
            $newHash = password_hash($newPass, PASSWORD_BCRYPT);
            $stmtUp  = $conn->prepare("UPDATE users SET password = ? WHERE id_user = ?");
            $stmtUp->bind_param('si', $newHash, $idUser);
            $stmtUp->execute();
            $stmtUp->close();

            $success['password'] = true;
        }
    }
}

// ------------------------------------------------------------
// Helpers
// ------------------------------------------------------------
function setVerifBadge(string $s): string {
    $map = [
        'pending'  => ['Menunggu Verifikasi', 'badge-pending'],
        'approved' => ['Terverifikasi',       'badge-done'],
        'rejected' => ['Ditolak',             'badge-cancelled'],
        'blocked'  => ['Diblokir',            'badge-cancelled'],
    ];
    $d = $map[$s] ?? [ucfirst($s), 'badge-pending'];
    return '<span class="bp-badge ' . $d[1] . '">' . $d[0] . '</span>';
}

function setDate(?string $dt): string {
    if (!$dt) return '-';
    $ts = strtotime($dt);
    return $ts ? date('d M Y, H:i', $ts) : $dt;
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
    <main class="bp-settings">

        <!-- ===== Heading ===== -->
        <div class="bp-settings__heading">
            <div>
                <h1>Pengaturan Akun</h1>
                <p>Kelola informasi perusahaan dan keamanan akun Anda.</p>
            </div>
        </div>

        <!-- ===== Status Verifikasi ===== -->
        <div class="bp-card bp-status-card">
            <div class="bp-status-card__row">
                <div class="bp-status-card__item">
                    <span class="bp-status-card__label">Kode Pelanggan</span>
                    <span class="bp-mono"><?= htmlspecialchars($profile['kode_pelanggan'] ?: '—') ?></span>
                </div>
                <div class="bp-status-card__item">
                    <span class="bp-status-card__label">Status Verifikasi</span>
                    <?= setVerifBadge($profile['status_verifikasi']) ?>
                </div>
                <div class="bp-status-card__item">
                    <span class="bp-status-card__label">Tenor Pembayaran</span>
                    <span class="bp-status-card__value"><?= (int) $profile['tenor_hari'] ?> hari</span>
                </div>
            </div>

            <?php if ($profile['status_verifikasi'] === 'pending'): ?>
                <div class="bp-alert bp-alert--info">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                         stroke-linecap="round" stroke-linejoin="round" width="18" height="18">
                        <circle cx="12" cy="12" r="10"></circle>
                        <line x1="12" y1="16" x2="12" y2="12"></line>
                        <line x1="12" y1="8" x2="12.01" y2="8"></line>
                    </svg>
                    <span>Akun Anda masih menunggu verifikasi dari tim kami. Beberapa fitur mungkin terbatas sampai status disetujui.</span>
                </div>
            <?php elseif ($profile['status_verifikasi'] === 'rejected'): ?>
                <div class="bp-alert bp-alert--danger">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                         stroke-linecap="round" stroke-linejoin="round" width="18" height="18">
                        <circle cx="12" cy="12" r="10"></circle>
                        <line x1="15" y1="9" x2="9" y2="15"></line>
                        <line x1="9" y1="9" x2="15" y2="15"></line>
                    </svg>
                    <span>Verifikasi akun Anda ditolak. Lengkapi kembali data &amp; dokumen di bawah, lalu hubungi tim kami.</span>
                </div>
            <?php elseif ($profile['status_verifikasi'] === 'blocked'): ?>
                <div class="bp-alert bp-alert--danger">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                         stroke-linecap="round" stroke-linejoin="round" width="18" height="18">
                        <circle cx="12" cy="12" r="10"></circle>
                        <line x1="4.93" y1="4.93" x2="19.07" y2="19.07"></line>
                    </svg>
                    <span>Akun Anda diblokir sejak <?= setDate($profile['tanggal_diblokir']) ?>. Silakan hubungi admin untuk informasi lebih lanjut.</span>
                </div>
            <?php endif; ?>
        </div>

        <!-- ===== Informasi Perusahaan ===== -->
        <div class="bp-card bp-form-card">
            <div class="bp-form-card__head">
                <h2>Informasi Perusahaan</h2>
                <p>Data ini digunakan untuk proses verifikasi, invoice, dan pengiriman.</p>
            </div>

            <?php if ($success['profile']): ?>
                <div class="bp-alert bp-alert--success">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                         stroke-linecap="round" stroke-linejoin="round" width="18" height="18">
                        <polyline points="20 6 9 17 4 12"></polyline>
                    </svg>
                    <span>Informasi perusahaan berhasil diperbarui.</span>
                </div>
            <?php endif; ?>
            <?php if (!empty($errors['profile'])): ?>
                <div class="bp-alert bp-alert--danger">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                         stroke-linecap="round" stroke-linejoin="round" width="18" height="18">
                        <circle cx="12" cy="12" r="10"></circle>
                        <line x1="12" y1="8" x2="12" y2="12"></line>
                        <line x1="12" y1="16" x2="12.01" y2="16"></line>
                    </svg>
                    <ul class="bp-alert__list">
                        <?php foreach ($errors['profile'] as $e): ?>
                            <li><?= htmlspecialchars($e) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <form method="POST" action="" enctype="multipart/form-data" class="bp-form">
                <div class="bp-form-grid">
                    <div class="bp-field">
                        <label for="nama_perusahaan">Nama Perusahaan <span class="req">*</span></label>
                        <input type="text" id="nama_perusahaan" name="nama_perusahaan" required
                               value="<?= htmlspecialchars($profile['nama_perusahaan']) ?>">
                    </div>
                    <div class="bp-field">
                        <label for="nama_pic">Nama PIC <span class="req">*</span></label>
                        <input type="text" id="nama_pic" name="nama_pic" required
                               value="<?= htmlspecialchars($profile['nama_pic']) ?>">
                    </div>
                    <div class="bp-field">
                        <label for="no_whatsapp">No. WhatsApp <span class="req">*</span></label>
                        <input type="text" id="no_whatsapp" name="no_whatsapp" required
                               value="<?= htmlspecialchars($profile['no_whatsapp']) ?>">
                    </div>
                    <div class="bp-field">
                        <label for="negara">Negara <span class="req">*</span></label>
                        <input type="text" id="negara" name="negara" required
                               value="<?= htmlspecialchars($profile['negara']) ?>">
                    </div>
                    <div class="bp-field">
                        <label for="contact_person">Contact Person</label>
                        <input type="text" id="contact_person" name="contact_person"
                               value="<?= htmlspecialchars($profile['contact_person'] ?? '') ?>">
                    </div>
                    <div class="bp-field">
                        <label for="no_telp">No. Telepon</label>
                        <input type="text" id="no_telp" name="no_telp"
                               value="<?= htmlspecialchars($profile['no_telp'] ?? '') ?>">
                    </div>
                    <div class="bp-field">
                        <label for="npwp">NPWP</label>
                        <input type="text" id="npwp" name="npwp"
                               value="<?= htmlspecialchars($profile['npwp'] ?? '') ?>">
                    </div>
                    <div class="bp-field">
                        <label for="nib">NIB</label>
                        <input type="text" id="nib" name="nib"
                               value="<?= htmlspecialchars($profile['nib'] ?? '') ?>">
                    </div>
                    <div class="bp-field bp-field--full">
                        <label for="alamat">Alamat <span class="req">*</span></label>
                        <textarea id="alamat" name="alamat" rows="3" required><?= htmlspecialchars($profile['alamat']) ?></textarea>
                    </div>
                    <div class="bp-field bp-field--full">
                        <label for="upload_dokumen">Dokumen Legalitas (NPWP/NIB)</label>
                        <input type="file" id="upload_dokumen" name="upload_dokumen" accept=".pdf,.jpg,.jpeg,.png">
                        <span class="bp-field__hint">
                            <?php if ($profile['upload_dokumen']): ?>
                                Dokumen saat ini: <strong><?= htmlspecialchars($profile['upload_dokumen']) ?></strong> ·
                            <?php endif; ?>
                            PDF/JPG/PNG, maks. 2MB. Kosongkan jika tidak ingin mengganti.
                        </span>
                    </div>
                </div>

                <div class="bp-form__actions">
                    <button type="submit" name="update_profile" value="1" class="bp-btn-primary">Simpan Perubahan</button>
                </div>
            </form>
        </div>

        <!-- ===== Keamanan Akun ===== -->
        <div class="bp-security-grid">

            <!-- Ubah Email -->
            <div class="bp-card bp-form-card">
                <div class="bp-form-card__head">
                    <h2>Ubah Email</h2>
                    <p>Email saat ini: <strong><?= htmlspecialchars($profile['email']) ?></strong></p>
                </div>

                <?php if ($success['email']): ?>
                    <div class="bp-alert bp-alert--success">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                             stroke-linecap="round" stroke-linejoin="round" width="18" height="18">
                            <polyline points="20 6 9 17 4 12"></polyline>
                        </svg>
                        <span>Email berhasil diperbarui.</span>
                    </div>
                <?php endif; ?>
                <?php if (!empty($errors['email'])): ?>
                    <div class="bp-alert bp-alert--danger">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                             stroke-linecap="round" stroke-linejoin="round" width="18" height="18">
                            <circle cx="12" cy="12" r="10"></circle>
                            <line x1="12" y1="8" x2="12" y2="12"></line>
                            <line x1="12" y1="16" x2="12.01" y2="16"></line>
                        </svg>
                        <ul class="bp-alert__list">
                            <?php foreach ($errors['email'] as $e): ?>
                                <li><?= htmlspecialchars($e) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <form method="POST" action="" class="bp-form">
                    <div class="bp-field">
                        <label for="email">Email Baru</label>
                        <input type="email" id="email" name="email" required
                               placeholder="email-baru@perusahaan.com">
                    </div>
                    <div class="bp-field">
                        <label for="current_password_email">Password Saat Ini</label>
                        <input type="password" id="current_password_email" name="current_password_email" required>
                    </div>
                    <div class="bp-form__actions">
                        <button type="submit" name="update_email" value="1" class="bp-btn-primary">Ubah Email</button>
                    </div>
                </form>
            </div>

            <!-- Ubah Password -->
            <div class="bp-card bp-form-card">
                <div class="bp-form-card__head">
                    <h2>Ubah Password</h2>
                    <p>Gunakan password yang kuat dan tidak digunakan di tempat lain.</p>
                </div>

                <?php if ($success['password']): ?>
                    <div class="bp-alert bp-alert--success">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                             stroke-linecap="round" stroke-linejoin="round" width="18" height="18">
                            <polyline points="20 6 9 17 4 12"></polyline>
                        </svg>
                        <span>Password berhasil diperbarui.</span>
                    </div>
                <?php endif; ?>
                <?php if (!empty($errors['password'])): ?>
                    <div class="bp-alert bp-alert--danger">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                             stroke-linecap="round" stroke-linejoin="round" width="18" height="18">
                            <circle cx="12" cy="12" r="10"></circle>
                            <line x1="12" y1="8" x2="12" y2="12"></line>
                            <line x1="12" y1="16" x2="12.01" y2="16"></line>
                        </svg>
                        <ul class="bp-alert__list">
                            <?php foreach ($errors['password'] as $e): ?>
                                <li><?= htmlspecialchars($e) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <form method="POST" action="" class="bp-form">
                    <div class="bp-field">
                        <label for="current_password">Password Lama</label>
                        <input type="password" id="current_password" name="current_password" required>
                    </div>
                    <div class="bp-field">
                        <label for="new_password">Password Baru</label>
                        <input type="password" id="new_password" name="new_password" required minlength="8">
                    </div>
                    <div class="bp-field">
                        <label for="confirm_password">Konfirmasi Password Baru</label>
                        <input type="password" id="confirm_password" name="confirm_password" required minlength="8">
                    </div>
                    <div class="bp-form__actions">
                        <button type="submit" name="update_password" value="1" class="bp-btn-primary">Ubah Password</button>
                    </div>
                </form>
            </div>

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
    .bp-settings {
        padding: 24px;
        max-width: 1080px;
        margin: 0 auto;
        display: flex;
        flex-direction: column;
        gap: 18px;
    }

    .bp-settings__heading h1 { margin: 0 0 4px; font-size: 22px; font-weight: 700; }
    .bp-settings__heading p  { margin: 0; font-size: 13.5px; color: #6b7280; }

    /* ===== Card base ===== */
    .bp-card {
        background: #fff;
        border: 1px solid #e5e7eb;
        border-radius: 14px;
    }

    /* ===== Status card ===== */
    .bp-status-card { padding: 18px 20px; display: flex; flex-direction: column; gap: 14px; }
    .bp-status-card__row {
        display: flex;
        align-items: center;
        gap: 28px;
        flex-wrap: wrap;
    }
    .bp-status-card__item { display: flex; flex-direction: column; gap: 5px; }
    .bp-status-card__label {
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        color: #9ca3af;
    }
    .bp-status-card__value { font-size: 14px; font-weight: 700; color: #111827; }

    /* ===== Alerts ===== */
    .bp-alert {
        display: flex;
        align-items: flex-start;
        gap: 10px;
        padding: 12px 16px;
        border-radius: 10px;
        font-size: 13.5px;
        line-height: 1.5;
    }
    .bp-alert--success { background: #f0fdf4; color: #166534; }
    .bp-alert--danger  { background: #fef2f2; color: #b91c1c; }
    .bp-alert--info    { background: #eff6ff; color: #1d4ed8; }
    .bp-alert svg { flex-shrink: 0; margin-top: 1px; }
    .bp-alert__list { margin: 0; padding-left: 18px; }
    .bp-alert__list li { margin-bottom: 2px; }

    /* ===== Form card ===== */
    .bp-form-card { padding: 20px 22px; display: flex; flex-direction: column; gap: 16px; }
    .bp-form-card__head h2 { margin: 0 0 3px; font-size: 16px; font-weight: 700; }
    .bp-form-card__head p  { margin: 0; font-size: 13px; color: #6b7280; }

    .bp-form { display: flex; flex-direction: column; gap: 16px; }
    .bp-form-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 14px 16px;
    }
    .bp-field { display: flex; flex-direction: column; gap: 6px; }
    .bp-field--full { grid-column: 1 / -1; }
    .bp-field label { font-size: 12.5px; font-weight: 600; color: #374151; }
    .bp-field label .req { color: #dc2626; }

    .bp-field input[type="text"],
    .bp-field input[type="email"],
    .bp-field input[type="password"],
    .bp-field textarea {
        padding: 9px 12px;
        border: 1px solid #d1d5db;
        border-radius: 9px;
        font-size: 13.5px;
        color: #111827;
        background: #fff;
        outline: none;
        font-family: inherit;
        transition: border-color 0.15s;
        resize: vertical;
    }
    .bp-field input:focus,
    .bp-field textarea:focus {
        border-color: #2563eb;
        box-shadow: 0 0 0 3px rgba(37,99,235,.1);
    }
    .bp-field input[type="file"] {
        font-size: 13px;
        padding: 6px 0;
        font-family: inherit;
    }
    .bp-field__hint { font-size: 12px; color: #9ca3af; line-height: 1.5; }

    .bp-form__actions { display: flex; justify-content: flex-end; }

    /* ===== Security grid ===== */
    .bp-security-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 16px;
    }

    /* ===== Buttons ===== */
    .bp-btn-primary {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        padding: 10px 20px;
        background: #2563eb;
        color: #fff;
        border: none;
        border-radius: 10px;
        font-size: 13.5px;
        font-weight: 600;
        cursor: pointer;
        transition: background 0.15s;
        white-space: nowrap;
        font-family: inherit;
    }
    .bp-btn-primary:hover { background: #1d4ed8; }

    /* ===== Shared helpers ===== */
    .bp-mono {
        font-family: 'SF Mono', 'Fira Code', 'Consolas', monospace;
        font-size: 13px;
        font-weight: 700;
        color: #374151;
    }

    .bp-badge {
        display: inline-flex;
        align-items: center;
        font-size: 12px;
        font-weight: 600;
        padding: 4px 10px;
        border-radius: 999px;
        white-space: nowrap;
        width: fit-content;
    }
    .badge-pending    { background: #fffbeb; color: #b45309; }
    .badge-done       { background: #f0fdf4; color: #15803d; }
    .badge-cancelled  { background: #fef2f2; color: #b91c1c; }

    /* ===== Responsive ===== */
    @media (max-width: 768px) {
        .bp-settings { padding: 16px; gap: 14px; }
        .bp-status-card__row { gap: 18px; }
        .bp-form-grid { grid-template-columns: 1fr; }
        .bp-security-grid { grid-template-columns: 1fr; }
        .bp-form-card { padding: 16px; }
    }
</style>

</body>
</html>