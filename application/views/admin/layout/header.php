<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>WLA - Admin</title>

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <!-- FontAwesome (optional) -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Custom CSS -->
    <link rel="stylesheet" href="<?= base_url('assets/css/wla.css') ?>">

    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body class="bg-gradient">

<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-light py-3 shadow-sm sticky-top" style="background-color: rgba(255, 255, 255, 0.65); backdrop-filter: blur(12px); z-index: 1030;">
  <div class="container-fluid">
    <!-- Sidebar Toggler (Mobile) -->
    <button class="btn btn-light d-md-none me-2" type="button" data-bs-toggle="offcanvas" data-bs-target="#sidebarMenu" aria-controls="sidebarMenu">
      <i class="bi bi-list fs-4"></i>
    </button>
    <a class="navbar-brand d-flex align-items-center" href="<?= site_url('admin') ?>">
      <div class="bg-primary bg-gradient text-white rounded p-1 me-2 d-flex align-items-center justify-content-center shadow-sm" style="width: 36px; height: 36px;">
        <i class="bi bi-bar-chart-fill fs-5"></i>
      </div>
      <span class="fw-bold text-dark">WLA <small class="text-muted d-none d-sm-inline">Bank Riau Kepri Syariah</small></span>
    </a>

    <div class="d-flex align-items-center ms-auto">
      <div class="dropdown">
        <a class="nav-link dropdown-toggle d-flex align-items-center text-dark text-decoration-none" href="#" role="button" id="profileDropdown" data-bs-toggle="dropdown" aria-expanded="false">
            <?php 
            $nip_admin = $this->session->userdata('user_nip');
            $admin_data = $this->db->select('foto')->where('nip', $nip_admin)->get('user')->row();
            $header_foto = (!empty($admin_data->foto) && file_exists('./assets/img/profil/'.$admin_data->foto)) 
                        ? base_url('assets/img/profil/'.$admin_data->foto) 
                        : 'https://ui-avatars.com/api/?name='.urlencode($this->session->userdata('user_name') ?? 'Admin').'&background=0d6efd&color=fff';
            ?>
            <img src="<?= $header_foto ?>" alt="Profil" class="rounded-circle me-2 border border-2 border-white shadow-sm" style="width: 32px; height: 32px; object-fit: cover;">
            <span class="d-none d-sm-block me-1 text-muted">Halo, <strong class="text-dark"><?= isset($user_name) ? htmlspecialchars($user_name) : 'Admin' ?></strong></span>
        </a>
        <ul class="dropdown-menu dropdown-menu-end shadow border-0 mt-2 rounded-3" aria-labelledby="profileDropdown">
          <li><a class="dropdown-item py-2" href="<?= site_url('admin/profil') ?>"><i class="bi bi-person-badge me-2 text-primary"></i>Profil Saya</a></li>
          <li><hr class="dropdown-divider my-1"></li>
          <li><a class="dropdown-item py-2 text-danger" href="<?= site_url('auth/logout') ?>"><i class="bi bi-box-arrow-right me-2"></i>Keluar Sistem</a></li>
        </ul>
      </div>
    </div>
  </div>
</nav>

<div class="container-fluid mt-3">
  <div class="row">