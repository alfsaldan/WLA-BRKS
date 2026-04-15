<div class="row mb-4">
    <div class="col-12">
        <h3 class="mb-0 text-primary"><i class="bi bi-file-earmark-bar-graph me-2"></i>Hasil Analisis Beban Kerja</h3>
        <small class="text-muted">Rekapitulasi perhitungan WLA per unit kerja</small>
    </div>
</div>

<!-- Filter Form -->
<form method="GET" action="<?= site_url('admin/monitoring/hasil') ?>" class="card glass p-3 mb-4 border-0 shadow-sm">
    <div class="row g-3 align-items-end">
        <div class="col-md-3">
            <label class="form-label text-muted small">Tahun</label>
            <input type="number" name="tahun" class="form-control" value="<?= htmlspecialchars($filter_tahun) ?>" required>
        </div>
        <div class="col-md-7">
            <label class="form-label text-muted small">Cabang / Induk Organisasi</label>
            <select name="id_cabang" class="form-select" required>
                <option value="">-- Pilih Cabang --</option>
                <?php foreach($cabang as $c): ?>
                    <option value="<?= $c->id_cabang ?>" <?= $c->id_cabang == $filter_cabang ? 'selected' : '' ?>><?= htmlspecialchars($c->kode_cabang . ' - ' . $c->nama_cabang) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-2">
            <button type="submit" class="btn btn-primary w-100"><i class="bi bi-search me-2"></i>Tampilkan</button>
        </div>
    </div>
</form>

<?php if($filter_cabang && !empty($results)): ?>

<?php if(isset($summary) && !empty($summary)): ?>
<div class="card bg-white p-4 shadow-sm border-0 mb-4">
    <h5 class="fw-bold mb-3"><i class="bi bi-pie-chart-fill text-primary me-2"></i>Ringkasan Status Beban Kerja</h5>
    <div class="row">
        <div class="col-lg-8">
            <div class="table-responsive">
                <table class="table table-sm table-bordered align-middle" style="font-size: 0.85rem;">
                    <thead class="text-center table-light align-middle">
                        <tr>
                            <th class="text-start">ITEM (Posisi Jabatan)</th>
                            <th class="text-danger"><i class="bi bi-arrow-up-circle-fill me-1"></i>Overload</th>
                            <th class="text-warning text-dark"><i class="bi bi-exclamation-triangle-fill me-1"></i>Stretch</th>
                            <th class="text-success"><i class="bi bi-check-circle-fill me-1"></i>Normal</th>
                            <th class="text-info text-dark"><i class="bi bi-arrow-down-circle-fill me-1"></i>Underload</th>
                            <th class="bg-secondary text-white">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $total_overload = 0;
                        $total_stretch = 0;
                        $total_normal = 0;
                        $total_underload = 0;
                        $grand_total = 0;
                        foreach($summary as $jabatan => $counts):
                            $row_total = $counts['Total'];
                            if ($row_total == 0) continue;
                            
                            $total_overload += $counts['Overload'];
                            $total_stretch += $counts['Stretch'];
                            $total_normal += $counts['Normal'];
                            $total_underload += $counts['Underload'];
                            $grand_total += $row_total;
                        ?>
                        <tr>
                            <td class="fw-bold"><?= htmlspecialchars($jabatan) ?></td>
                            <td class="text-center <?= $counts['Overload'] > 0 ? 'text-danger fw-bold bg-danger bg-opacity-10' : 'text-muted' ?>"><?= $counts['Overload'] ?></td>
                            <td class="text-center <?= $counts['Stretch'] > 0 ? 'text-warning text-dark fw-bold bg-warning bg-opacity-10' : 'text-muted' ?>"><?= $counts['Stretch'] ?></td>
                            <td class="text-center <?= $counts['Normal'] > 0 ? 'text-success fw-bold bg-success bg-opacity-10' : 'text-muted' ?>"><?= $counts['Normal'] ?></td>
                            <td class="text-center <?= $counts['Underload'] > 0 ? 'text-info text-dark fw-bold bg-info bg-opacity-10' : 'text-muted' ?>"><?= $counts['Underload'] ?></td>
                            <td class="text-center fw-bold bg-light"><?= $row_total ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot class="fw-bold table-light">
                        <tr class="text-center">
                            <td>Total</td>
                            <td class="text-danger fs-6"><?= $total_overload ?></td>
                            <td class="text-warning text-dark fs-6"><?= $total_stretch ?></td>
                            <td class="text-success fs-6"><?= $total_normal ?></td>
                            <td class="text-info text-dark fs-6"><?= $total_underload ?></td>
                            <td class="fs-6"><?= $grand_total ?></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
        <div class="col-lg-4">
            <table class="table table-sm table-bordered shadow-sm" style="font-size: 0.85rem;">
                <thead class="table-light"><th colspan="2"><i class="bi bi-info-circle-fill text-primary me-2"></i>Keterangan Kelayakan Data</th></thead>
                <tbody>
                    <tr><td><span class="badge bg-danger bg-opacity-10 text-danger border border-danger me-2">1</span> Data Abnormal (Over/Stretch/Under)</td><td class="text-center align-middle fw-bold text-danger"><?= $total_overload + $total_stretch + $total_underload ?></td></tr>
                    <tr><td><span class="badge bg-success bg-opacity-10 text-success border border-success me-2">2</span> Data Normal</td><td class="text-center align-middle fw-bold text-success"><?= $total_normal ?></td></tr>
                </tbody>
                <tfoot class="fw-bold table-light"><tr><td class="text-end">Total Seluruh Data :</td><td class="text-center fs-6"><?= $grand_total ?></td></tr></tfoot>
            </table>
        </div>
    </div>
