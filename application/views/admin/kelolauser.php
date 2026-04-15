<div class="row mb-4">
  <div class="col-12 d-flex justify-content-between align-items-center">
    <div>
      <h3 class="mb-0">Kelola User</h3>
      <small class="text-muted">Manajemen data pengguna dan hak akses</small>
    </div>
    <button class="btn btn-primary shadow-sm" data-bs-toggle="modal" data-bs-target="#addUserModal">
      <i class="bi bi-plus-circle me-1"></i> Tambah User
    </button>
  </div>
</div>

<!-- Tampilkan Notifikasi Flashdata -->
<?php if($this->session->flashdata('success')): ?>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        Swal.fire({
            icon: 'success',
            title: 'Berhasil!',
            text: '<?= $this->session->flashdata("success") ?>',
            showConfirmButton: false,
            timer: 2000
        });
    });
</script>
<?php endif; ?>

<?php if($this->session->flashdata('error')): ?>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        Swal.fire({
            icon: 'error',
            title: 'Gagal!',
            text: '<?= $this->session->flashdata("error") ?>'
        });
    });
</script>
<?php endif; ?>

<div class="card glass p-4 shadow-sm border-0">
    <div class="table-responsive">
        <table class="table table-hover align-middle" id="userTable">
            <thead>
                <tr>
                    <th>NIP</th>
                    <th>Nama Pegawai</th>
                    <th>Role Akses</th>
                    <th>Tanggal Dibuat</th>
                    <th class="text-end">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($users as $u): ?>
                <tr>
                    <td class="fw-bold text-primary"><?= htmlspecialchars($u->nip) ?></td>
                    <td><?= htmlspecialchars($u->nama) ?></td>
                    <!-- Data sort custom agar DataTables bisa mengurutkan select dropdown dengan baik -->
                    <td data-sort="<?= $u->role ?>">
                        <?php if($u->nip == $this->session->userdata('user_nip')): ?>
                            <span class="badge bg-primary rounded-pill px-3">Admin (Anda)</span>
                        <?php else: ?>
                            <select class="form-select form-select-sm role-select shadow-sm" data-nip="<?= $u->nip ?>" style="width: 140px; cursor: pointer;">
                                <option value="admin" <?= $u->role == 'admin' ? 'selected' : '' ?>>Admin</option>
                                <option value="pegawai" <?= $u->role == 'pegawai' ? 'selected' : '' ?>>Pegawai</option>
                            </select>
                        <?php endif; ?>
                    </td>
                    <td class="text-muted small"><?= !empty($u->created_at) ? date('d M Y, H:i', strtotime($u->created_at)) : '-' ?></td>
                    <td class="text-end">
                        <button class="btn btn-sm btn-outline-warning text-dark" data-bs-toggle="modal" data-bs-target="#editUserModal<?= $u->nip ?>" title="Edit">
                            <i class="bi bi-pencil-square"></i>
                        </button>
                        <?php if($u->nip != $this->session->userdata('user_nip')): ?>
                        <a href="<?= site_url('admin/user/delete/'.$u->nip) ?>" class="btn btn-sm btn-outline-danger btn-delete" title="Hapus">
                            <i class="bi bi-trash"></i>
                        </a>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Tempatkan semua Modal di luar container tabel utama agar Z-Index / Backdrop tidak bermasalah -->
<?php foreach($users as $u): ?>
<!-- Modal Edit User -->
<div class="modal fade" id="editUserModal<?= $u->nip ?>" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content glass border-0 shadow">
      <div class="modal-header border-0">
        <h5 class="modal-title text-primary"><i class="bi bi-pencil-square me-2"></i>Edit User</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form action="<?= site_url('admin/user/update') ?>" method="post">
      <div class="modal-body">
          <input type="hidden" name="nip" value="<?= $u->nip ?>">
          <input type="hidden" name="role" value="<?= $u->role ?>">
          <div class="mb-3">
              <label class="form-label text-muted">NIP</label>
              <input type="text" class="form-control bg-light" value="<?= htmlspecialchars($u->nip) ?>" readonly>
          </div>
          <div class="mb-3">
              <label class="form-label text-muted">Nama Lengkap</label>
              <input type="text" name="nama" class="form-control" value="<?= htmlspecialchars($u->nama) ?>" required>
          </div>
          <div class="mb-3">
              <label class="form-label text-muted">Password Baru (Opsional)</label>
              <input type="password" name="password" class="form-control" placeholder="Kosongkan jika tidak ingin mengubah password" minlength="6">
          </div>
      </div>
      <div class="modal-footer border-0">
        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
        <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
      </div>
      </form>
    </div>
  </div>
