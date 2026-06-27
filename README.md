# ThreadB2B — PT Benang Nusantara
Platform B2B textile trading untuk pemesanan benang.

## Stack
- PHP 8.x + MySQL (XAMPP)
- Bootstrap 5 (lokal per panel)
- Vanilla JS (fetch/AJAX)
- PHPMailer (notifikasi email)

## Role
- Buyer      -> buyer_panel/
- Marketing  -> marketing_panel/
- Admin      -> admin_panel/
- Publik     -> root (index.php, about.php)

## Setup
1. Extract ke htdocs/threadb2b/
2. Import database/_threadb2b.sql ke phpMyAdmin
3. Copy .env.example ke .env, isi kredensial DB dan SMTP
4. Akses via http://localhost/threadb2b/
