<div class="row mb-4">
    <div class="col-12 d-flex justify-content-between align-items-center">
        <div>
            <h3 class="mb-0 text-primary"><i class="bi bi-person-circle me-2"></i>Profil Saya</h3>
            <small class="text-muted">Kelola informasi data diri dan pengaturan keamanan akun Anda.</small>
        </div>
        <a href="<?= site_url('pegawai/dashboard') ?>" class="btn btn-outline-secondary rounded-pill shadow-sm"><i class="bi bi-arrow-left me-1"></i>Kembali</a>
    </div>
</div>

<!-- Alert Flashdata -->
<?php if($this->session->flashdata('success_pwd')): ?>
    <script> document.addEventListener("DOMContentLoaded", function() { Swal.fire({icon: 'success', title: 'Berhasil!', text: '<?= $this->session->flashdata("success_pwd") ?>', timer: 3000, showConfirmButton: false}); }); </script>
<?php endif; ?>
<?php if($this->session->flashdata('error_pwd')): ?>
    <script> document.addEventListener("DOMContentLoaded", function() { Swal.fire({icon: 'error', title: 'Gagal!', text: '<?= $this->session->flashdata("error_pwd") ?>', showConfirmButton: true}); }); </script>
<?php endif; ?>

<!-- Peringatan Password Default -->
<?php if($is_default_password): ?>
<div class="alert alert-warning border-warning border-opacity-50 shadow-sm d-flex align-items-center mb-4 rounded-4" role="alert">
    <i class="bi bi-exclamation-triangle-fill fs-2 text-warning me-3"></i>
    <div>
        <h6 class="alert-heading fw-bold mb-1">Keamanan Akun (Sangat Disarankan)</h6>
        <div class="small text-dark">Sistem mendeteksi Anda masih menggunakan password default (NIP). Demi keamanan, mohon segera <b>ganti password Anda</b> pada form di bawah.</div>
    </div>
</div>
<?php endif; ?>

