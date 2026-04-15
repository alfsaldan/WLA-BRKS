<div class="row mb-4">
  <div class="col-12">
    <h3 class="mb-0 text-primary"><i class="bi bi-person-bounding-box me-2"></i>Monitoring Individu</h3>
    <small class="text-muted">Laporan rekapitulasi Analisis Beban Kerja per Pegawai berdasarkan NIP (Januari - Desember)</small>
  </div>
</div>

<!-- Filter Form -->
<form method="GET" action="<?= site_url('admin/monitoring/individu') ?>" class="card glass p-3 mb-4 border-0 shadow-sm">
    <div class="row g-3 align-items-end">
        <div class="col-md-3">
            <label class="form-label text-muted small">NIP Pegawai</label>
            <input type="text" name="nip" class="form-control" value="<?= htmlspecialchars($filter_nip ?? '') ?>" placeholder="Masukkan NIP Pegawai" required>
        </div>
        <div class="col-md-2">
            <label class="form-label text-muted small">Tahun</label>
            <input type="number" name="tahun" class="form-control" value="<?= $filter_tahun ?>" required>
        </div>
        <div class="col-md-2">
            <button type="submit" class="btn btn-primary w-100" title="Cari Data"><i class="bi bi-search me-2"></i>Tampilkan</button>
        </div>
    </div>
</form>

<?php if(!empty($filter_nip) && !isset($pegawai)): ?>
    <div class="alert alert-danger shadow-sm border-0"><i class="bi bi-exclamation-triangle-fill me-2"></i> Pegawai dengan NIP <b><?= htmlspecialchars($filter_nip) ?></b> tidak ditemukan.</div>
<?php elseif(!empty($filter_nip) && isset($pegawai) && empty($pegawai->id_jabatan)): ?>
    <div class="alert alert-warning shadow-sm border-0"><i class="bi bi-exclamation-circle-fill me-2"></i> Pegawai dengan NIP <b><?= htmlspecialchars($filter_nip) ?></b> belum diatur penempatan jabatannya.</div>
<?php elseif(!empty($filter_nip) && isset($pegawai) && !empty($pegawai->id_jabatan)): ?>

<?php 
    $wke_default = 9888;
