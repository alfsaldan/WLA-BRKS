<div class="row mb-4">
  <div class="col-12 d-flex justify-content-between align-items-center">
    <div>
      <h3 class="mb-0">Data Pegawai</h3>
      <small class="text-muted">Kelola akun dan penempatan jabatan pegawai</small>
    </div>
    <div>
      <a href="<?= site_url('admin/pegawai/download_template') ?>" class="btn btn-outline-info shadow-sm me-1"><i class="bi bi-download"></i> Template Excel</a>
      <button class="btn btn-outline-success shadow-sm me-2" data-bs-toggle="modal" data-bs-target="#importModal"><i class="bi bi-file-earmark-excel"></i> Import Excel</button>
      <button class="btn btn-primary shadow-sm" data-bs-toggle="modal" data-bs-target="#addPegawaiModal"><i class="bi bi-person-plus"></i> Tambah Pegawai</button>
    </div>
  </div>
</div>

<?php if($this->session->flashdata('success')): ?>
<script> document.addEventListener("DOMContentLoaded", function() { Swal.fire({icon: 'success', title: 'Berhasil!', html: '<?= $this->session->flashdata("success") ?>', showConfirmButton: true}); }); </script>
<?php endif; ?>

<?php if($this->session->flashdata('warning')): ?>
<script> document.addEventListener("DOMContentLoaded", function() { Swal.fire({icon: 'warning', title: 'Import Selesai dengan Catatan', html: '<?= $this->session->flashdata("warning") ?>', showConfirmButton: true}); }); </script>
<?php endif; ?>

<?php if($this->session->flashdata('error')): ?>
<script> document.addEventListener("DOMContentLoaded", function() { Swal.fire({icon: 'error', title: 'Gagal!', text: '<?= $this->session->flashdata("error") ?>', showConfirmButton: true}); }); </script>
<?php endif; ?>

<div class="card glass p-4 shadow-sm border-0">
    <div class="table-responsive">
        <table class="table table-hover align-middle datatable">
            <thead>
                <tr>
                    <th>NIP</th>
                    <th>Nama Pegawai</th>
                    <th>Penempatan (Cabang/Unit)</th>
                    <th>Jabatan</th>
                    <th class="text-end">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($pegawai as $p): ?>
                <tr>
                    <td class="fw-bold text-primary"><?= htmlspecialchars($p->nip) ?></td>
                    <td><?= htmlspecialchars($p->nama) ?></td>
                    <td>
                        <span class="d-block small fw-bold text-dark"><?= htmlspecialchars($p->nama_cabang ?? '-') ?></span>
                        <span class="small text-muted"><?= htmlspecialchars($p->nama_unit ?? '-') ?></span>
                    </td>
                    <td><span class="badge bg-success rounded-pill px-3"><?= htmlspecialchars($p->nama_jabatan ?? '-') ?></span></td>
                    <td class="text-end">
                        <button class="btn btn-sm btn-outline-warning text-dark" data-bs-toggle="modal" data-bs-target="#editPegawaiModal<?= $p->nip ?>" title="Edit"><i class="bi bi-pencil-square"></i></button>
                        <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal Edit Pegawai -->
<?php foreach($pegawai as $p): ?>
<div class="modal fade" id="editPegawaiModal<?= $p->nip ?>" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content glass border-0 shadow">
      <div class="modal-header border-0"><h5 class="modal-title text-warning"><i class="bi bi-pencil-square me-2"></i>Edit Pegawai</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
      <form action="<?= site_url('admin/pegawai/update') ?>" method="post">
      <div class="modal-body">
          <div class="mb-3"><label class="form-label text-muted">NIP</label><input type="text" name="nip" class="form-control bg-light" value="<?= htmlspecialchars($p->nip) ?>" readonly></div>
          <div class="mb-3"><label class="form-label text-muted">Nama Lengkap</label><input type="text" name="nama" class="form-control" value="<?= htmlspecialchars($p->nama) ?>" required></div>
          <div class="mb-3"><label class="form-label text-muted">Cabang</label>
              <select name="id_cabang" class="form-select edit-cabang" data-nip="<?= $p->nip ?>" required>
                  <option value="">-- Pilih --</option>
                  <?php foreach($cabang as $c): ?>
                      <option value="<?= $c->id_cabang ?>" <?= $c->id_cabang == $p->id_cabang ? 'selected' : '' ?>><?= $c->nama_cabang ?></option>
                  <?php endforeach; ?>
              </select>
          </div>
          <div class="mb-3"><label class="form-label text-muted">Unit Kerja</label>
              <select name="id_unit" id="editUnit<?= $p->nip ?>" class="form-select edit-unit" data-nip="<?= $p->nip ?>" required>
                  <option value="">-- Pilih --</option>
                  <?php foreach($unit as $u): if($u->id_cabang == $p->id_cabang): ?>
                      <option value="<?= $u->id_unit ?>" <?= $u->id_unit == $p->id_unit ? 'selected' : '' ?>><?= $u->nama_unit ?></option>
                  <?php endif; endforeach; ?>
              </select>
          </div>
          <div class="mb-3"><label class="form-label text-muted">Jabatan</label>
              <select name="id_jabatan" id="editJabatan<?= $p->nip ?>" class="form-select" required>
                  <option value="">-- Pilih --</option>
                  <?php foreach($jabatan as $j): if($j->id_unit == $p->id_unit): ?>
                      <option value="<?= $j->id_jabatan ?>" <?= $j->id_jabatan == $p->id_jabatan ? 'selected' : '' ?>><?= $j->nama_jabatan ?></option>
                  <?php endif; endforeach; ?>
              </select>
          </div>
          <div class="mb-3"><label class="form-label text-muted">Password Baru (Opsional)</label><input type="password" name="password" class="form-control" placeholder="Kosongkan jika tidak diubah"></div>
      </div>
      <div class="modal-footer border-0"><button type="submit" class="btn btn-primary">Simpan Perubahan</button></div>
      </form>
    </div>
  </div>
