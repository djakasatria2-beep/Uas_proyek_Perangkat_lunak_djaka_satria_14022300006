<?php
// ============================================================
//  ThreadB2B — assets/updateProfile.php
//  Update data profil pengguna yang sedang login.
//  Body (JSON):
//    Buyer     : nama_pic, no_whatsapp, no_telp, alamat, contact_person
//    Marketing / Admin : nama (via users.email domain — pakai nama_pic field)
//  Semua field opsional — hanya yang dikirim yang diupdate.
// ============================================================

session_start();
include __DIR__ . '/config.php';
include __DIR__ . '/noSessionRedirect.php';
header('Content-Type: application/json; charset=utf-8');

requireMethod('POST');

$role   = $_SESSION['role'];
$idUser = (int)$_SESSION['user_id'];

$body = getJsonBody();

if ($role === 'buyer') {
    $idBuyer = (int)($_SESSION['id_buyer'] ?? 0);

    $allowed = ['nama_pic', 'no_whatsapp', 'no_telp', 'alamat', 'contact_person'];
    $setClauses = [];
    $params     = [];
    $types      = '';

    foreach ($allowed as $field) {
        if (isset($body[$field])) {
            $val = trim($body[$field]);
            if (in_array($field, ['nama_pic', 'no_whatsapp']) && $val === '') {
                respond('error', "$field tidak boleh kosong.");
            }
            $setClauses[] = "$field = ?";
            $params[]     = $val;
            $types       .= 's';
        }
    }

    if (empty($setClauses)) respond('error', 'Tidak ada data yang diubah.');

    $params[] = $idBuyer;
    $types   .= 'i';

    $stmt = mysqli_prepare($conn,
        'UPDATE buyer_profile SET ' . implode(', ', $setClauses) . ' WHERE id_buyer = ?');
    mysqli_stmt_bind_param($stmt, $types, ...$params);

    if (!mysqli_stmt_execute($stmt)) {
        respond('error', 'Gagal memperbarui profil: ' . mysqli_error($conn));
    }

    respond('success', 'Profil berhasil diperbarui.');
}

// Marketing / Admin — hanya bisa update email (username) di tabel users
if (isset($body['email'])) {
    $email = trim($body['email']);
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        respond('error', 'Format email tidak valid.');
    }

    // Cek apakah email sudah dipakai user lain
    $dup = mysqli_prepare($conn,
        "SELECT id_user FROM users WHERE email = ? AND id_user <> ?");
    mysqli_stmt_bind_param($dup, 'si', $email, $idUser);
    mysqli_stmt_execute($dup);
    if (mysqli_fetch_assoc(mysqli_stmt_get_result($dup))) {
        respond('error', 'Email sudah digunakan oleh akun lain.');
    }

    $stmt = mysqli_prepare($conn,
        "UPDATE users SET email = ? WHERE id_user = ?");
    mysqli_stmt_bind_param($stmt, 'si', $email, $idUser);

    if (!mysqli_stmt_execute($stmt)) {
        respond('error', 'Gagal memperbarui email: ' . mysqli_error($conn));
    }

    respond('success', 'Profil berhasil diperbarui.', ['email' => $email]);
}

respond('error', 'Tidak ada data yang diubah.');
