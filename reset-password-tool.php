<?php
// ============================================================
//  reset-password-tool.php — Tool reset password sementara
//  CARA PAKAI:
//  1. Ganti $SECRET_KEY di bawah ini dengan kata kunci rahasia kamu.
//  2. Akses file ini lewat browser dengan menambahkan ?key=KATA_KUNCI_KAMU
//     Contoh: http://localhost/threadb2b/reset-password-tool.php?key=KATA_KUNCI_KAMU
//  3. Isi form (email akun + password baru), klik "Reset Password".
//  4. SETELAH SELESAI DIGUNAKAN, HAPUS FILE INI DARI SERVER.
//     Jangan biarkan file ini tertinggal di server production.
// ============================================================

require_once __DIR__ . '/assets/config.php';

// ── Ganti kata kunci rahasia ini sebelum dipakai ──────────────
$SECRET_KEY = 'password';

// ── Validasi token akses ───────────────────────────────────────
$providedKey = $_GET['key'] ?? $_POST['key'] ?? '';
if (!hash_equals($SECRET_KEY, $providedKey)) {
    http_response_code(403);
    die('<div style="font-family:sans-serif;padding:40px;text-align:center">'
        . '<h2>403 — Akses Ditolak</h2>'
        . '<p>Token tidak valid. Tambahkan parameter <code>?key=...</code> yang benar di URL.</p>'
        . '</div>');
}

$conn    = getDB();
$message = '';
$isError = false;

// ── Proses form submit ─────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email       = trim($_POST['email'] ?? '');
    $newPassword = $_POST['new_password'] ?? '';

    if ($email === '' || $newPassword === '') {
        $message = 'Email dan password baru wajib diisi.';
        $isError = true;
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = 'Format email tidak valid.';
        $isError = true;
    } elseif (strlen($newPassword) < 6) {
        $message = 'Password baru minimal 6 karakter.';
        $isError = true;
    } else {
        // Pastikan user dengan email tersebut ada
        $stmt = $conn->prepare("SELECT id_user, role FROM users WHERE email = ? LIMIT 1");
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $user = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (!$user) {
            $message = 'Tidak ada akun dengan email tersebut.';
            $isError = true;
        } else {
            $hash = password_hash($newPassword, PASSWORD_DEFAULT);

            $stmt = $conn->prepare("UPDATE users SET password = ? WHERE id_user = ?");
            $stmt->bind_param('si', $hash, $user['id_user']);
            $stmt->execute();
            $stmt->close();

            $message = "Password untuk \"{$email}\" (role: {$user['role']}) berhasil direset. "
                      . "Jangan lupa hapus file ini dari server sekarang.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Reset Password Tool — ThreadB2B</title>
    <style>
        body { font-family: -apple-system, sans-serif; background: #f4f4f7; display: flex; align-items: center; justify-content: center; min-height: 100vh; margin: 0; }
        .box { background: #fff; padding: 32px; border-radius: 10px; box-shadow: 0 2px 12px rgba(0,0,0,.08); width: 100%; max-width: 380px; }
        h1 { font-size: 18px; margin: 0 0 4px; }
        .warn { font-size: 12px; color: #b45309; background: #fff7ed; border: 1px solid #fed7aa; padding: 8px 10px; border-radius: 6px; margin: 12px 0; }
        label { display: block; font-size: 13px; font-weight: 600; margin: 14px 0 4px; }
        input { width: 100%; padding: 9px 10px; border: 1px solid #d1d5db; border-radius: 6px; box-sizing: border-box; font-size: 14px; }
        button { width: 100%; margin-top: 18px; padding: 10px; background: #111827; color: #fff; border: none; border-radius: 6px; font-size: 14px; cursor: pointer; }
        .msg { margin-top: 14px; padding: 10px; border-radius: 6px; font-size: 13px; }
        .msg.ok  { background: #ecfdf5; color: #065f46; border: 1px solid #a7f3d0; }
        .msg.err { background: #fef2f2; color: #991b1b; border: 1px solid #fecaca; }
    </style>
</head>
<body>
    <div class="box">
        <h1>Reset Password — ThreadB2B</h1>
        <div class="warn">⚠️ Tool sementara. Hapus file ini setelah selesai dipakai.</div>

        <?php if ($message): ?>
            <div class="msg <?= $isError ? 'err' : 'ok' ?>"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>

        <form method="POST">
            <input type="hidden" name="key" value="<?= htmlspecialchars($providedKey) ?>">

            <label for="email">Email akun</label>
            <input type="email" id="email" name="email" required value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">

            <label for="new_password">Password baru</label>
            <input type="text" id="new_password" name="new_password" required minlength="6" placeholder="Minimal 6 karakter">

            <button type="submit">Reset Password</button>
        </form>
    </div>
</body>
</html>