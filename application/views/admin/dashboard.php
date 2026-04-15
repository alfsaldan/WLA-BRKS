<div class="row mb-4">
  <div class="col-12">
    <div class="d-flex align-items-center justify-content-between mb-3">
      <div>
        <h3 class="mb-0 text-primary"><i class="bi bi-speedometer2 me-2"></i>Dashboard</h3>
        <small class="text-muted">Ringkasan informasi dan statistik sistem Work Load Analysis (WLA)</small>
      </div>
      <div class="text-end">
        <div class="fw-bold text-dark">Selamat datang, <?= isset($user_name) ? htmlspecialchars($user_name) : 'Admin' ?></div>
        <small class="text-muted"><?= date('l, d F Y') ?></small>
      </div>
    </div>
  </div>
</div>

<!-- Summary Cards -->
<div class="row g-3 mb-4">
  <div class="col-sm-6 col-lg-3">
    <div class="card glass p-3 border-0 shadow-sm h-100 rounded-4">
      <div class="d-flex align-items-center">
        <div class="me-3 display-6 text-primary"><i class="bi bi-people-fill"></i></div>
        <div>
          <div class="small text-muted fw-bold">Total Pegawai</div>
          <div class="h4 mb-0 fw-bold"><?= $total_pegawai ?? 0 ?></div>
        </div>
      </div>
    </div>
  </div>

  <div class="col-sm-6 col-lg-3">
    <div class="card glass p-3 border-0 shadow-sm h-100 rounded-4">
      <div class="d-flex align-items-center">
        <div class="me-3 display-6 text-success"><i class="bi bi-person-check-fill"></i></div>
        <div>
          <div class="small text-muted fw-bold">Total Jabatan</div>
          <div class="h4 mb-0 fw-bold"><?= $total_jabatan ?? 0 ?></div>
        </div>
      </div>
    </div>
  </div>

  <div class="col-sm-6 col-lg-3">
    <div class="card glass p-3 border-0 shadow-sm h-100 rounded-4">
      <div class="d-flex align-items-center">
        <div class="me-3 display-6 text-info"><i class="bi bi-building"></i></div>
        <div>
          <div class="small text-muted fw-bold">Total Unit Kerja</div>
          <div class="h4 mb-0 fw-bold"><?= $total_unit ?? 0 ?></div>
        </div>
      </div>
    </div>
  </div>

  <div class="col-sm-6 col-lg-3">
    <div class="card glass p-3 border-0 shadow-sm h-100 rounded-4">
        <div class="small text-muted fw-bold d-flex justify-content-between">
            <span>Kepatuhan Input (<?= $bulan_nama ?? '' ?>)</span>
            <span class="fw-bold text-dark"><?= $kepatuhan_persen ?? 0 ?>%</span>
        </div>
        <div class="progress mt-2" style="height: 10px;">
            <div class="progress-bar" role="progressbar" style="width: <?= $kepatuhan_persen ?? 0 ?>%;" aria-valuenow="<?= $kepatuhan_persen ?? 0 ?>" aria-valuemin="0" aria-valuemax="100"></div>
        </div>
    </div>
  </div>
</div>

<!-- Row 2: Charts Area -->
<div class="row g-4 mb-4">
  <!-- Line Chart Tren Volume -->
  <div class="col-lg-7">
    <div class="card glass p-4 border-0 shadow-sm h-100 rounded-4">
      <div class="d-flex justify-content-between align-items-center mb-3">
        <h6 class="fw-bold text-dark mb-0"><i class="bi bi-graph-up text-primary me-2"></i>Tren Volume Transaksi Global</h6>
        <span class="badge bg-primary bg-opacity-10 text-primary border border-primary">Tahun <?= $tahun ?? date('Y') ?></span>
      </div>
      <div style="height: 300px; position: relative;">
        <canvas id="trenChart"></canvas>
      </div>
    </div>
  </div>

  <!-- Doughnut Chart WLA Status -->
  <div class="col-lg-5">
    <div class="card glass p-4 border-0 shadow-sm h-100 rounded-4">
      <h6 class="fw-bold text-dark mb-3"><i class="bi bi-pie-chart-fill text-warning me-2"></i>Ringkasan Status Beban Kerja (Tahun <?= $tahun ?? '' ?>)</h6>
      <div style="height: 250px; display: flex; align-items: center; justify-content: center;">
        <canvas id="wlaStatusChart"></canvas>
      </div>
    </div>
  </div>
</div>

