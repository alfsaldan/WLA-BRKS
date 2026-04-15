# WLA (Work Load Analysis) - CodeIgniter 3 Skeleton

Ini adalah skeleton awal project WLA (Work Load Analysis) menggunakan CodeIgniter 3 dengan struktur MVC yang rapi.

Quick start:

1. Copy project ke webroot (sudah di root ini).
2. Edit `application/config/database.php` sesuaikan koneksi database (database, username, password).
3. Buka browser: `http://your-host/wla/setup` untuk membuat tabel `user` dan membuat admin awal (NIP=123456, password=Admin@123).
   - Setelah setup sukses, HAPUS atau nonaktifkan controller `Setup` (`application/controllers/Setup.php`) untuk keamanan.
4. Buka `http://your-host/wla/admin/login` untuk melakukan login.

Fitur yang dibuat di initial commit:
- Login admin (controllers/Admin/Login.php)
- Dashboard skeleton (controllers/Admin/Dashboard.php)
- User model (application/models/User_model.php)
- Views: `application/views/admin/layout/*`, `application/views/admin/login.php`, `application/views/admin/dashboard.php`
- Assets: `assets/css/wla.css`, `assets/img/wla-logo.svg`
- Route entries added in `application/config/routes.php` for admin and setup

Security notes:
- Setup controller is a convenience to bootstrap the DB and should be removed after use.

Design notes:
- Uses Bootstrap 5, Bootstrap Icons, and glassmorphism custom CSS.