<div class="row g-4">
    <!-- Bagian Kiri: Kartu Profil & Foto -->
    <div class="col-lg-4">
        <div class="card glass border-0 shadow-sm rounded-4 text-center p-4 h-100">
            <div class="position-relative d-inline-block mx-auto mb-3">
                <?php 
                $foto_url = (!empty($user->foto) && file_exists('./assets/img/profil/'.$user->foto)) 
                            ? base_url('assets/img/profil/'.$user->foto) 
                            : 'https://ui-avatars.com/api/?name='.urlencode($user->nama).'&background=0D8ABC&color=fff&size=150';
                ?>
                <img src="<?= $foto_url ?>" alt="Foto Profil" class="rounded-circle border border-4 border-white shadow-sm" style="width: 140px; height: 140px; object-fit: cover;">
                
                <button type="button" class="btn btn-primary btn-sm rounded-circle position-absolute bottom-0 end-0 shadow" data-bs-toggle="modal" data-bs-target="#modalUploadFoto" title="Ganti Foto" style="width: 38px; height: 38px;">
                    <i class="bi bi-camera-fill"></i>
                </button>
            </div>
            <h5 class="fw-bold text-dark mb-1"><?= htmlspecialchars($user->nama) ?></h5>
            <p class="text-muted mb-3"><i class="bi bi-upc-scan me-1"></i> NIP: <?= htmlspecialchars($user->nip) ?></p>
            
            <div class="text-start bg-light rounded-3 p-3 small mt-3">
                <div class="mb-2"><span class="text-muted d-block" style="font-size: 0.75rem;">Penempatan Cabang</span><span class="fw-bold"><?= htmlspecialchars($user->nama_cabang ?? 'Belum Diatur') ?></span></div>
                <div class="mb-2"><span class="text-muted d-block" style="font-size: 0.75rem;">Unit Kerja</span><span class="fw-bold"><?= htmlspecialchars($user->nama_unit ?? 'Belum Diatur') ?></span></div>
                <div><span class="text-muted d-block" style="font-size: 0.75rem;">Jabatan Aktif</span><span class="badge bg-success rounded-pill mt-1"><?= htmlspecialchars($user->nama_jabatan ?? 'Belum Diatur') ?></span></div>
            </div>
        </div>
    </div>

    <!-- Bagian Kanan: Form Ubah Password -->
    <div class="col-lg-8">
        <div class="card glass border-0 shadow-sm rounded-4 p-4 h-100">
            <h5 class="text-dark fw-bold mb-4 border-bottom pb-3"><i class="bi bi-shield-lock me-2 text-primary"></i>Ubah Password Login</h5>
            
            <form action="<?= site_url('pegawai/profil/update_password') ?>" method="POST">
                <div class="mb-4">
                    <label class="form-label text-muted small fw-bold">Password Saat Ini</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0"><i class="bi bi-key text-muted"></i></span>
                        <input type="password" name="old_password" class="form-control border-start-0 ps-0 bg-light" required placeholder="Masukkan password saat ini yang Anda gunakan">
                        <button class="btn border border-start-0 bg-light text-muted toggle-password" type="button"><i class="bi bi-eye"></i></button>
                    </div>
                </div>
                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <label class="form-label text-muted small fw-bold">Password Baru</label>
                        <div class="input-group">
                            <span class="input-group-text bg-white border-end-0"><i class="bi bi-lock text-primary"></i></span>
                            <input type="password" name="new_password" class="form-control border-start-0 ps-0" required placeholder="Buat password baru" minlength="6">
                            <button class="btn border border-start-0 text-muted toggle-password" type="button"><i class="bi bi-eye"></i></button>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-muted small fw-bold">Konfirmasi Password Baru</label>
                        <div class="input-group">
                            <span class="input-group-text bg-white border-end-0"><i class="bi bi-check2-circle text-success"></i></span>
                            <input type="password" name="confirm_password" class="form-control border-start-0 ps-0" required placeholder="Ketik ulang password baru">
                            <button class="btn border border-start-0 text-muted toggle-password" type="button"><i class="bi bi-eye"></i></button>
                        </div>
                    </div>
                </div>
                <div class="text-end mt-4 pt-3">
                    <button type="submit" class="btn btn-primary rounded-pill shadow-sm px-4 fw-bold"><i class="bi bi-save me-1"></i> Simpan Password Baru</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.querySelectorAll('.toggle-password').forEach(function(btn) {
    btn.addEventListener('click', function() {
        let input = this.previousElementSibling;
        let icon = this.querySelector('i');
        
        if (input.type === 'password') { input.type = 'text'; icon.classList.replace('bi-eye', 'bi-eye-slash'); } 
        else { input.type = 'password'; icon.classList.replace('bi-eye-slash', 'bi-eye'); }
    });
});
</script>

<!-- Modal Upload Foto -->
<div class="modal fade" id="modalUploadFoto" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-sm modal-dialog-centered">
        <div class="modal-content glass border-0 shadow">
            <div class="modal-header border-0 pb-0"><h6 class="modal-title fw-bold text-dark"><i class="bi bi-image me-2 text-primary"></i>Upload Foto Profil</h6><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <form action="<?= site_url('pegawai/profil/upload_foto') ?>" method="POST" enctype="multipart/form-data">
                <div class="modal-body text-center">
                    <input class="form-control form-control-sm border-primary" type="file" id="foto" name="foto" accept="image/png, image/jpeg, image/jpg" required>
                    <div class="form-text mt-2 small text-muted">Format: JPG/PNG. Maksimal 2MB. Rasio kotak (1:1) lebih direkomendasikan.</div>
                </div>
                <div class="modal-footer border-0 pt-0"><button type="submit" class="btn btn-primary w-100 rounded-pill shadow-sm">Upload Sekarang</button></div>
            </form>
        </div>
    </div>
</div>