<?php if($this->session->flashdata('success')): ?>
<script> document.addEventListener("DOMContentLoaded", function() { Swal.fire({icon: 'success', title: 'Tersimpan!', text: '<?= $this->session->flashdata("success") ?>', timer: 2500, showConfirmButton: false}); }); </script>
<?php endif; ?>

<div class="row g-4 mb-4">
    <!-- Kartu Profil Jabatan -->
    <div class="col-md-7">
        <div class="card glass border-0 shadow-sm h-100 p-4">
            <div class="d-flex align-items-start">
                <?php 
                $foto_url = (!empty($user->foto) && file_exists('./assets/img/profil/'.$user->foto)) 
                            ? base_url('assets/img/profil/'.$user->foto) 
                            : 'https://ui-avatars.com/api/?name='.urlencode($user->nama ?? 'P').'&background=198754&color=fff';
                ?>
                <img src="<?= $foto_url ?>" alt="Profil" class="rounded-circle me-3 shadow-sm border border-2 border-white" style="width: 60px; height: 60px; object-fit: cover;">
                <div>
                    <h4 class="mb-1 text-dark fw-bold"><?= isset($user->nama) ? htmlspecialchars($user->nama) : '-' ?></h4>
                    <p class="mb-0 text-primary fw-bold"><i class="bi bi-briefcase me-1"></i> <?= isset($user->nama_jabatan) ? htmlspecialchars($user->nama_jabatan) : 'Jabatan Belum Diatur' ?></p>
                    <small class="text-muted"><i class="bi bi-building me-1"></i> <?= htmlspecialchars($user->nama_cabang ?? '-') ?> / <?= htmlspecialchars($user->nama_unit ?? '-') ?></small>
                </div>
            </div>
        </div>
    </div>

    <!-- Kartu Filter Periode -->
    <div class="col-md-5">
        <div class="card glass border-0 shadow-sm h-100 p-4">
            <h6 class="text-muted mb-3"><i class="bi bi-calendar-month me-2"></i>Pilih Periode Input</h6>
            <form method="GET" action="<?= site_url('pegawai/dashboard') ?>" class="row g-2">
                <div class="col-sm-5">
                    <input type="number" name="tahun" class="form-control form-control-sm" value="<?= $filter_tahun ?>" required>
                </div>
                <div class="col-sm-5">
                    <select name="bulan" class="form-select form-select-sm" required>
                        <?php $bulan_indo = ['', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
                        for($i=1; $i<=12; $i++): $m = str_pad($i, 2, '0', STR_PAD_LEFT); ?>
                            <option value="<?= $m ?>" <?= $m == $filter_bulan ? 'selected' : '' ?>><?= $bulan_indo[$i] ?></option>
                        <?php endfor; ?>
                    </select>
                </div>
                <div class="col-sm-2">
                    <button type="submit" class="btn btn-primary btn-sm w-100 shadow-sm"><i class="bi bi-search"></i></button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Dashboard Metrik & Grafik Tren -->
<div class="row g-4 mb-4">
    <div class="col-md-4 d-flex flex-column gap-4">
        <div class="card glass border-0 shadow-sm p-4 flex-grow-1">
            <h6 class="text-muted mb-3"><i class="bi bi-pie-chart text-primary me-2"></i>Status Pengisian (<?= $bulan_indo[(int)$filter_bulan] ?>)</h6>
            <h3 class="fw-bold text-dark mb-1"><?= $filled_tasks ?> / <?= $total_tasks ?> <span class="fs-6 fw-normal text-muted">Tugas</span></h3>
            <?php $pct = $total_tasks > 0 ? round(($filled_tasks/$total_tasks)*100) : 0; ?>
            <div class="progress mt-3" style="height: 10px;">
                <div class="progress-bar bg-primary progress-bar-striped <?= $pct < 100 ? 'progress-bar-animated' : '' ?>" style="width: <?= $pct ?>%"></div>
            </div>
            <small class="text-muted mt-2 d-block"><?= $pct ?>% selesai diisi bulan ini</small>
        </div>
        <div class="card glass border-0 shadow-sm p-4 flex-grow-1">
            <h6 class="text-muted mb-3"><i class="bi bi-bar-chart-steps text-success me-2"></i>Total Volume Input</h6>
            <h3 class="fw-bold text-success mb-0"><?= number_format($total_volume_month, 2, ',', '.') ?> <span class="fs-6 fw-normal text-muted">Aktivitas</span></h3>
            <small class="text-muted mt-2 d-block">Akumulasi seluruh beban kerja bulan ini</small>
        </div>
    </div>
    <div class="col-md-8">
        <div class="card glass border-0 shadow-sm p-4 h-100">
            <h6 class="text-muted mb-4"><i class="bi bi-graph-up-arrow text-warning me-2"></i>Tren Volume Transaksi Tahun <?= $filter_tahun ?></h6>
            <div style="height: 220px;"><canvas id="volumeChart"></canvas></div>
        </div>
    </div>
</div>

<!-- Form Tabel Beban Kerja -->
<div class="card bg-white border-0 shadow-sm rounded-4 overflow-hidden mb-4">
    <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
        <h5 class="mb-0 text-dark fw-bold"><i class="bi bi-pencil-square me-2 text-success"></i>Form Input Volume Transaksi</h5>
        <span class="badge bg-light text-dark border px-3 py-2">Periode: <?= $bulan_indo[(int)$filter_bulan] ?> <?= $filter_tahun ?></span>
    </div>
    <div class="card-body p-0">
        <form action="<?= site_url('pegawai/dashboard/save') ?>" method="post">
            <input type="hidden" name="tahun" value="<?= $filter_tahun ?>">
            <input type="hidden" name="bulan" value="<?= $filter_bulan ?>">
            
            <div class="table-responsive">
                <table class="table table-hover table-bordered align-middle mb-0" style="font-size: 0.9rem;">
                    <thead class="table-light text-center">
                        <tr>
                            <th width="50">No</th>
                            <th style="min-width: 300px;">Uraian Tugas</th>
                            <th width="150">Standar Waktu</th>
                            <th width="180" class="bg-success bg-opacity-10 text-success border-success">Input Volume</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(empty($tugas)): ?>
                            <tr><td colspan="4" class="text-center py-5 text-muted"><i class="bi bi-inbox fs-1 d-block mb-2"></i> Belum ada Master Uraian Tugas untuk jabatan Anda pada periode ini.</td></tr>
                        <?php endif; ?>
                        
                        <?php 
                        $parents = array_filter($tugas, function($t) { return empty($t->id_parent); });
                        $children = array_filter($tugas, function($t) { return !empty($t->id_parent); });
                        $has_children_array = array_column($children, 'id_parent');
                        $no = 1;
                        
                        foreach($parents as $t): 
                            $is_group = in_array($t->id_tugas, $has_children_array);
                            $val = isset($volumes[$t->id_tugas]) ? $volumes[$t->id_tugas] : '';
                        ?>
                        <tr <?= $is_group ? 'class="bg-light fw-bold"' : '' ?>>
                            <td class="text-center"><?= $no++ ?></td>
                            <td style="white-space: normal;"><?= nl2br(htmlspecialchars($t->nama_tugas)) ?></td>
                            <td class="text-center"><?= $is_group ? '' : ($t->standar_waktu ? $t->standar_waktu.' mnt' : '-') ?></td>
                            <td class="text-center p-2 <?= $is_group ? '' : 'bg-success bg-opacity-10 border-success' ?>">
                                <?= $is_group ? '' : '<input type="number" step="0.01" min="0" name="volume['.$t->id_tugas.']" class="form-control form-control-sm text-center fw-bold text-success border-success shadow-sm" placeholder="0" value="'.$val.'">' ?>
                            </td>
                        </tr>
                        <?php $char = 'a'; foreach($children as $c): if($c->id_parent == $t->id_tugas): $val_c = isset($volumes[$c->id_tugas]) ? $volumes[$c->id_tugas] : ''; ?>
                        <tr>
                            <td class="text-center"><?= $char++ ?></td>
                            <td style="white-space: normal; padding-left: 2rem;"><i class="bi bi-arrow-return-right text-muted me-2"></i><?= nl2br(htmlspecialchars($c->nama_tugas)) ?></td>
                            <td class="text-center"><?= $c->standar_waktu ? $c->standar_waktu.' mnt' : '-' ?></td>
                            <td class="text-center p-2 bg-success bg-opacity-10 border-success"><input type="number" step="0.01" min="0" name="volume[<?= $c->id_tugas ?>]" class="form-control form-control-sm text-center fw-bold text-success border-success shadow-sm" placeholder="0" value="<?= $val_c ?>"></td>
                        </tr>
                        <?php endif; endforeach; endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php if(!empty($tugas)): ?><div class="p-3 bg-light border-top text-end"><button type="submit" class="btn btn-success rounded-pill shadow-sm px-4 fw-bold"><i class="bi bi-check2-circle me-1"></i> Simpan Data Volume</button></div><?php endif; ?>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('volumeChart').getContext('2d');
    
    // Gradasi Warna Area Garis
    const gradient = ctx.createLinearGradient(0, 0, 0, 300);
    gradient.addColorStop(0, 'rgba(13, 110, 253, 0.4)'); // Biru Primary Transparent
    gradient.addColorStop(1, 'rgba(13, 110, 253, 0)');

    new Chart(ctx, {
        type: 'line',
        data: {
            labels: ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'],
            datasets: [{
                label: 'Total Volume Bulanan',
                data: <?= $chart_data ?>,
                borderColor: '#0d6efd',
                backgroundColor: gradient,
                borderWidth: 3,
                pointBackgroundColor: '#fff',
                pointBorderColor: '#0d6efd',
                pointRadius: 4,
                fill: true,
                tension: 0.4 // Membuat garis melengkung dinamis (smooth)
            }]
        },
        options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, grid: { borderDash: [5, 5] } }, x: { grid: { display: false } } } }
    });
});
</script>