</div>
<?php endforeach; ?>

<!-- Modal Tambah Pegawai -->
<div class="modal fade" id="addPegawaiModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content glass border-0 shadow">
      <div class="modal-header border-0"><h5 class="modal-title text-primary"><i class="bi bi-person-plus me-2"></i>Tambah Pegawai</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
      <form action="<?= site_url('admin/pegawai/store') ?>" method="post">
      <div class="modal-body">
          <div class="mb-3"><label class="form-label text-muted">NIP</label><input type="text" name="nip" class="form-control" maxlength="6" required></div>
          <div class="mb-3"><label class="form-label text-muted">Nama Lengkap</label><input type="text" name="nama" class="form-control" required></div>
          <div class="mb-3"><label class="form-label text-muted">Cabang</label>
              <select name="id_cabang" id="pegawaiCabang" class="form-select" required>
                  <option value="">-- Pilih --</option><?php foreach($cabang as $c) echo "<option value='{$c->id_cabang}'>{$c->nama_cabang}</option>"; ?>
              </select>
          </div>
          <div class="mb-3"><label class="form-label text-muted">Unit Kerja</label>
              <select name="id_unit" id="pegawaiUnit" class="form-select" required><option value="">-- Tunggu Cabang --</option></select>
          </div>
          <div class="mb-3"><label class="form-label text-muted">Jabatan</label>
              <select name="id_jabatan" id="pegawaiJabatan" class="form-select" required><option value="">-- Tunggu Unit --</option></select>
          </div>
          <div class="mb-3"><label class="form-label text-muted">Password Login</label><input type="password" name="password" class="form-control" required></div>
      </div>
      <div class="modal-footer border-0"><button class="btn btn-primary">Simpan</button></div>
      </form>
    </div>
  </div>
</div>

<!-- Modal Import Excel -->
<div class="modal fade" id="importModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content glass border-0 shadow">
      <div class="modal-header border-0"><h5 class="modal-title text-success"><i class="bi bi-file-earmark-excel me-2"></i>Import Data Pegawai</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
      <form action="<?= site_url('admin/pegawai/upload_excel') ?>" method="post" enctype="multipart/form-data">
      <div class="modal-body">
          <div class="alert alert-warning small">Pastikan format NIP tidak duplikat. Penulisan Cabang, Unit Kerja, dan Jabatan otomatis menyesuaikan data Master (toleransi typo ringan & huruf besar/kecil diizinkan).</div>
          <div class="mb-3"><label class="form-label">Upload File (.xlsx)</label><input type="file" name="file_excel" class="form-control" accept=".xlsx, .xls" required></div>
      </div>
      <div class="modal-footer border-0"><button class="btn btn-success">Mulai Import</button></div>
      </form>
    </div>
  </div>
</div>

<link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css">
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
<script>
$(document).ready(function() {
    $('.datatable').DataTable({ language: {url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/id.json'} });
    
    // Dependent Dropdowns (Cabang -> Unit -> Jabatan)
    $('#pegawaiCabang').change(function() {
        $.post('<?= site_url("admin/organisasi/ajax_get_unit") ?>', {id_cabang: $(this).val()}, function(d) {
            let h = '<option value="">-- Pilih Unit --</option>'; $.each(d, function(i, v) { h += `<option value="${v.id_unit}">${v.nama_unit}</option>`; });
            $('#pegawaiUnit').html(h); $('#pegawaiJabatan').html('<option value="">-- Tunggu Unit --</option>');
        }, 'json');
    });
    $('#pegawaiUnit').change(function() {
        $.post('<?= site_url("admin/organisasi/ajax_get_jabatan") ?>', {id_unit: $(this).val()}, function(d) {
            let h = '<option value="">-- Pilih Jabatan --</option>'; $.each(d, function(i, v) { h += `<option value="${v.id_jabatan}">${v.nama_jabatan}</option>`; });
            $('#pegawaiJabatan').html(h);
        }, 'json');
    });
    
    // Dependent Dropdowns untuk Edit Modal
    $('.edit-cabang').change(function() {
        let nip = $(this).data('nip');
        $('#editUnit' + nip).html('<option>Loading...</option>');
        $.post('<?= site_url("admin/organisasi/ajax_get_unit") ?>', {id_cabang: $(this).val()}, function(d) {
            let h = '<option value="">-- Pilih Unit --</option>'; $.each(d, function(i, v) { h += `<option value="${v.id_unit}">${v.nama_unit}</option>`; });
            $('#editUnit' + nip).html(h); $('#editJabatan' + nip).html('<option value="">-- Tunggu Unit --</option>');
        }, 'json');
    });
    
    $('.edit-unit').change(function() {
        let nip = $(this).data('nip');
        $('#editJabatan' + nip).html('<option>Loading...</option>');
        $.post('<?= site_url("admin/organisasi/ajax_get_jabatan") ?>', {id_unit: $(this).val()}, function(d) {
            let h = '<option value="">-- Pilih Jabatan --</option>'; $.each(d, function(i, v) { h += `<option value="${v.id_jabatan}">${v.nama_jabatan}</option>`; });
            $('#editJabatan' + nip).html(h);
        }, 'json');
    });
});
</script>