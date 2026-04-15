<div class="row mb-4">
  <div class="col-12">
    <h3 class="mb-0 text-primary"><i class="bi bi-display me-2"></i>Monitoring WLA</h3>
    <small class="text-muted">Laporan komprehensif Analisis Beban Kerja per Jabatan</small>
  </div>
</div>

<!-- Filter Form -->
<form method="GET" action="<?= site_url('admin/monitoring') ?>" class="card glass p-3 mb-4 border-0 shadow-sm">
    <div class="row g-3 align-items-end">
        <div class="col-md-2">
            <label class="form-label text-muted small">Tahun</label>
            <input type="number" name="tahun" class="form-control" value="<?= $filter_tahun ?>" required>
        </div>
        <div class="col-md-2">
            <label class="form-label text-muted small">Bulan</label>
            <select name="bulan" class="form-select" required>
                <option value="all" <?= $filter_bulan === 'all' ? 'selected' : '' ?>>Keseluruhan (Akumulasi Jan-Des)</option>
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
            <button type="submit" class="btn btn-primary w-100" title="Tampilkan Laporan"><i class="bi bi-search"></i></button>
        </div>
    </div>
</form>

<?php if(!empty($filter_jabatan)): 
    // Ambil nama struktur untuk dicetak di Header Laporan
    $nama_jab = '-';
    $nama_unt = '-';
    $nama_cab = '-';
    foreach($jabatan as $j) { if($j->id_jabatan == $filter_jabatan) { $nama_jab = $j->nama_jabatan; break; } }
    foreach($unit as $u) { if($u->id_unit == $filter_unit) { $nama_unt = $u->nama_unit; break; } }
    foreach($cabang as $c) { if($c->id_cabang == $filter_cabang) { $nama_cab = $c->nama_cabang; break; } }
    
    $emp_count = !empty($pegawai_list) ? count($pegawai_list) : 1;
    
    // WKE (Waktu Kerja Efektif) - Pembagi RPM untuk menghitung kebutuhan pegawai
    $wke_default = 9888; // Diperbarui sesuai permintaan rumus baru