<!-- Row 3: Top Lists & Belum Input -->
<div class="row g-4 mb-4">
  <div class="col-lg-8">
    <div class="card glass p-4 border-0 shadow-sm h-100 rounded-4">
      <h6 class="fw-bold text-dark mb-3"><i class="bi bi-list-ol text-info me-2"></i>Jabatan dengan Beban Kerja Kritis (Tahun <?= $tahun ?? '' ?>)</h6>
      <div class="row">
        <div class="col-md-6">
          <h6 class="small fw-bold text-danger"><i class="bi bi-arrow-up-circle-fill"></i> Top 5 Overload / Stretch</h6>
          <ul class="list-group list-group-flush">
            <?php if(empty($top_overload)): ?>
              <li class="list-group-item d-flex justify-content-between align-items-center small text-muted">Tidak ada data.</li>
            <?php endif; ?>
            <?php foreach($top_overload as $item): ?>
            <li class="list-group-item d-flex justify-content-between align-items-center small ps-0">
              <span class="text-truncate" title="<?= htmlspecialchars($item['nama_jabatan']) ?>"><?= htmlspecialchars($item['nama_jabatan']) ?></span>
              <span class="badge bg-danger rounded-pill"><?= number_format($item['ej'], 2) ?></span>
            </li>
            <?php endforeach; ?>
          </ul>
        </div>
        <div class="col-md-6 mt-3 mt-md-0">
          <h6 class="small fw-bold text-primary"><i class="bi bi-arrow-down-circle-fill"></i> Top 5 Underload</h6>
          <ul class="list-group list-group-flush">
            <?php if(empty($top_underload)): ?>
              <li class="list-group-item d-flex justify-content-between align-items-center small text-muted">Tidak ada data.</li>
            <?php endif; ?>
            <?php foreach($top_underload as $item): ?>
            <li class="list-group-item d-flex justify-content-between align-items-center small ps-0">
              <span class="text-truncate" title="<?= htmlspecialchars($item['nama_jabatan']) ?>"><?= htmlspecialchars($item['nama_jabatan']) ?></span>
              <span class="badge bg-primary rounded-pill"><?= number_format($item['ej'], 2) ?></span>
            </li>
            <?php endforeach; ?>
          </ul>
        </div>
      </div>
    </div>
  </div>
  
  <div class="col-lg-4">
    <div class="card glass p-4 border-0 shadow-sm h-100 rounded-4">
      <h6 class="fw-bold text-dark mb-3"><i class="bi bi-person-x-fill text-danger me-2"></i>Pegawai Belum Input (<?= $bulan_nama ?? '' ?>)</h6>
      <div class="list-group list-group-flush" style="max-height: 300px; overflow-y: auto;">
        <?php if(empty($pegawai_belum_input_list)): ?>
          <div class="list-group-item text-center text-muted small py-3">
            <i class="bi bi-check-all fs-2 text-success"></i><br>
            Semua pegawai sudah melakukan input.
          </div>
        <?php endif; ?>
        <?php foreach($pegawai_belum_input_list as $p): ?>
        <a href="<?= site_url('admin/monitoring/individu?nip='.$p->nip) ?>" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center small">
          <span class="text-truncate"><?= htmlspecialchars($p->nama) ?></span>
          <i class="bi bi-chevron-right"></i>
        </a>
        <?php endforeach; ?>
      </div>
      <div class="mt-auto pt-3">
        <div class="d-grid gap-2">
            <a href="<?= site_url('admin/monitoring/hasil') ?>" class="btn btn-primary"><i class="bi bi-file-earmark-bar-graph me-2"></i>Lihat Laporan Hasil WLA</a>
            <a href="<?= site_url('admin/monitoring') ?>" class="btn btn-outline-secondary"><i class="bi bi-display me-2"></i>Monitoring Detail</a>
        </div>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener("DOMContentLoaded", function() {
    // 1. Line Chart Tren Volume
    const trenLabels = <?= $chart_tren_labels ?? '[]' ?>;
    const trenData = <?= $chart_tren_data ?? '[]' ?>;
    if(trenData.some(item => item > 0)) {
        new Chart(document.getElementById('trenChart').getContext('2d'), {
            type: 'line',
            data: {
                labels: trenLabels,
                datasets: [{
                    label: 'Total Volume',
                    data: trenData,
                    borderColor: '#0d6efd',
                    backgroundColor: 'rgba(13, 110, 253, 0.1)',
                    borderWidth: 2,
                    fill: true,
                    tension: 0.3,
                    pointBackgroundColor: '#0d6efd'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    y: { beginAtZero: true, grid: { borderDash: [5, 5] } },
                    x: { grid: { display: false } }
                }
            }
        });
    } else {
        document.getElementById('trenChart').outerHTML = '<div class="text-muted text-center mt-5">Belum ada data volume di tahun ini.</div>';
    }

    // 2. Doughnut Chart Status WLA
    const wlaStatusLabels = <?= $wla_summary_labels ?? '[]' ?>;
    const wlaStatusData = <?= $wla_summary_data ?? '[]' ?>;
    new Chart(document.getElementById('wlaStatusChart').getContext('2d'), {
        type: 'doughnut',
        data: {
            labels: wlaStatusLabels,
            datasets: [{
                data: wlaStatusData,
                backgroundColor: ['#dc3545', '#ffc107', '#198754', '#0dcaf0'],
                borderWidth: 0,
                cutout: '70%'
            }]
        },
        options: {
            responsive: true, maintainAspectRatio: false,
            plugins: { legend: { position: 'right', labels: { usePointStyle: true, padding: 15, boxWidth: 10 } } }
        }
    }
    );
});
</script>
