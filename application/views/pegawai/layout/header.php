<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>WLA - Pegawai Area</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= base_url('assets/css/wla.css') ?>">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        body.bg-gradient { background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%); min-height: 100vh; display: flex; flex-direction: column; }
        .pegawai-container { max-width: 1200px; margin: 0 auto; }
        .form-control:focus { box-shadow: none; border-color: #0d6efd; }
    </style>
</head>
<body class="bg-gradient">

<nav class="navbar navbar-expand-lg navbar-light py-3 shadow-sm sticky-top" style="background-color: rgba(255, 255, 255, 0.8); backdrop-filter: blur(12px);">
  <div class="container pegawai-container">
    <a class="navbar-brand d-flex align-items-center" href="<?= site_url('pegawai/dashboard') ?>">
      <div class="bg-success bg-gradient text-white rounded p-1 me-2 d-flex align-items-center justify-content-center shadow-sm" style="width: 36px; height: 36px;">
        <i class="bi bi-person-workspace fs-5"></i>
      </div>
      <span class="fw-bold text-dark">WLA <small class="text-muted d-none d-sm-inline">Pegawai</small></span>
    </a>

    <div class="d-flex align-items-center ms-auto">
      <div class="dropdown">
        <a class="nav-link dropdown-toggle d-flex align-items-center text-dark text-decoration-none" href="#" role="button" id="profileDropdown" data-bs-toggle="dropdown" aria-expanded="false">
            <?php 
            $header_foto = (!empty($user->foto) && file_exists('./assets/img/profil/'.$user->foto)) 
                        ? base_url('assets/img/profil/'.$user->foto) 
                        : 'https://ui-avatars.com/api/?name='.urlencode($user->nama ?? 'P').'&background=198754&color=fff';
            ?>
            <img src="<?= $header_foto ?>" alt="Profil" class="rounded-circle me-2 border border-2 border-white shadow-sm" style="width: 32px; height: 32px; object-fit: cover;">
            <span class="d-none d-sm-block me-1 text-muted">Halo, <strong class="text-dark"><?= isset($user->nama) ? htmlspecialchars($user->nama) : 'Pegawai' ?></strong></span>
        </a>
        <ul class="dropdown-menu dropdown-menu-end shadow border-0 mt-2 rounded-3" aria-labelledby="profileDropdown">
          <li><a class="dropdown-item py-2" href="<?= site_url('pegawai/profil') ?>"><i class="bi bi-person-badge me-2 text-primary"></i>Profil Saya</a></li>
          <li><hr class="dropdown-divider my-1"></li>
          <li><a class="dropdown-item py-2 text-danger" href="<?= site_url('auth/logout') ?>"><i class="bi bi-box-arrow-right me-2"></i>Keluar Sistem</a></li>
        </ul>
      </div>
    </div>
  </div>
</nav>

<div class="container pegawai-container flex-grow-1 mt-4 mb-5">