?>
<div class="card bg-white p-4 shadow-sm border-0 mb-5" style="border-radius: 0; overflow-x: auto;">
    
    <!-- Laporan Header -->
    <div class="mb-4">
        <h4 class="text-center fw-bold mb-4" style="letter-spacing: 2px;">WORK LOAD ANALYSIS</h4>
        <table class="table table-sm table-borderless fw-bold" style="width: auto;">
            <tr><td width="30">1</td><td width="200">NAMA JABATAN</td><td width="10">:</td><td><?= strtoupper($nama_jab) ?></td></tr>
            <tr><td>2</td><td>UNIT KERJA</td><td>:</td><td><?= strtoupper($nama_cab . ' - ' . $nama_unt) ?></td></tr>
            <tr><td>3</td><td>PERTANGGUNGJAWABAN</td><td>:</td><td class="text-muted fst-italic">Pemimpin Seksi / Manajer Terkait</td></tr>
            <tr><td>4</td><td colspan="3">TUGAS POKOK</td></tr>
        </table>
    </div>

    <!-- Laporan Tabel -->
    <table class="table table-bordered align-middle" style="font-size: 0.85rem; border-color: #dee2e6;">
        <thead class="text-center align-middle bg-light">
            <tr>
                <th rowspan="2" width="40">NO</th>
                <th rowspan="2" style="min-width: 250px;">URAIAN TUGAS</th>
                <th rowspan="2" style="min-width: 150px;">HASIL KERJA / OUTPUT</th>
                <th colspan="<?= $emp_count ?>">Volume Transaksi</th>
                <th rowspan="2" width="100">Rata-Rata Transaksi</th>
                <th rowspan="2" width="100">Standar Waktu Penyelesaian<br><small class="fw-normal">( menit )</small></th>
                <th rowspan="2" width="120">Requirred Processing Minutes ( RPM )<br><small class="fw-normal">( menit / bulan )</small></th>
                <th rowspan="2" style="min-width: 200px;">KETERANGAN</th>
                <th rowspan="2">Tindakan Petugas</th>
            </tr>
            <tr style="font-size: 0.75rem;">
                <?php if(!empty($pegawai_list)): foreach($pegawai_list as $p): ?>
                    <th class="text-uppercase text-muted"><?= htmlspecialchars($p->nama ?? '') ?></th>
                <?php endforeach; else: ?>
                    <th class="text-muted">Belum ada pegawai</th>
                <?php endif; ?>
            </tr>
        </thead>
        <tbody>
            <?php if(empty($tugas)): ?>
                <tr><td colspan="<?= 7 + $emp_count ?>" class="text-center text-muted py-3">Tidak ada data uraian tugas.</td></tr>
            <?php endif; ?>
            
            <?php 
            $parents = array_filter($tugas, function($t) { return empty($t->id_parent); });
            $children = array_filter($tugas, function($t) { return !empty($t->id_parent); });
            
            $no = 1;
            $total_all_standar_waktu = 0;
            $total_all_rpm = 0;
            
            // Helper: Cek apakah Parent punya anak
            $has_children_array = array_column($children, 'id_parent');
            
            foreach($parents as $t): 
                $is_group = in_array($t->id_tugas, $has_children_array); // Jika punya poin a,b maka ini parent group
                
                // Hitung volume dan RPM (jika bukan group header)
                $total_volume = 0;
                if (!$is_group) {
                    if(!empty($pegawai_list)) { 
                        foreach($pegawai_list as $p) { 
                            $vol = isset($all_volumes[$p->nip][$t->id_tugas]) ? $all_volumes[$p->nip][$t->id_tugas] : 0;
                            $total_volume += $vol; 
                        } 
                    }
                }
                
                $rata_rata_transaksi = 0;
                if (!$is_group) {
                    // Jika filter keseluruhan, pastikan dibagi (jumlah pegawai * 12 bulan) agar mendapat rata-rata wajar per bulan
                    $divisor = ($filter_bulan === 'all') ? ($emp_count * 12) : $emp_count;
                    $avg = $total_volume / $divisor;
                    $rata_rata_transaksi = ceil($avg);
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
                
                <!-- Cetak Volume Kolom -->
                <?php if(!empty($pegawai_list)): foreach($pegawai_list as $p): 
                    $vol_val = isset($all_volumes[$p->nip][$t->id_tugas]) ? $all_volumes[$p->nip][$t->id_tugas] : '-';
                ?>
                    <td class="text-center"><?= $is_group ? '' : $vol_val ?></td>
                <?php endforeach; else: ?>
                    <td class="text-center"><?= $is_group ? '' : '-' ?></td>
                <?php endif; ?>
                
                <td class="text-center fw-bold text-success"><?= $is_group ? '' : $rata_rata_transaksi ?></td>
                <td class="text-center"><?= $is_group ? '' : ($t->standar_waktu ?: '-') ?></td>
                <td class="text-center"><?= $is_group ? '' : ($rpm > 0 ? $rpm : '-') ?></td>
                <td style="white-space: normal;" class="small"><?= nl2br(htmlspecialchars($t->keterangan ?? '')) ?></td>
                <td><?= $t->tindakan_petugas ? ucfirst(htmlspecialchars($t->tindakan_petugas ?? '')) : '' ?></td>
            </tr>

            <!-- Cetak Anak Poin (a, b) -->
            <?php 
            $char = 'a'; 
            foreach($children as $c): if($c->id_parent == $t->id_tugas): 
                
                $c_total_volume = 0;
                if(!empty($pegawai_list)) { 
                    foreach($pegawai_list as $p) { 
                        $vol_c = isset($all_volumes[$p->nip][$c->id_tugas]) ? $all_volumes[$p->nip][$c->id_tugas] : 0;
                        $c_total_volume += $vol_c; 
                    } 
                }
                
                // Sama seperti tugas utama, pastikan dibagi 12 bulan jika filter 'all'
                $c_divisor = ($filter_bulan === 'all') ? ($emp_count * 12) : $emp_count;
                $c_avg = $c_total_volume / $c_divisor;
                $c_rata_rata_transaksi = ceil($c_avg);
                
                $c_rpm = ($c->standar_waktu !== null) ? ($c_rata_rata_transaksi * $c->standar_waktu) : 0;
                
                $total_all_standar_waktu += (float)$c->standar_waktu;
                $total_all_rpm += $c_rpm;
            ?>
            <tr>
                <td class="text-center"><?= $char++ ?></td>
                <td style="white-space: normal; padding-left: 2rem;"><?= nl2br(htmlspecialchars($c->nama_tugas ?? '')) ?></td>
                <td><?= htmlspecialchars($c->output_pekerjaan ?? '') ?></td>
                
                <?php if(!empty($pegawai_list)): foreach($pegawai_list as $p): 
                    $c_vol_val = isset($all_volumes[$p->nip][$c->id_tugas]) ? $all_volumes[$p->nip][$c->id_tugas] : '-';
                ?>
                    <td class="text-center"><?= $c_vol_val ?></td>
                <?php endforeach; else: ?><td class="text-center">-</td><?php endif; ?>
                
                <td class="text-center fw-bold text-success"><?= $c_rata_rata_transaksi ?></td>
                <td class="text-center"><?= $c->standar_waktu ?: '-' ?></td>
                <td class="text-center"><?= $c_rpm > 0 ? $c_rpm : '-' ?></td>
                <td style="white-space: normal;" class="small"><?= nl2br(htmlspecialchars($c->keterangan ?? '')) ?></td>
                <td><?= $c->tindakan_petugas ? ucfirst(htmlspecialchars($c->tindakan_petugas ?? '')) : '' ?></td>
            </tr>
            <?php endif; endforeach; ?>

            <?php endforeach; ?>
        </tbody>
        <tfoot class="fw-bold bg-light">
            <?php 
                // Cari allowance jabatan dari array $jabatan
                $allowance_pct = 0;
                foreach($jabatan as $j) {
                    if($j->id_jabatan == $filter_jabatan) {
                        $allowance_pct = isset($j->allowance) ? (float)$j->allowance : 0;
                        break;
                    }
                }
                
                $allowance_val = ($allowance_pct / 100) * $total_all_rpm;
                $total_rpm_plus_allowance = $total_all_rpm + $allowance_val;
                
                $fte = $total_rpm_plus_allowance > 0 ? ($total_rpm_plus_allowance / $wke_default) : 0;
                $kebutuhan = round($fte);
            ?>
            <tr>
                <td colspan="<?= 4 + $emp_count ?>" class="text-end">JUMLAH</td>
                <td class="text-center"><?= $total_all_standar_waktu > 0 ? $total_all_standar_waktu : '-' ?></td>
                <td class="text-center"><?= $total_all_rpm > 0 ? $total_all_rpm : '-' ?></td>
                <td colspan="2"></td>
            </tr>
            <tr>
                <td colspan="<?= 4 + $emp_count ?>" class="text-end text-primary align-middle">
                    JUMLAH KEBUTUHAN PEGAWAI (FTE)<br>
                    <span class="text-muted fw-normal" style="font-size:0.7rem;">Rumus: (Total RPM + <?= $allowance_pct ?>%) / <?= $wke_default ?></span>
                </td>
                <td colspan="4" class="text-start text-primary h6 mb-0 py-3 align-middle">
                    <?= number_format($fte, 2, ',', '.') ?> &nbsp; <i class="bi bi-arrow-right"></i> &nbsp; Pembulatan: <?= $kebutuhan ?>
                </td>
            </tr>
        </tfoot>
    </table>
</div>
<?php endif; ?>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function() {
    // AJAX Dropdown berantai untuk Form Laporan (Sama dengan master_uraian_tugas)
    $('#filterCabang').change(function() { $('#filterUnit').html('<option>Loading...</option>'); $.post('<?= site_url("admin/organisasi/ajax_get_unit") ?>', {id_cabang: $(this).val()}, function(d) { let h='<option value="">-- Pilih Unit --</option>'; $.each(d, function(i,v){ h+=`<option value="${v.id_unit}">${v.nama_unit}</option>`;}); $('#filterUnit').html(h); $('#filterJabatan').html('<option value="">-- Pilih --</option>'); }, 'json'); });
    $('#filterUnit').change(function() { $('#filterJabatan').html('<option>Loading...</option>'); $.post('<?= site_url("admin/organisasi/ajax_get_jabatan") ?>', {id_unit: $(this).val()}, function(d) { let h='<option value="">-- Pilih Jabatan --</option>'; $.each(d, function(i,v){ h+=`<option value="${v.id_jabatan}">${v.nama_jabatan}</option>`;}); $('#filterJabatan').html(h); }, 'json'); });
});
</script>