</div>
<?php endif; ?>

<div class="card bg-white p-4 shadow-sm border-0 mb-5" style="border-radius: 0; overflow-x: auto;">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h5 class="fw-bold mb-0">Tabel Hasil WLA</h5>
        <button class="btn btn-sm btn-outline-success" onclick="window.print()"><i class="bi bi-printer me-2"></i>Cetak Laporan</button>
    </div>

    <table class="table table-bordered align-middle table-hover" style="font-size: 0.85rem; border-color: #dee2e6;">
        <thead class="text-center align-middle fw-bold" style="background-color: #f8f9fa;">
            <tr>
                <th rowspan="2" class="align-middle">Posisi Jabatan</th>
                <th rowspan="2" class="align-middle">Kode Induk Unit</th>
                <th rowspan="2" class="align-middle">Kode Unit</th>
                <th rowspan="2" class="align-middle">Unit Kantor</th>
                <th colspan="2" class="bg-primary bg-opacity-10 text-primary">Perhitungan Jumlah Kebutuhan Pegawai</th>
                <th rowspan="2" class="align-middle">Jumlah Pegawai yang Ada</th>
                <th rowspan="2" class="align-middle">+/-</th>
                <th rowspan="2" class="align-middle">EJ</th>
                <th rowspan="2" class="align-middle">PJ</th>
                <th rowspan="2" class="align-middle">Keterangan</th>
            </tr>
            <tr>
                <th class="bg-primary bg-opacity-10 text-primary">FTE</th>
                <th class="bg-primary bg-opacity-10 text-primary">Pembulatan</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            $current_unit = '';
            foreach($results as $index => $r): 
                $is_new_unit = ($current_unit !== $r['kode_unit']);
                if ($is_new_unit) {
                    $current_unit = $r['kode_unit'];
                }
                
                // Tambahkan jarak/margin visual kosong (spacer) jika ini baris unit baru untuk memisahkan setiap unitnya
                if ($is_new_unit && $index > 0): ?>
                    <tr><td colspan="11" style="height: 15px; background-color: #fff; border-left: none; border-right: none;"></td></tr>
                <?php endif; ?>
                
            <tr>
                <td><?= htmlspecialchars($r['jabatan']->nama_jabatan) ?></td>
                <td class="text-center"><?= $is_new_unit ? htmlspecialchars($r['kode_induk_unit']) : '' ?></td>
                <td class="text-center"><?= $is_new_unit ? htmlspecialchars($r['kode_unit']) : '' ?></td>
                <td><?= $is_new_unit ? htmlspecialchars($r['unit_kantor']) : '' ?></td>
                
                <td class="text-center"><?= $r['fte'] !== null ? number_format($r['fte'], 2, ',', '.') : '-' ?></td>
                <td class="text-center fw-bold"><?= $r['kebutuhan'] !== null ? $r['kebutuhan'] : '-' ?></td>
                <td class="text-center"><?= $r['jumlah_pegawai'] ?></td>
                <td class="text-center fw-bold <?= ($r['selisih'] ?? 0) > 0 ? 'text-primary' : (($r['selisih'] ?? 0) < 0 ? 'text-danger' : '') ?>">
                    <?= $r['selisih'] !== null ? $r['selisih'] : '-' ?>
                </td>
                
                <?php 
                $ej_color = '';
                if ($r['ej'] !== null) {
                    if ($r['ej'] > 1.20) $ej_color = 'text-danger fw-bold';
                    elseif ($r['ej'] >= 1.01) $ej_color = 'text-warning text-dark fw-bold';
                    elseif ($r['ej'] >= 0.80) $ej_color = 'text-success fw-bold';
                    else $ej_color = 'text-info text-dark fw-bold';
                }
                ?>
                <td class="text-center <?= $ej_color ?>"><?= $r['ej'] !== null ? number_format($r['ej'], 2, ',', '.') : '-' ?></td>
                <td class="text-center">
                    <?php if ($r['status'] === 'Normal'): ?>
                        <span class="badge bg-success shadow-sm" style="width: 80px;">Normal</span>
                    <?php elseif ($r['status'] === 'Stretch'): ?>
                        <span class="badge bg-warning text-dark shadow-sm" style="width: 80px;">Stretch</span>
                    <?php elseif ($r['status'] === 'Overload'): ?>
                        <span class="badge bg-danger shadow-sm" style="width: 80px;">Overload</span>
                    <?php elseif ($r['status'] === 'Underload'): ?>
                        <span class="badge bg-info text-dark shadow-sm" style="width: 80px;">Underload</span>
                    <?php else: ?>
                        -
                    <?php endif; ?>
                </td>
                <td class="text-muted" style="white-space: normal; font-size: 0.8rem;"><?= htmlspecialchars($r['ket_status']) ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php elseif($filter_cabang && empty($results)): ?>
    <div class="alert alert-info border-0 shadow-sm glass">Tidak ada data jabatan/pegawai untuk cabang terpilih pada tahun <?= htmlspecialchars($filter_tahun) ?>.</div>
<?php endif; ?>