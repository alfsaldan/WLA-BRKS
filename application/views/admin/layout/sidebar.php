<?php
// Deteksi URL segment ke-2 (contoh: admin/user -> segment 2 adalah 'user')
$current_menu = $this->uri->segment(2);
$current_submenu = $this->uri->segment(3);
$dashboard_active = ($current_menu == '' || $current_menu == 'dashboard') ? 'active' : '';
$user_active = ($current_menu == 'user') ? 'active' : '';
$monitoring_active = ($current_menu == 'monitoring' && $current_submenu != 'individu' && $current_submenu != 'hasil') ? 'active' : '';
$monitoring_ind_active = ($current_menu == 'monitoring' && $current_submenu == 'individu') ? 'active' : '';
$wla_hasil_active = ($current_menu == 'monitoring' && $current_submenu == 'hasil') ? 'active' : '';
?>
<!-- Desktop Sidebar (Hidden di Mobile) -->
<div class="col-md-2 d-none d-md-block d-print-none">
    <div class="card glass p-2 sticky-top" style="top: 1rem; min-height: calc(100vh - 100px);">
        <ul class="nav nav-pills flex-column w-100">
            <li class="nav-item mb-1">
                <a class="nav-link <?= $dashboard_active ?> rounded-pill" href="<?= site_url('admin') ?>"><i
                        class="bi bi-speedometer2 me-2"></i>Dashboard</a>
            </li>
            <li class="nav-item mt-3 mb-1">
                <small class="text-muted fw-bold px-3">MASTER DATA</small>
            </li>
            <li class="nav-item mb-1">
                <a class="nav-link <?= $user_active ?> rounded-pill" href="<?= site_url('admin/user') ?>"><i
                        class="bi bi-people me-2"></i>Kelola User</a>
            </li>
            <li class="nav-item mb-1">
                <a class="nav-link <?= ($current_menu == 'pegawai') ? 'active' : '' ?> rounded-pill"
                    href="<?= site_url('admin/pegawai') ?>"><i class="bi bi-person-badge me-2"></i>Data Pegawai</a>
            </li>
            <li class="nav-item mb-1">
                <a class="nav-link <?= ($current_menu == 'organisasi') ? 'active' : '' ?> rounded-pill"
                    href="<?= site_url('admin/organisasi') ?>"><i class="bi bi-diagram-3 me-2"></i>Organisasi</a>
            </li>


            <li class="nav-item mt-3 mb-1">
                <small class="text-muted fw-bold px-3">WLA (INTI SISTEM)</small>
            </li>
            <li class="nav-item mb-1">
                <a class="nav-link <?= ($current_menu == 'uraiantugas') ? 'active' : '' ?> rounded-pill"
                    href="<?= site_url('admin/uraiantugas') ?>"><i class="bi bi-card-checklist me-2"></i>Master Uraian
                    Tugas</a>
            </li>
            <li class="nav-item mb-1">
                <a class="nav-link <?= ($current_menu == 'kelolarumus') ? 'active' : '' ?> rounded-pill"
                    href="<?= site_url('admin/kelolarumus') ?>"><i class="bi bi-calculator me-2"></i>Kelola Rumus
                    WLA</a>
            </li>
            <li class="nav-item mb-1">
                <a class="nav-link <?= $monitoring_active ?> rounded-pill" href="<?= site_url('admin/monitoring') ?>"><i
                        class="bi bi-display me-2"></i>Monitoring WLA</a>
            </li>
            <li class="nav-item mb-1">
                <a class="nav-link <?= $monitoring_ind_active ?> rounded-pill"
                    href="<?= site_url('admin/monitoring/individu') ?>"><i
                        class="bi bi-person-bounding-box me-2"></i>Monitoring Individu</a>
            </li>
            <li class="nav-item mb-1">
                <a class="nav-link <?= $wla_hasil_active ?> rounded-pill"
                    href="<?= site_url('admin/monitoring/hasil') ?>"><i
                        class="bi bi-file-earmark-bar-graph me-2"></i>WLA Hasil</a>
            </li>

        </ul>
    </div>
</div>

<!-- Mobile Offcanvas Sidebar (Tampil saat tombol hamburger diklik) -->
<div class="offcanvas offcanvas-start glass border-0 shadow-sm d-print-none" tabindex="-1" id="sidebarMenu"
    aria-labelledby="sidebarMenuLabel">
    <div class="offcanvas-header border-bottom">
        <h5 class="offcanvas-title fw-bold text-primary" id="sidebarMenuLabel">Menu Navigasi</h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body p-3">
        <ul class="nav nav-pills flex-column w-100">
            <li class="nav-item mb-1">
                <a class="nav-link <?= $dashboard_active ?> rounded-pill" href="<?= site_url('admin') ?>"><i
                        class="bi bi-speedometer2 me-2"></i>Dashboard</a>
            </li>
            <li class="nav-item mt-3 mb-1">
                <small class="text-muted fw-bold px-3">MASTER DATA</small>
            </li>
            <li class="nav-item mb-1">
                <a class="nav-link <?= $user_active ?> rounded-pill" href="<?= site_url('admin/user') ?>"><i
                        class="bi bi-people me-2"></i>Kelola User</a>
            </li>
            <li class="nav-item mb-1">
                <a class="nav-link <?= ($current_menu == 'pegawai') ? 'active' : '' ?> rounded-pill"
                    href="<?= site_url('admin/pegawai') ?>"><i class="bi bi-person-badge me-2"></i>Data Pegawai</a>
            </li>
            <li class="nav-item mb-1">
                <a class="nav-link <?= ($current_menu == 'organisasi') ? 'active' : '' ?> rounded-pill"
                    href="<?= site_url('admin/organisasi') ?>"><i class="bi bi-diagram-3 me-2"></i>Organisasi</a>
            </li>

            <li class="nav-item mt-3 mb-1">
                <small class="text-muted fw-bold px-3">WLA (INTI SISTEM)</small>
            </li>
            <li class="nav-item mb-1">
                <a class="nav-link <?= ($current_menu == 'uraiantugas') ? 'active' : '' ?> rounded-pill"
                    href="<?= site_url('admin/uraiantugas') ?>"><i class="bi bi-card-checklist me-2"></i>Master Uraian
                    Tugas</a>
            </li>
            <li class="nav-item mb-1">
                <a class="nav-link <?= ($current_menu == 'kelolarumus') ? 'active' : '' ?> rounded-pill"
                    href="<?= site_url('admin/kelolarumus') ?>"><i class="bi bi-calculator me-2"></i>Kelola Rumus
                    WLA</a>
            </li>
            <li class="nav-item mb-1">
                <a class="nav-link <?= $monitoring_active ?> rounded-pill" href="<?= site_url('admin/monitoring') ?>"><i
                        class="bi bi-display me-2"></i>Monitoring WLA</a>
            </li>
            <li class="nav-item mb-1">
                <a class="nav-link <?= $monitoring_ind_active ?> rounded-pill"
                    href="<?= site_url('admin/monitoring/individu') ?>"><i
                        class="bi bi-person-bounding-box me-2"></i>Monitoring Individu</a>
            </li>
            <li class="nav-item mb-1">
                <a class="nav-link <?= $wla_hasil_active ?> rounded-pill"
                    href="<?= site_url('admin/monitoring/hasil') ?>"><i
                        class="bi bi-file-earmark-bar-graph me-2"></i>WLA Hasil</a>
            </li>

        </ul>
    </div>
</div>

<div class="col-md-10 d-flex flex-column" style="min-height: calc(100vh - 100px);">
    <div class="container-fluid">