</div>
<?php endforeach; ?>

<!-- Modal Tambah User -->
<div class="modal fade" id="addUserModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content glass border-0 shadow">
      <div class="modal-header border-0">
        <h5 class="modal-title text-primary"><i class="bi bi-person-plus me-2"></i>Tambah User Baru</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form action="<?= site_url('admin/user/store') ?>" method="post">
      <div class="modal-body">
          <div class="mb-3">
              <label class="form-label text-muted">NIP (6 digit angka)</label>
              <input type="text" name="nip" class="form-control" placeholder="Masukkan 6 digit angka" maxlength="6" pattern="\d{6}" required>
          </div>
          <div class="mb-3">
              <label class="form-label text-muted">Nama Lengkap</label>
              <input type="text" name="nama" class="form-control" placeholder="Nama lengkap pegawai" required>
          </div>
          <div class="mb-3">
              <label class="form-label text-muted">Role Akses</label>
              <select name="role" class="form-select" required>
                  <option value="pegawai">Pegawai</option>
                  <option value="admin">Admin MSDI</option>
              </select>
          </div>
          <div class="mb-3">
              <label class="form-label text-muted">Password</label>
              <input type="password" name="password" class="form-control" placeholder="Minimal 6 karakter" minlength="6" required>
          </div>
      </div>
      <div class="modal-footer border-0">
        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
        <button type="submit" class="btn btn-primary shadow-sm">Simpan Data</button>
      </div>
      </form>
    </div>
  </div>
</div>

<!-- DataTables Dependencies -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css">
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
<script>
    $(document).ready(function() {
        // Init DataTables (Search, Pagination, Sort diaktifkan)
        $('#userTable').DataTable({
            language: { url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/id.json' },
            ordering: true,
            pageLength: 10,
            lengthChange: false
        });

        // Konfirmasi Hapus dengan SweetAlert2
        $(document).on('click', '.btn-delete', function(e) {
            e.preventDefault();
            var url = $(this).attr('href');
            Swal.fire({
                title: 'Apakah Anda yakin?',
                text: "Data user yang dihapus tidak dapat dikembalikan!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Ya, Hapus!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = url;
                }
            });
        });

        // Fitur Ganti Role Cepat via AJAX
        $('.role-select').on('change', function() {
            var selectEl = $(this);
            var nip = selectEl.data('nip');
            var role = selectEl.val();
            
            // Efek loading ringan
            selectEl.addClass('bg-warning text-dark border-warning');

            $.post('<?= site_url("admin/user/change_role") ?>', { nip: nip, role: role }, function(res) {
                if(res.status === 'success') {
                    // Ubah jadi hijau sejenak untuk menandakan sukses disimpan
                    selectEl.removeClass('bg-warning text-dark border-warning').addClass('bg-success text-white border-success');
                    setTimeout(() => selectEl.removeClass('bg-success text-white border-success'), 1200);
                    
                    // Toast sukses
                    Swal.fire({
                        toast: true,
                        position: 'top-end',
                        icon: 'success',
                        title: res.message,
                        showConfirmButton: false,
                        timer: 2000
                    });
                } else {
                    Swal.fire('Gagal!', res.message, 'error');
                    selectEl.removeClass('bg-warning text-dark border-warning');
                }
            }, 'json').fail(function() {
                Swal.fire('Error!', 'Terjadi kesalahan koneksi saat mengubah role.', 'error');
                selectEl.removeClass('bg-warning text-dark border-warning');
            });
        });
    });
</script>