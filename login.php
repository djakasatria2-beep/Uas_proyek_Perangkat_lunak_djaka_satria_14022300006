<?php
// ============================================================
//  login.php — Halaman login ThreadB2B
// ============================================================
require_once __DIR__ . '/assets/config.php';

// session_start() TIDAK dipanggil lagi di sini,
// karena sudah ditangani otomatis oleh config.php
// (lihat blok "Session" di config.php)

// Sudah login → redirect ke panel sesuai role
if (isset($_SESSION['user_id'], $_SESSION['role'])) {
    $roleRedirect = [
        'admin'     => SITE_URL . '/admin_panel/dashboard.php',
        'marketing' => SITE_URL . '/marketing_panel/dashboard.php',
        'buyer'     => SITE_URL . '/buyer_panel/dashboard.php',
    ];
    $dest = $roleRedirect[$_SESSION['role']] ?? SITE_URL . '/login.php';
    header("Location: $dest");
    exit;
}

// Ambil flash message dari session (dari login-backend.php)
$flashError   = $_SESSION['login_error']   ?? '';
$flashSuccess = $_SESSION['login_success'] ?? '';
unset($_SESSION['login_error'], $_SESSION['login_success']);

// Pertahankan email yang diinput sebelumnya supaya tidak harus ketik ulang
$lastEmail = $_SESSION['login_last_email'] ?? '';
unset($_SESSION['login_last_email']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — ThreadB2B</title>

    <link rel="icon" type="image/x-icon" href="<?= SITE_URL ?>/images/favicon.ico">

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:opsz,wght@9..40,400;9..40,500;9..40,600;9..40,700;9..40,800&family=DM+Mono:wght@400;500&display=swap" rel="stylesheet">

    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <link rel="stylesheet" href="login-style.css">
</head>
<body>

<div class="login-wrap">

    <!-- ── Sisi kiri: branding ────────────────────────────── -->
    <div class="login-brand">

        <a href="<?= SITE_URL ?>/index.php" class="login-brand__logo">
            <div class="login-brand__logo-mark">T</div>
            <span class="login-brand__logo-text">Thread<strong>B2B</strong></span>
        </a>

        <div class="login-brand__body">
            <h1 class="login-brand__title">
                Platform B2B<br>
                <span>Perdagangan Benang</span><br>
                Digital
            </h1>
            <p class="login-brand__desc">
                Kelola pesanan, sampel warna, invoice, dan pengiriman benang
                PT Benang Nusantara dalam satu platform terpadu.
            </p>
            <ul class="login-brand__features">
                <li>
                    <span class="login-brand__feat-icon"><i class="bi bi-bag-check"></i></span>
                    Manajemen pesanan real-time
                </li>
                <li>
                    <span class="login-brand__feat-icon"><i class="bi bi-palette"></i></span>
                    Permintaan & approval sampel warna
                </li>
                <li>
                    <span class="login-brand__feat-icon"><i class="bi bi-receipt"></i></span>
                    Invoice & dokumen pembayaran digital
                </li>
                <li>
                    <span class="login-brand__feat-icon"><i class="bi bi-geo-alt"></i></span>
                    Tracking pengiriman dengan milestone
                </li>
            </ul>
        </div>

        <p class="login-brand__footer">
            &copy; <?= date('Y') ?> PT Benang Nusantara. All rights reserved.
        </p>

    </div>

    <!-- ── Sisi kanan: form login ─────────────────────────── -->
    <div class="login-form-side">

        <h2 class="login-form__heading">Masuk ke akun Anda</h2>
        <p class="login-form__sub">Selamat datang kembali di ThreadB2B</p>

        <!-- Flash error -->
        <?php if ($flashError): ?>
        <div class="login-alert login-alert--error" id="flashAlert">
            <i class="bi bi-exclamation-circle-fill"></i>
            <span><?= htmlspecialchars($flashError) ?></span>
        </div>
        <?php endif; ?>

        <!-- Flash success (mis. setelah reset password) -->
        <?php if ($flashSuccess): ?>
        <div class="login-alert login-alert--success" id="flashAlert">
            <i class="bi bi-check-circle-fill"></i>
            <span><?= htmlspecialchars($flashSuccess) ?></span>
        </div>
        <?php endif; ?>

        <form action="login-backend.php" method="POST" id="loginForm" novalidate>

            <!-- CSRF token -->
            <?php
            if (empty($_SESSION['csrf_token'])) {
                $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
            }
            ?>
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">

            <!-- Email -->
            <div class="lf-group">
                <label class="lf-label" for="email">Email</label>
                <div class="lf-input-wrap">
                    <input
                        type="email"
                        id="email"
                        name="email"
                        class="lf-input"
                        placeholder="nama@perusahaan.com"
                        value="<?= htmlspecialchars($lastEmail) ?>"
                        autocomplete="email"
                        required
                    >
                    <i class="bi bi-envelope lf-icon"></i>
                </div>
            </div>

            <!-- Password -->
            <div class="lf-group">
                <label class="lf-label" for="password">
                    Password
                    <a href="<?= SITE_URL ?>/forgot-password.php">Lupa password?</a>
                </label>
                <div class="lf-input-wrap">
                    <input
                        type="password"
                        id="password"
                        name="password"
                        class="lf-input lf-input--pw"
                        placeholder="Masukkan password"
                        autocomplete="current-password"
                        required
                    >
                    <i class="bi bi-lock lf-icon"></i>
                    <button type="button" class="lf-pw-toggle" id="pwToggle" aria-label="Tampilkan password">
                        <i class="bi bi-eye" id="pwToggleIcon"></i>
                    </button>
                </div>
            </div>

            <!-- Role -->
            <div class="lf-group">
                <label class="lf-label" for="role">Login sebagai</label>
                <div class="lf-input-wrap">
                    <select name="role" id="role" class="lf-select" required>
                        <option value="">— Pilih role —</option>
                        <option value="buyer">Buyer</option>
                        <option value="marketing">Marketing</option>
                        <option value="admin">Admin</option>
                    </select>
                    <i class="bi bi-person-badge lf-icon"></i>
                </div>
            </div>

            <!-- Submit -->
            <button type="submit" class="lf-btn" id="loginBtn">
                <span id="btnLabel">Masuk</span>
                <span class="spinner-border d-none" id="btnSpinner" role="status" aria-hidden="true"></span>
            </button>

        </form>

        <div class="lf-divider">atau</div>

        <div class="lf-register-link">
            Belum punya akun? <a href="<?= SITE_URL ?>/register.php">Daftar sebagai Buyer</a>
        </div>

    </div><!-- /.login-form-side -->

</div><!-- /.login-wrap -->

<script>
(function () {
    // Toggle show/hide password
    const pwInput   = document.getElementById('password');
    const pwToggle  = document.getElementById('pwToggle');
    const pwIcon    = document.getElementById('pwToggleIcon');

    pwToggle?.addEventListener('click', function () {
        const isHidden = pwInput.type === 'password';
        pwInput.type   = isHidden ? 'text' : 'password';
        pwIcon.className = isHidden ? 'bi bi-eye-slash' : 'bi bi-eye';
    });

    // Loading state saat submit
    const form    = document.getElementById('loginForm');
    const loginBtn= document.getElementById('loginBtn');
    const btnLabel= document.getElementById('btnLabel');
    const spinner = document.getElementById('btnSpinner');

    form?.addEventListener('submit', function (e) {
        const email = document.getElementById('email').value.trim();
        const pass  = document.getElementById('password').value;
        const role  = document.getElementById('role').value;

        if (!email || !pass || !role) {
            e.preventDefault();
            // Tandai field kosong
            [['email', email], ['password', pass]].forEach(([id, val]) => {
                if (!val.trim()) document.getElementById(id)?.classList.add('is-invalid');
            });
            return;
        }

        loginBtn.disabled  = true;
        btnLabel.textContent = 'Memproses...';
        spinner.classList.remove('d-none');
    });

    // Hapus is-invalid saat user mengetik
    document.querySelectorAll('.lf-input, .lf-select').forEach(el => {
        el.addEventListener('input', () => el.classList.remove('is-invalid'));
    });

    // Auto-dismiss flash alert setelah 5 detik
    const flash = document.getElementById('flashAlert');
    if (flash) {
        setTimeout(() => {
            flash.style.transition = 'opacity .4s ease';
            flash.style.opacity    = '0';
            setTimeout(() => flash.remove(), 400);
        }, 5000);
    }
})();
</script>

</body>
</html>