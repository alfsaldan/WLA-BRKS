<div class="row mb-4">
  <div class="col-12 d-flex justify-content-between align-items-center">
    <div>
      <h3 class="mb-0">Kelola Organisasi</h3>
      <small class="text-muted">Master Data Induk (Pusat/Divisi/Cabang), Unit Kerja, dan Jabatan</small>
    </div>
    <button class="btn btn-outline-success shadow-sm" data-bs-toggle="modal" data-bs-target="#importOrgModal"><i class="bi bi-file-earmark-excel"></i> Import Excel Mapping</button>
  </div>
</div>

<?php if($this->session->flashdata('success')): ?>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        Swal.fire({icon: 'success', title: 'Berhasil!', text: '<?= $this->session->flashdata("success") ?>', showConfirmButton: false, timer: 2000});
    });
</script>
<?php endif; ?>

<?php if($this->session->flashdata('error')): ?>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        Swal.fire({icon: 'error', title: 'Gagal!', text: '<?= $this->session->flashdata("error") ?>', showConfirmButton: true});
    });
</script>
<?php endif; ?>

<!-- Nav Tabs -->
<ul class="nav nav-tabs mb-3" id="orgTabs" role="tablist">
  <li class="nav-item" role="presentation">
    <button class="nav-link active fw-bold" data-bs-toggle="tab" data-bs-target="#jabatan" type="button" role="tab">Jabatan</button>
  </li>
  <li class="nav-item" role="presentation">
    <button class="nav-link fw-bold" data-bs-toggle="tab" data-bs-target="#unit" type="button" role="tab">Unit Kerja</button>
  </li>
  <li class="nav-item" role="presentation">
    <button class="nav-link fw-bold" data-bs-toggle="tab" data-bs-target="#cabang" type="button" role="tab">Data Induk (Pusat/Div/Cab)</button>
  </li>
</ul>

<div class="tab-content" id="orgTabContent">
  <!-- TAB JABATAN -->
  <div class="tab-pane fade show active" id="jabatan" role="tabpanel">
    <div class="card glass p-4 shadow-sm border-0">
      <div class="d-flex justify-content-between mb-3">
          <h5 class="mb-0 text-primary">Data Jabatan</h5>
          <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addJabatanModal"><i class="bi bi-plus"></i> Tambah Jabatan</button>
      </div>
      <div class="table-responsive">
          <table class="table table-hover align-middle datatable">
              <thead>
                  <tr>
                      <th>Organisasi Induk</th>
                      <th>Kode Unit</th>
                      <th>Nama Unit</th>
                      <th>Nama Jabatan</th>
                      <th class="text-end">Aksi</th>
                  </tr>
              </thead>
              <tbody>
                  <?php foreach($jabatan as $j): ?>
                  <tr>
                      <td><?= htmlspecialchars($j->nama_cabang) ?></td>
                      <td><span class="badge bg-secondary"><?= htmlspecialchars($j->kode_unit) ?></span></td>
                      <td><?= htmlspecialchars($j->nama_unit) ?></td>
                      <td class="fw-bold"><?= htmlspecialchars($j->nama_jabatan) ?></td>
                      <td class="text-end">
                          <button class="btn btn-sm btn-outline-warning text-dark" data-bs-toggle="modal" data-bs-target="#editJabatanModal<?= $j->id_jabatan ?>" title="Edit"><i class="bi bi-pencil-square"></i></button>
                          <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                      </td>
                  </tr>
                  <?php endforeach; ?>
              </tbody>
          </table>
      </div>
    </div>
  </div>

  <!-- TAB UNIT -->
  <div class="tab-pane fade" id="unit" role="tabpanel">
    <div class="card glass p-4 shadow-sm border-0">
      <div class="d-flex justify-content-between mb-3">
          <h5 class="mb-0 text-primary">Data Unit Kerja</h5>
          <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addUnitModal"><i class="bi bi-plus"></i> Tambah Unit</button>
      </div>
      <div class="table-responsive">
          <table class="table table-hover align-middle datatable w-100">
              <thead><tr><th>Organisasi Induk</th><th>Kode Unit</th><th>Nama Unit</th><th class="text-end">Aksi</th></tr></thead>
              <tbody>
                  <?php foreach($unit as $u): ?>
                  <tr>
                      <td><?= htmlspecialchars($u->nama_cabang) ?></td>
                      <td><span class="badge bg-secondary"><?= htmlspecialchars($u->kode_unit) ?></span></td>
                      <td class="fw-bold"><?= htmlspecialchars($u->nama_unit) ?></td>
                      <td class="text-end">
                          <button class="btn btn-sm btn-outline-warning text-dark" data-bs-toggle="modal" data-bs-target="#editUnitModal<?= $u->id_unit ?>" title="Edit"><i class="bi bi-pencil-square"></i></button>
                          <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                      </td>
                  </tr>
                  <?php endforeach; ?>
              </tbody>
          </table>
      </div>
    </div>
  </div>

  <!-- TAB CABANG -->
  <div class="tab-pane fade" id="cabang" role="tabpanel">
    <div class="card glass p-4 shadow-sm border-0">
      <div class="d-flex justify-content-between mb-3">
          <h5 class="mb-0 text-primary">Data Organisasi Induk</h5>
          <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addCabangModal"><i class="bi bi-plus"></i> Tambah Induk</button>
      </div>
      <div class="table-responsive">
          <table class="table table-hover align-middle datatable w-100">
              <thead><tr><th>Kode Induk</th><th>Nama Organisasi Induk</th><th class="text-end">Aksi</th></tr></thead>
              <tbody>
                  <?php foreach($cabang as $c): ?>
                  <tr>
                      <td><span class="badge bg-primary"><?= htmlspecialchars($c->kode_cabang) ?></span></td>
                      <td class="fw-bold"><?= htmlspecialchars($c->nama_cabang) ?></td>
                      <td class="text-end">
                          <button class="btn btn-sm btn-outline-warning text-dark" data-bs-toggle="modal" data-bs-target="#editCabangModal<?= $c->id_cabang ?>" title="Edit"><i class="bi bi-pencil-square"></i></button>
                          <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                      </td>
                  </tr>
                  <?php endforeach; ?>
              </tbody>
          </table>
      </div>
    </div>
  </div>