?>
<div class="card bg-white p-4 shadow-sm border-0 mb-5" style="border-radius: 0; overflow-x: auto;">
    
    <!-- Laporan Header -->
    <div class="mb-4">
        <h4 class="text-center fw-bold mb-4" style="letter-spacing: 2px;">WORK LOAD ANALYSIS (INDIVIDU)</h4>
        <table class="table table-sm table-borderless fw-bold" style="width: auto;">
            <tr><td width="30">1</td><td width="200">NAMA PEGAWAI / NIP</td><td width="10">:</td><td class="text-primary"><?= strtoupper($pegawai->nama) ?> / <?= $pegawai->nip ?></td></tr>
            <tr><td>2</td><td>NAMA JABATAN</td><td>:</td><td><?= strtoupper($jabatan_info->nama_jabatan ?? '-') ?></td></tr>
            <tr><td>3</td><td>UNIT KERJA</td><td>:</td><td><?= strtoupper(($jabatan_info->nama_cabang ?? '-') . ' - ' . ($jabatan_info->nama_unit ?? '-')) ?></td></tr>
            <tr><td>4</td><td colspan="3">TUGAS POKOK</td></tr>
        </table>
    </div>

    <!-- Laporan Tabel -->
    <table class="table table-bordered align-middle" style="font-size: 0.85rem; border-color: #dee2e6; min-width: 1400px;">
        <thead class="text-center align-middle bg-light">
            <tr>
                <th rowspan="2" width="40">NO</th>
                <th rowspan="2" style="min-width: 250px;">URAIAN TUGAS</th>
                <th rowspan="2" style="min-width: 150px;">HASIL KERJA / OUTPUT</th>
                <th colspan="12">Volume Pekerjaan (bulan) <?= $filter_tahun ?></th>
                <th rowspan="2" width="90">Rata-Rata Transaksi</th>
                <th rowspan="2" width="90">Standar Waktu Penyelesaian<br><small class="fw-normal">( menit )</small></th>
                <th rowspan="2" width="100">Requirred Processing Minutes ( RPM )<br><small class="fw-normal">( menit / bulan )</small></th>
                <th rowspan="2" style="min-width: 150px;">KETERANGAN</th>
            </tr>
            <tr style="font-size: 0.75rem;">
                <?php $months = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Ags', 'Sep', 'Okt', 'Nov', 'Des'];
                foreach($months as $m): ?>
                    <th width="45"><?= $m ?></th>
                <?php endforeach; ?>
            </tr>
        </thead>
        <tbody>
            <?php if(empty($tugas)): ?>
                <tr><td colspan="19" class="text-center text-muted py-3">Tidak ada data uraian tugas untuk jabatan ini.</td></tr>
            <?php endif; ?>
            
            <?php 
            $parents = array_filter($tugas, function($t) { return empty($t->id_parent); });
            $children = array_filter($tugas, function($t) { return !empty($t->id_parent); });
            
            $no = 1;
            $total_all_standar_waktu = 0;
            $total_all_rpm = 0;
            
            $has_children_array = array_column($children, 'id_parent');
            
            foreach($parents as $t): 
                $is_group = in_array($t->id_tugas, $has_children_array);
                
                $total_volume = 0;
                if (!$is_group) {
                    for($i=1; $i<=12; $i++) {
                        $vol = isset($monthly_volumes[$t->id_tugas][$i]) ? $monthly_volumes[$t->id_tugas][$i] : 0;
                        $total_volume += $vol; 
                    }
                }
                
                $rata_rata_transaksi = 0;
                if (!$is_group) {
                    $rata_rata_transaksi = ceil($total_volume / 12);
                    $rata_rata_transaksi = ceil($total_volume / (isset($active_months) ? $active_months : 12));
                }
                
                $rpm = (!$is_group && $t->standar_waktu !== null) ? ($rata_rata_transaksi * $t->standar_waktu) : 0;
                
                if (!$is_group) {
                    $total_all_standar_waktu += (float)$t->standar_waktu;
                    $total_all_rpm += $rpm;
                }
            ?>
            <tr <?= $is_group ? 'class="bg-light fw-bold"' : '' ?>>
                <td class="text-center"><?= $no++ ?></td>
                <td style="white-space: normal;"><?= nl2br(htmlspecialchars($t->nama_tugas ?? '')) ?></td>
                <td><?= htmlspecialchars($t->output_pekerjaan ?? '') ?></td>
                
                <!-- Cetak Volume 12 Bulan -->
                <?php for($i=1; $i<=12; $i++): 
                    $vol_val = isset($monthly_volumes[$t->id_tugas][$i]) && $monthly_volumes[$t->id_tugas][$i] > 0 ? $monthly_volumes[$t->id_tugas][$i] : '-';
                ?>
                    <td class="text-center <?= $vol_val !== '-' ? 'text-primary fw-bold' : '' ?>"><?= $is_group ? '' : $vol_val ?></td>
                <?php endfor; ?>
                
                <td class="text-center fw-bold text-success"><?= $is_group ? '' : $rata_rata_transaksi ?></td>
                <td class="text-center"><?= $is_group ? '' : ($t->standar_waktu ?: '-') ?></td>
                <td class="text-center"><?= $is_group ? '' : ($rpm > 0 ? $rpm : '-') ?></td>
                <td style="white-space: normal;" class="small"><?= nl2br(htmlspecialchars($t->keterangan ?? '')) ?></td>
            </tr>

            <!-- Cetak Anak Poin (a, b) -->
            <?php 
            $char = 'a'; 
            foreach($children as $c): if($c->id_parent == $t->id_tugas): 
                
                $c_total_volume = 0;
                for($i=1; $i<=12; $i++) {
                    $c_vol = isset($monthly_volumes[$c->id_tugas][$i]) ? $monthly_volumes[$c->id_tugas][$i] : 0;
                    $c_total_volume += $c_vol; 
                }
                
                $c_rata_rata_transaksi = ceil($c_total_volume / 12);
                $c_rata_rata_transaksi = ceil($c_total_volume / (isset($active_months) ? $active_months : 12));
                $c_rpm = ($c->standar_waktu !== null) ? ($c_rata_rata_transaksi * $c->standar_waktu) : 0;
                
                $total_all_standar_waktu += (float)$c->standar_waktu;
                $total_all_rpm += $c_rpm;
            ?>
            <tr>
                <td class="text-center"><?= $char++ ?></td>
                <td style="white-space: normal; padding-left: 2rem;"><?= nl2br(htmlspecialchars($c->nama_tugas ?? '')) ?></td>
                <td><?= htmlspecialchars($c->output_pekerjaan ?? '') ?></td>
                
                <?php for($i=1; $i<=12; $i++): 
                    $c_vol_val = isset($monthly_volumes[$c->id_tugas][$i]) && $monthly_volumes[$c->id_tugas][$i] > 0 ? $monthly_volumes[$c->id_tugas][$i] : '-';
                ?>
                    <td class="text-center <?= $c_vol_val !== '-' ? 'text-primary fw-bold' : '' ?>"><?= $c_vol_val ?></td>
                <?php endfor; ?>
                
                <td class="text-center fw-bold text-success"><?= $c_rata_rata_transaksi ?></td>
                <td class="text-center"><?= $c->standar_waktu ?: '-' ?></td>
                <td class="text-center"><?= $c_rpm > 0 ? $c_rpm : '-' ?></td>
                <td style="white-space: normal;" class="small"><?= nl2br(htmlspecialchars($c->keterangan ?? '')) ?></td>
            </tr>
            <?php endif; endforeach; ?>

            <?php endforeach; ?>
        </tbody>
        <tfoot class="fw-bold bg-light">
            <?php 
                $allowance_val = ($allowance_pct / 100) * $total_all_rpm;
                $total_rpm_plus_allowance = $total_all_rpm + $allowance_val;
                $fte = $total_rpm_plus_allowance > 0 ? ($total_rpm_plus_allowance / $wke_default) : 0;
                $kebutuhan = round($fte);
            ?>
            <tr>
                <td colspan="16" class="text-end">JUMLAH</td>
                <td class="text-center"><?= $total_all_standar_waktu > 0 ? $total_all_standar_waktu : '-' ?></td>
                <td class="text-center"><?= $total_all_rpm > 0 ? $total_all_rpm : '-' ?></td>
                <td></td>
            </tr>
            <tr>
                <td colspan="16" class="text-end text-primary">JUMLAH KEBUTUHAN PEGAWAI (FTE)</td>
                <td colspan="3" class="text-start text-primary h6 mb-0 py-3"><?= $kebutuhan ?> Pegawai</td>
            </tr>
        </tfoot>
    </table>
</div>
<?php endif; ?>