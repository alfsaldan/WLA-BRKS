<div class="row mb-4">
  <div class="col-12 d-flex justify-content-between align-items-center">
    <div>
      <h3 class="mb-0 text-primary"><i class="bi bi-card-checklist me-2"></i>Master Uraian Tugas</h3>
      <small class="text-muted">Kelola uraian tugas per jabatan untuk keperluan WLA</small>
    </div>
  </div>
</div>

<?php if($this->session->flashdata('success')): ?>
<script> document.addEventListener("DOMContentLoaded", function() { Swal.fire({icon: 'success', title: 'Berhasil!', html: '<?= $this->session->flashdata("success") ?>', timer: 2500, showConfirmButton: false}); }); </script>
<?php endif; ?>

<!-- Filter Form -->
<form method="GET" action="<?= site_url('admin/uraiantugas') ?>" class="card glass p-3 mb-4 border-0 shadow-sm">
    <div class="row g-3 align-items-end">
        <div class="col-md-2">
            <label class="form-label text-muted small">Tahun</label>
            <input type="number" name="tahun" class="form-control" value="<?= $filter_tahun ?>" required>
        </div>
        <div class="col-md-2">
            <label class="form-label text-muted small">Bulan</label>
            <select name="bulan" class="form-select" required>
                <?php $bulan_indo = ['', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
                for($i=1; $i<=12; $i++): $m = str_pad($i, 2, '0', STR_PAD_LEFT); ?>
                    <option value="<?= $m ?>" <?= $m == $filter_bulan ? 'selected' : '' ?>><?= $bulan_indo[$i] ?></option>
                <?php endfor; ?>
            </select>
        </div>
        <div class="col-md-3">
            <label class="form-label text-muted small">Unit Kantor</label>
            <select name="id_cabang" id="filterCabang" class="form-select" required>
                <option value="">-- Pilih --</option>
                <?php foreach($cabang as $c): ?>
                    <option value="<?= $c->id_cabang ?>" <?= $c->id_cabang == $filter_cabang ? 'selected' : '' ?>><?= $c->nama_cabang ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-2">
            <label class="form-label text-muted small">Jenis Unit</label>
            <select name="id_unit" id="filterUnit" class="form-select" required>
                <option value="">-- Pilih --</option>
                <?php foreach($unit as $u): if($u->id_cabang == $filter_cabang): ?>
                    <option value="<?= $u->id_unit ?>" <?= $u->id_unit == $filter_unit ? 'selected' : '' ?>><?= $u->nama_unit ?></option>
                <?php endif; endforeach; ?>
            </select>
        </div>
        <div class="col-md-2">
            <label class="form-label text-muted small">Jabatan</label>
            <select name="id_jabatan" id="filterJabatan" class="form-select" required>
                <option value="">-- Pilih --</option>
                <?php foreach($jabatan as $j): if($j->id_unit == $filter_unit): ?>
                    <option value="<?= $j->id_jabatan ?>" <?= $j->id_jabatan == $filter_jabatan ? 'selected' : '' ?>><?= $j->nama_jabatan ?></option>
                <?php endif; endforeach; ?>
            </select>
        </div>
        <div class="col-md-1">
            <button type="submit" class="btn btn-primary w-100"><i class="bi bi-search"></i></button>
        </div>
    </div>
</form>

<?php if(!empty($filter_jabatan)): ?>
<div class="card glass p-4 shadow-sm border-0">
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
        <h5 class="text-dark mb-0">Daftar Uraian Tugas <small class="text-muted">(<?= $bulan_indo[(int)$filter_bulan] ?> <?= $filter_tahun ?>)</small></h5>
        <div>
            <form action="<?= site_url('admin/uraiantugas/sync_months') ?>" method="POST" class="d-inline" id="formSyncMonths">
                <input type="hidden" name="tahun" value="<?= $filter_tahun ?>">
                <input type="hidden" name="bulan" value="<?= $filter_bulan ?>">
                <input type="hidden" name="id_cabang" value="<?= $filter_cabang ?>">
                <input type="hidden" name="id_unit" value="<?= $filter_unit ?>">
                <input type="hidden" name="id_jabatan" value="<?= $filter_jabatan ?>">
                <button type="button" id="btnSyncMonths" class="btn btn-sm btn-outline-info shadow-sm me-1" title="Salin tugas di bulan ini ke semua bulan lain"><i class="bi bi-arrow-repeat"></i> Terapkan ke Semua Bulan</button>
            </form>
            <button class="btn btn-sm btn-success shadow-sm me-1" data-bs-toggle="modal" data-bs-target="#importTugasModal"><i class="bi bi-file-earmark-excel"></i> Import Excel</button>
            <button class="btn btn-sm btn-primary shadow-sm" data-bs-toggle="modal" data-bs-target="#addTugasModal"><i class="bi bi-plus-circle"></i> Tambah Tugas</button>
        </div>
    </div>
    <div class="table-responsive">
        <table class="table table-bordered table-hover align-middle" style="white-space: nowrap; font-size: 0.9rem;">
            <thead class="table-light text-center align-middle">
                <tr>
                    <th>NO</th>
                    <th style="min-width: 250px; white-space: normal;">URAIAN TUGAS</th>
                    <th>HASIL KERJA / OUTPUT</th>
                    <th>Standar Waktu<br><small>(menit)</small></th>
                    <th>KETERANGAN</th>
                    <th>Tindakan Petugas</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if(empty($tugas)): ?>
                    <tr><td colspan="8" class="text-center text-muted">Belum ada data uraian tugas untuk jabatan ini.</td></tr>
                <?php endif; ?>
                
                <?php 
                // Pisahkan Parent dan Child
                $parents = array_filter($tugas, function($t) { return empty($t->id_parent); });
                $children = array_filter($tugas, function($t) { return !empty($t->id_parent); });
                $no = 1;
                
                foreach($parents as $t): 
                ?>
                <!-- TUGAS INDUK -->
                <tr class="bg-light">
                    <td class="text-center fw-bold"><?= $no++ ?></td>
                    <td style="white-space: normal;" class="fw-bold"><?= nl2br(htmlspecialchars($t->nama_tugas ?? '')) ?></td>
                    <td><?= htmlspecialchars($t->output_pekerjaan ?? '') ?></td>
                    
                    <td class="text-center fw-bold text-primary"><?= $t->standar_waktu !== null ? $t->standar_waktu : '-' ?></td>
                    <td style="white-space: normal; min-width: 200px;"><?= nl2br(htmlspecialchars($t->keterangan ?? '')) ?></td>
                    <td class="text-center"><?= $t->tindakan_petugas ? ucfirst(htmlspecialchars($t->tindakan_petugas)) : '-' ?></td>
                    <td>
                        <?php if($t->is_active == 1): ?><span class="badge bg-success">Aktif</span><?php else: ?><span class="badge bg-danger">Nonaktif</span><?php endif; ?>
                    </td>
                    <td class="text-end">
                        <button class="btn btn-sm btn-outline-warning text-dark mb-1" data-bs-toggle="modal" data-bs-target="#editTugasModal<?= $t->id_tugas ?>" title="Edit"><i class="bi bi-pencil-square"></i></button>
                        <a href="<?= site_url('admin/uraiantugas/delete/'.$t->id_tugas) ?>" class="btn btn-sm btn-outline-danger btn-delete" title="Hapus (Nonaktifkan)"><i class="bi bi-trash"></i></a>
                    </td>
                </tr>

                <!-- SUB TUGAS (CHILDREN) -->
                <?php $char = 'a'; foreach($children as $c): if($c->id_parent == $t->id_tugas): ?>
                <tr>
                    <td class="text-center"><?= $char++ ?></td>
                    <td style="white-space: normal; padding-left: 2rem;"><i class="bi bi-arrow-return-right text-muted me-2"></i><?= nl2br(htmlspecialchars($c->nama_tugas ?? '')) ?></td>
                    <td><?= htmlspecialchars($c->output_pekerjaan ?? '') ?></td>
                    
                    <td class="text-center fw-bold text-primary"><?= $c->standar_waktu !== null ? $c->standar_waktu : '-' ?></td>
                    <td style="white-space: normal; min-width: 200px;"><?= nl2br(htmlspecialchars($c->keterangan ?? '')) ?></td>
                    <td class="text-center"><?= $c->tindakan_petugas ? ucfirst(htmlspecialchars($c->tindakan_petugas)) : '-' ?></td>
                    <td><?php if($c->is_active == 1): ?><span class="badge bg-success">Aktif</span><?php else: ?><span class="badge bg-danger">Nonaktif</span><?php endif; ?></td>
                    <td class="text-end">
                        <button class="btn btn-sm btn-outline-warning text-dark mb-1" data-bs-toggle="modal" data-bs-target="#editTugasModal<?= $c->id_tugas ?>" title="Edit"><i class="bi bi-pencil-square"></i></button>
                        <a href="<?= site_url('admin/uraiantugas/delete/'.$c->id_tugas) ?>" class="btn btn-sm btn-outline-danger btn-delete" title="Hapus (Nonaktifkan)"><i class="bi bi-trash"></i></a>
                    </td>
                </tr>
                <?php endif; endforeach; ?>

                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal Tambah Data -->
<div class="modal fade" id="addTugasModal" tabindex="-1">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content glass border-0 shadow">
      <div class="modal-header border-0"><h5 class="modal-title text-primary"><i class="bi bi-plus-circle me-2"></i>Tambah Uraian Tugas</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
      <form action="<?= site_url('admin/uraiantugas/store') ?>" method="post">
      <div class="modal-body">
          <!-- Hidden fields berdasarkan filter -->
          <input type="hidden" name="tahun" value="<?= $filter_tahun ?>">
          <input type="hidden" name="bulan" value="<?= $filter_bulan ?>">
          <input type="hidden" name="id_cabang" value="<?= $filter_cabang ?>">
          <input type="hidden" name="id_unit" value="<?= $filter_unit ?>">
          <input type="hidden" name="id_jabatan" value="<?= $filter_jabatan ?>">
          
          <div class="mb-3">
              <label class="form-label text-muted">Tugas Induk (Opsional)</label>
              <select name="id_parent" class="form-select">
                  <option value="">-- Tidak Ada (Ini Tugas Utama) --</option>
                  <?php foreach($parents as $p): ?>
                      <option value="<?= $p->id_tugas ?>"><?= htmlspecialchars(substr($p->nama_tugas, 0, 70)) ?>...</option>
                  <?php endforeach; ?>
              </select>
              <small class="text-muted">Pilih jika ini adalah sub-tugas (contoh: poin a, b) dari tugas utama.</small>
          </div>

          <div class="mb-3">
              <label class="form-label text-muted">Uraian Tugas</label>
              <textarea name="nama_tugas" class="form-control" rows="2" placeholder="Contoh: Melakukan aktivitas pemasaran dan cross selling..." required></textarea>
          </div>
          
          <div class="row">
              <div class="col-md-6 mb-3">
                  <label class="form-label text-muted">Output Pekerjaan (Opsional)</label>
                  <input type="text" name="output_pekerjaan" class="form-control" placeholder="Contoh: Dokumen Laporan">
              </div>
              <div class="col-md-6 mb-3">
                  <label class="form-label text-muted">Standar Waktu (Opsional)</label>
                  <input type="number" step="0.01" name="standar_waktu" class="form-control" placeholder="Angka dalam menit (cth: 10)">
              </div>
          </div>

          <div class="mb-3">
              <label class="form-label text-muted">Keterangan Tugas (Opsional)</label>
              <textarea name="keterangan" class="form-control" rows="2" placeholder="Detail keterangan tugas..."></textarea>
          </div>

          <div class="row">
              <div class="col-md-6 mb-3">
                  <label class="form-label text-muted">Tindakan Petugas (Opsional)</label>
                  <select name="tindakan_petugas" class="form-select"><option value="">-- Pilih --</option><option value="Observasi">Observasi</option><option value="Konfirmasi">Konfirmasi</option></select>
              </div>
              <div class="col-md-6 mb-3">
                  <label class="form-label text-muted">Status</label>
                  <select name="is_active" class="form-select"><option value="1">Aktif</option><option value="0">Nonaktif</option></select>
              </div>
          </div>
      </div>
      <div class="modal-footer border-0"><button class="btn btn-primary shadow-sm"><i class="bi bi-save me-1"></i> Simpan Data</button></div>
      </form>
    </div>
  </div>
</div>

<!-- Modal Edit Data -->
<?php foreach($tugas as $t): ?>
<div class="modal fade" id="editTugasModal<?= $t->id_tugas ?>" tabindex="-1">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content glass border-0 shadow">
      <div class="modal-header border-0"><h5 class="modal-title text-warning"><i class="bi bi-pencil-square me-2"></i>Edit Uraian Tugas</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
      <form action="<?= site_url('admin/uraiantugas/update') ?>" method="post">
      <div class="modal-body">
          <input type="hidden" name="id_tugas" value="<?= $t->id_tugas ?>">
          
          <div class="mb-3">
              <label class="form-label text-muted">Tugas Induk</label>
              <select name="id_parent" class="form-select">
                  <option value="">-- Tidak Ada (Ini Tugas Utama) --</option>
                  <?php foreach($parents as $p): if($p->id_tugas != $t->id_tugas): ?>
                      <option value="<?= $p->id_tugas ?>" <?= $p->id_tugas == $t->id_parent ? 'selected' : '' ?>><?= htmlspecialchars(substr($p->nama_tugas, 0, 70)) ?>...</option>
                  <?php endif; endforeach; ?>
              </select>
          </div>

          <div class="mb-3"><label class="form-label text-muted">Uraian Tugas</label><textarea name="nama_tugas" class="form-control" rows="3" required><?= htmlspecialchars($t->nama_tugas ?? '') ?></textarea></div>
          
          <div class="row">
              <div class="col-md-6 mb-3"><label class="form-label text-muted">Output Pekerjaan</label><input type="text" name="output_pekerjaan" class="form-control" value="<?= htmlspecialchars($t->output_pekerjaan ?? '') ?>"></div>
              <div class="col-md-6 mb-3"><label class="form-label text-muted">Standar Waktu (Menit)</label><input type="number" step="0.01" name="standar_waktu" class="form-control" value="<?= htmlspecialchars($t->standar_waktu ?? '') ?>"></div>
          </div>

          <div class="mb-3"><label class="form-label text-muted">Keterangan Tugas</label><textarea name="keterangan" class="form-control" rows="2"><?= htmlspecialchars($t->keterangan ?? '') ?></textarea></div>

          <div class="row">
              <div class="col-md-6 mb-3">
                  <label class="form-label text-muted">Tindakan Petugas</label>
                  <select name="tindakan_petugas" class="form-select"><option value="">-- Pilih --</option><option value="Observasi" <?= strtolower($t->tindakan_petugas) == 'observasi' ? 'selected' : '' ?>>Observasi</option><option value="Konfirmasi" <?= strtolower($t->tindakan_petugas) == 'konfirmasi' ? 'selected' : '' ?>>Konfirmasi</option></select>
              </div>
              <div class="col-md-6 mb-3">
                  <label class="form-label text-muted">Status</label>
                  <select name="is_active" class="form-select"><option value="1" <?= $t->is_active == 1 ? 'selected' : '' ?>>Aktif</option><option value="0" <?= $t->is_active == 0 ? 'selected' : '' ?>>Nonaktif</option></select>
              </div>
          </div>
      </div>
      <div class="modal-footer border-0"><button class="btn btn-primary shadow-sm">Simpan Perubahan</button></div>
      </form>
    </div>
  </div>
</div>
<?php endforeach; ?>

<!-- Modal Import Excel -->
<div class="modal fade" id="importTugasModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content glass border-0 shadow">
      <div class="modal-header border-0"><h5 class="modal-title text-success"><i class="bi bi-file-earmark-excel me-2"></i>Import Uraian Tugas</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
      <form action="<?= site_url('admin/uraiantugas/import_excel') ?>" method="post" enctype="multipart/form-data">
      <div class="modal-body">
          <input type="hidden" name="tahun" value="<?= $filter_tahun ?>">
          <input type="hidden" name="bulan" value="<?= $filter_bulan ?>">
          <input type="hidden" name="id_cabang" value="<?= $filter_cabang ?>">
          <input type="hidden" name="id_unit" value="<?= $filter_unit ?>">
          <input type="hidden" name="id_jabatan" value="<?= $filter_jabatan ?>">
          <div class="alert alert-info small">Silakan upload laporan WLA atau template Excel. Sistem otomatis membaca kolom Uraian Tugas, Hasil Kerja, dan Standar Waktu. Tugas yang diimport otomatis diterapkan ke seluruh bulan di tahun <?= $filter_tahun ?>.</div>
          <div class="mb-3"><label class="form-label">File Excel (.xlsx)</label><input type="file" name="file_excel" class="form-control" accept=".xlsx, .xls" required></div>
      </div>
      <div class="modal-footer border-0"><button class="btn btn-success">Mulai Import</button></div>
      </form>
    </div>
  </div>
</div>
<?php endif; ?>

<!-- Datatables & Script Dependent Dropdown -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function() {
    // Konfirmasi Terapkan Ke Semua Bulan
    $('#btnSyncMonths').click(function(e) {
        e.preventDefault();
        Swal.fire({
            title: 'Terapkan ke Semua Bulan?',
            text: "Tindakan ini akan menyalin seluruh tugas di bulan ini ke seluruh bulan lain di tahun <?= $filter_tahun ?>. Lanjutkan?",
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#0dcaf0',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Ya, Lanjutkan!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                $('#formSyncMonths').submit();
            }
        });
    });

    // Konfirmasi Delete
    $(document).on('click', '.btn-delete', function(e) {
        e.preventDefault();
        var url = $(this).attr('href');
        Swal.fire({ title: 'Nonaktifkan Tugas?', text: "Data tidak akan dihapus permanen, melainkan di set ke Nonaktif.", icon: 'warning', showCancelButton: true, confirmButtonColor: '#d33', cancelButtonColor: '#6c757d', confirmButtonText: 'Ya, Nonaktifkan!' }).then((res) => { if (res.isConfirmed) window.location.href = url; });
    });

    // AJAX Filter Dropdowns
    $('#filterCabang').change(function() { 
        $('#filterUnit').html('<option>Loading...</option>'); 
        $.post('<?= site_url("admin/organisasi/ajax_get_unit") ?>', {id_cabang: $(this).val()}, function(d) { 
            let h='<option value="">-- Pilih Unit --</option>'; $.each(d, function(i,v){ h+=`<option value="${v.id_unit}">${v.nama_unit}</option>`;}); 
            $('#filterUnit').html(h); $('#filterJabatan').html('<option value="">-- Pilih --</option>');
        }, 'json'); 
    });
    $('#filterUnit').change(function() { 
        $('#filterJabatan').html('<option>Loading...</option>'); 
        $.post('<?= site_url("admin/organisasi/ajax_get_jabatan") ?>', {id_unit: $(this).val()}, function(d) { 
            let h='<option value="">-- Pilih Jabatan --</option>'; $.each(d, function(i,v){ h+=`<option value="${v.id_jabatan}">${v.nama_jabatan}</option>`;}); 
            $('#filterJabatan').html(h);
        }, 'json'); 
    });
});
</script>