</div>

<!-- MODAL IMPORT -->
<div class="modal fade" id="importOrgModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content glass border-0 shadow">
      <div class="modal-header border-0"><h5 class="modal-title text-success"><i class="bi bi-file-earmark-excel me-2"></i>Import Struktur Organisasi</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
      <form action="<?= site_url('admin/organisasi/import_excel') ?>" method="post" enctype="multipart/form-data">
      <div class="modal-body">
          <div class="alert alert-info small">Silakan upload file Excel mapping struktur organisasi Anda. Sistem otomatis membaca kolom A (Kode Cabang) sampai F (Jabatan) dan mendeteksi kolom kosong (seperti pada Cabang Pembantu/Divisi).</div>
          <div class="mb-3"><label class="form-label">File Excel (.xlsx)</label><input type="file" name="file_excel" class="form-control" accept=".xlsx, .xls" required></div>
      </div>
      <div class="modal-footer border-0"><button class="btn btn-success">Mulai Import</button></div>
      </form>
    </div>
  </div>
</div>

<!-- MODALS -->
<div class="modal fade" id="addCabangModal" tabindex="-1"><div class="modal-dialog"><div class="modal-content glass border-0"><div class="modal-header border-0"><h5 class="modal-title">Tambah Cabang</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div><form action="<?= site_url('admin/organisasi/store_cabang') ?>" method="post"><div class="modal-body"><div class="mb-3"><label>Kode Cabang</label><input type="text" name="kode" class="form-control" required></div><div class="mb-3"><label>Nama Cabang</label><input type="text" name="nama" class="form-control" required></div></div><div class="modal-footer border-0"><button class="btn btn-primary">Simpan</button></div></form></div></div></div>

<div class="modal fade" id="addUnitModal" tabindex="-1"><div class="modal-dialog"><div class="modal-content glass border-0"><div class="modal-header border-0"><h5 class="modal-title">Tambah Unit</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div><form action="<?= site_url('admin/organisasi/store_unit') ?>" method="post"><div class="modal-body"><div class="mb-3"><label>Pilih Cabang</label><select name="id_cabang" class="form-select" required><option value="">-- Pilih --</option><?php foreach($cabang as $c) echo "<option value='{$c->id_cabang}'>{$c->nama_cabang}</option>"; ?></select></div><div class="mb-3"><label>Kode Unit</label><input type="text" name="kode" class="form-control" required></div><div class="mb-3"><label>Nama Unit</label><input type="text" name="nama" class="form-control" required></div></div><div class="modal-footer border-0"><button class="btn btn-primary">Simpan</button></div></form></div></div></div>

<div class="modal fade" id="addJabatanModal" tabindex="-1"><div class="modal-dialog"><div class="modal-content glass border-0"><div class="modal-header border-0"><h5 class="modal-title">Tambah Jabatan</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div><form action="<?= site_url('admin/organisasi/store_jabatan') ?>" method="post"><div class="modal-body"><div class="mb-3"><label>Pilih Cabang</label><select id="selectCabangJabatan" class="form-select" required><option value="">-- Pilih Cabang Dulu --</option><?php foreach($cabang as $c) echo "<option value='{$c->id_cabang}'>{$c->nama_cabang}</option>"; ?></select></div><div class="mb-3"><label>Pilih Unit</label><select name="id_unit" id="selectUnitJabatan" class="form-select" required><option value="">-- Menunggu Cabang --</option></select></div><div class="mb-3"><label>Nama Jabatan</label><input type="text" name="nama" class="form-control" required></div></div><div class="modal-footer border-0"><button class="btn btn-primary">Simpan</button></div></form></div></div></div>

