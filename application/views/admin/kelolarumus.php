<div class="row mb-4">
  <div class="col-12 d-flex justify-content-between align-items-center">
    <div>
      <h3 class="mb-0 text-primary"><i class="bi bi-calculator me-2"></i>Kelola Rumus WLA</h3>
      <small class="text-muted">Atur persentase Allowance per Jabatan untuk perhitungan kebutuhan pegawai (FTE)</small>
    </div>
  </div>
</div>

<?php if($this->session->flashdata('success')): ?>
<script> document.addEventListener("DOMContentLoaded", function() { Swal.fire({icon: 'success', title: 'Tersimpan!', text: '<?= $this->session->flashdata("success") ?>', timer: 2500, showConfirmButton: false}); }); </script>
<?php endif; ?>

<div class="card glass p-4 shadow-sm border-0">
    <form id="formRumus" action="<?= site_url('admin/kelolarumus/update_bulk') ?>" method="POST">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="text-dark mb-0">Persentase Allowance (%) per Jabatan</h5>
            <button type="submit" class="btn btn-primary shadow-sm"><i class="bi bi-save"></i> Simpan Semua Perubahan</button>
        </div>
        
        <div class="alert alert-info small">
            <b>Info:</b> Angka allowance ini akan ditambahkan pada perhitungan akhir (Total RPM) saat melakukan monitoring WLA.<br> 
            Contoh penulisan untuk 40,56% cukup ketik: <code>40.56</code>.
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle datatable">
                <thead class="table-light">
                    <tr>
                        <th width="50" class="text-center">No</th>
                        <th>Nama Jabatan</th>
                        <th width="200">Allowance (%)</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $no=1; foreach($jabatan as $j): ?>
                    <tr>
                        <td class="text-center"><?= $no++ ?></td>
                        <td class="fw-bold"><?= htmlspecialchars($j->nama_jabatan) ?></td>
                        <td>
                            <div class="input-group input-group-sm">
                                <input type="hidden" name="nama_jabatan[]" value="<?= htmlspecialchars($j->nama_jabatan) ?>">
                                <input type="number" step="0.01" min="0" max="100" name="allowance[]" class="form-control" value="<?= isset($j->allowance) ? (float)$j->allowance : '0' ?>">
                                <span class="input-group-text">%</span>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </form>
</div>

<link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css">
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
<script>
$(document).ready(function() {
    $('.datatable').DataTable({ language: {url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/id.json'}, pageLength: 25 });
    
    // Pastikan seluruh data dari semua page DataTables ikut ter-submit
    $('#formRumus').on('submit', function() {
        if ($.fn.DataTable.isDataTable('.datatable')) {
            $('.datatable').DataTable().destroy();
        }
    });
});
</script>