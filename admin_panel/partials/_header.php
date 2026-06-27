<?php
// ============================================================
//  admin_panel/partials/_header.php
//  Tag <head> HTML untuk semua halaman Admin.
//  Variabel yang bisa di-set sebelum include:
//    $pageTitle  — string, judul halaman (default: "Admin Panel")
//    $extraCss   — string, path CSS tambahan relatif ke admin_panel/
// ============================================================
$pageTitle = $pageTitle ?? 'Admin Panel';
?>
<!DOCTYPE html>
<html lang="id" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title><?= htmlspecialchars($pageTitle) ?> — ThreadB2B Admin</title>

    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="<?= SITE_URL ?>/images/favicon.ico">

    <!-- Google Fonts: DM Sans + DM Mono -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,300;0,9..40,400;0,9..40,500;0,9..40,600;0,9..40,700;1,9..40,400&family=DM+Mono:wght@400;500&display=swap" rel="stylesheet">

    <!-- Bootstrap 5 CDN -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">

    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <!-- Panel Admin Stylesheet -->
    <link rel="stylesheet" href="<?= SITE_URL ?>/admin_panel/style.css">

    <?php if (!empty($extraCss)): ?>
    <link rel="stylesheet" href="<?= SITE_URL ?>/admin_panel/<?= htmlspecialchars($extraCss) ?>">
    <?php endif; ?>
</head>
<body>