<!-- EDIT MODALS -->
<?php foreach($cabang as $c): ?>
<div class="modal fade" id="editCabangModal<?= $c->id_cabang ?>" tabindex="-1"><div class="modal-dialog"><div class="modal-content glass border-0"><div class="modal-header border-0"><h5 class="modal-title text-warning"><i class="bi bi-pencil-square me-2"></i>Edit Cabang</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div><form action="<?= site_url('admin/organisasi/update_cabang') ?>" method="post"><div class="modal-body"><input type="hidden" name="id_cabang" value="<?= $c->id_cabang ?>"><div class="mb-3"><label>Kode Cabang</label><input type="text" name="kode" class="form-control" value="<?= htmlspecialchars($c->kode_cabang) ?>" required></div><div class="mb-3"><label>Nama Cabang</label><input type="text" name="nama" class="form-control" value="<?= htmlspecialchars($c->nama_cabang) ?>" required></div></div><div class="modal-footer border-0"><button class="btn btn-primary">Simpan Perubahan</button></div></form></div></div></div>
<?php endforeach; ?>

<?php foreach($unit as $u): ?>
<div class="modal fade" id="editUnitModal<?= $u->id_unit ?>" tabindex="-1"><div class="modal-dialog"><div class="modal-content glass border-0"><div class="modal-header border-0"><h5 class="modal-title text-warning"><i class="bi bi-pencil-square me-2"></i>Edit Unit Kerja</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div><form action="<?= site_url('admin/organisasi/update_unit') ?>" method="post"><div class="modal-body"><input type="hidden" name="id_unit" value="<?= $u->id_unit ?>"><div class="mb-3"><label>Pilih Cabang Induk</label><select name="id_cabang" class="form-select" required><?php foreach($cabang as $cb) { $sel = ($cb->id_cabang == $u->id_cabang) ? 'selected' : ''; echo "<option value='{$cb->id_cabang}' {$sel}>{$cb->nama_cabang}</option>"; } ?></select></div><div class="mb-3"><label>Kode Unit</label><input type="text" name="kode" class="form-control" value="<?= htmlspecialchars($u->kode_unit) ?>" required></div><div class="mb-3"><label>Nama Unit</label><input type="text" name="nama" class="form-control" value="<?= htmlspecialchars($u->nama_unit) ?>" required></div></div><div class="modal-footer border-0"><button class="btn btn-primary">Simpan Perubahan</button></div></form></div></div></div>
<?php endforeach; ?>

<?php foreach($jabatan as $j): ?>
<div class="modal fade" id="editJabatanModal<?= $j->id_jabatan ?>" tabindex="-1"><div class="modal-dialog"><div class="modal-content glass border-0"><div class="modal-header border-0"><h5 class="modal-title text-warning"><i class="bi bi-pencil-square me-2"></i>Edit Jabatan</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div><form action="<?= site_url('admin/organisasi/update_jabatan') ?>" method="post"><div class="modal-body"><input type="hidden" name="id_jabatan" value="<?= $j->id_jabatan ?>"><div class="mb-3"><label>Pilih Unit Kerja</label><select name="id_unit" class="form-select" required><?php foreach($unit as $un) { $sel = ($un->id_unit == $j->id_unit) ? 'selected' : ''; echo "<option value='{$un->id_unit}' {$sel}>{$un->nama_cabang} - {$un->nama_unit}</option>"; } ?></select></div><div class="mb-3"><label>Nama Jabatan</label><input type="text" name="nama" class="form-control" value="<?= htmlspecialchars($j->nama_jabatan) ?>" required></div></div><div class="modal-footer border-0"><button class="btn btn-primary">Simpan Perubahan</button></div></form></div></div></div>
<?php endforeach; ?>

<link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css">
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
<script>
$(document).ready(function() {
    $('.datatable').DataTable({ language: {url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/id.json'} });
    // Relasi dropdown Cabang -> Unit
    $('#selectCabangJabatan').change(function() {
        let id = $(this).val();
        $('#selectUnitJabatan').html('<option>Loading...</option>');
        $.post('<?= site_url("admin/organisasi/ajax_get_unit") ?>', {id_cabang: id}, function(data) {
            let html = '<option value="">-- Pilih Unit --</option>';
            $.each(data, function(i, v) { html += `<option value="${v.id_unit}">${v.kode_unit} - ${v.nama_unit}</option>`; });
            $('#selectUnitJabatan').html(html);
        }, 'json');
    });
});
</script>