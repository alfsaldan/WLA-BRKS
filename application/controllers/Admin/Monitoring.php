<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Monitoring extends CI_Controller {
    public function __construct() {
        parent::__construct();
        $this->load->library('session');
        $this->load->model(['UraianTugas_model', 'Organisasi_model', 'Pegawai_model', 'BebanKerja_model']);
        if (!$this->session->userdata('logged_in') || $this->session->userdata('user_role') !== 'admin') redirect('auth');

        // Auto-add column allowance to jabatan if not exists
        if (!$this->db->field_exists('allowance', 'jabatan')) {
            $this->db->query("ALTER TABLE jabatan ADD allowance DECIMAL(5,2) DEFAULT 0");
        }
    }

    public function index() {
        $data['cabang'] = $this->Organisasi_model->get_cabang();
        $data['unit'] = $this->Organisasi_model->get_unit();
        $data['jabatan'] = $this->Organisasi_model->get_jabatan();
        $data['user_name'] = $this->session->userdata('user_name');
        
        // Ambil filter dari URL (GET)
        $data['filter_tahun'] = $this->input->get('tahun') ?: date('Y');
        $data['filter_bulan'] = $this->input->get('bulan') ?: date('m');
        $data['filter_cabang'] = $this->input->get('id_cabang');
        $data['filter_unit'] = $this->input->get('id_unit');
        $data['filter_jabatan'] = $this->input->get('id_jabatan');

        if ($data['filter_jabatan']) {
            if ($data['filter_bulan'] === 'all') {
                // Ambil semua tugas di tahun tersebut tanpa filter bulan
                $all_tugas = $this->UraianTugas_model->get_by_filter($data['filter_tahun'], 'all', $data['filter_cabang'], $data['filter_unit'], $data['filter_jabatan']);
                
                // Cari bulan terbaru yang memiliki tugas (sebagai template baris laporan)
                $latest_month = '01';
                foreach ($all_tugas as $t) {
                    if ($t->bulan > $latest_month) $latest_month = $t->bulan;
                }
                
                // Filter list tugas agar tidak ada duplikasi baris ke bawah
                $template_tugas = array_filter($all_tugas, function($t) use ($latest_month) {
                    return $t->bulan == $latest_month;
                });
                $data['tugas'] = array_values($template_tugas);
                
                // Ambil semua volume yang pernah diinput pegawai di tahun tersebut
                $raw_volumes = $this->BebanKerja_model->get_all_volumes($data['filter_tahun'], 'all');
                
                // Mapping id_tugas dari bulan lama ke id_tugas template berdasarkan Nama Tugas
                $map_id_to_name = [];
                foreach ($all_tugas as $t) {
                    $map_id_to_name[$t->id_tugas] = strtolower(trim($t->nama_tugas));
                }
                $map_name_to_template_id = [];
                foreach ($data['tugas'] as $t) {
                    $map_name_to_template_id[strtolower(trim($t->nama_tugas))] = $t->id_tugas;
                }
                
                // Jumlahkan (Akumulasi) seluruh volume tersebut
                $agg_volumes = [];
                foreach ($raw_volumes as $nip => $vols) {
                    foreach ($vols as $id_tugas => $vol) {
                        if (isset($map_id_to_name[$id_tugas]) && isset($map_name_to_template_id[$map_id_to_name[$id_tugas]])) {
                            $template_id = $map_name_to_template_id[$map_id_to_name[$id_tugas]];
                            if (!isset($agg_volumes[$nip][$template_id])) $agg_volumes[$nip][$template_id] = 0;
                            $agg_volumes[$nip][$template_id] += $vol;
                        }
                    }
                }
                $data['all_volumes'] = $agg_volumes;
            } else {
                $data['tugas'] = $this->UraianTugas_model->get_by_filter($data['filter_tahun'], $data['filter_bulan'], $data['filter_cabang'], $data['filter_unit'], $data['filter_jabatan']);
                $data['all_volumes'] = $this->BebanKerja_model->get_all_volumes($data['filter_tahun'], $data['filter_bulan']);
            }
            $data['pegawai_list'] = $this->Pegawai_model->get_by_jabatan($data['filter_jabatan']);
        } else {
            $data['tugas'] = [];
            $data['pegawai_list'] = [];
            $data['all_volumes'] = [];
        }

        $this->load->view('admin/layout/header', $data);
        $this->load->view('admin/layout/sidebar', $data);
        $this->load->view('admin/monitoringwla', $data);
        $this->load->view('admin/layout/footer', $data);
    }

    public function individu() {
        $data['user_name'] = $this->session->userdata('user_name');
        $data['filter_tahun'] = $this->input->get('tahun') ?: date('Y');
        $data['filter_nip'] = $this->input->get('nip');

        if ($data['filter_nip']) {
            // Cek apakah NIP ditemukan
            $pegawai = $this->db->get_where('user', ['nip' => $data['filter_nip']])->row();
            $data['pegawai'] = $pegawai;

            if ($pegawai && $pegawai->id_jabatan) {
                // Ambil Info Jabatan
                $this->db->select('jabatan.*, unit.nama_unit, cabang.nama_cabang');
                $this->db->from('jabatan');
                $this->db->join('unit', 'unit.id_unit = jabatan.id_unit', 'left');
                $this->db->join('cabang', 'cabang.id_cabang = unit.id_cabang', 'left');
                $this->db->where('id_jabatan', $pegawai->id_jabatan);
                $data['jabatan_info'] = $this->db->get()->row();

                // Ambil Template Master Tugas (dari keseluruhan tahun tersebut)
                $all_tugas = $this->UraianTugas_model->get_by_filter($data['filter_tahun'], 'all', $pegawai->id_cabang, $pegawai->id_unit, $pegawai->id_jabatan);
                $latest_month = '01';
                foreach ($all_tugas as $t) { if ($t->bulan > $latest_month) $latest_month = $t->bulan; }
                $template_tugas = array_filter($all_tugas, function($t) use ($latest_month) { return $t->bulan == $latest_month; });
                $data['tugas'] = array_values($template_tugas);
                
                // Ambil Database Volume setahun khusus untuk NIP ini
                $raw_volumes = $this->db->where(['nip' => $data['filter_nip'], 'tahun' => $data['filter_tahun']])->get('wla_beban_kerja')->result();
                
                // Mapping Volume per Bulan (1 - 12)
                $map_id_to_name = [];
                foreach ($all_tugas as $t) { $map_id_to_name[$t->id_tugas] = strtolower(trim($t->nama_tugas)); }
                $map_name_to_template_id = [];
                foreach ($data['tugas'] as $t) { $map_name_to_template_id[strtolower(trim($t->nama_tugas))] = $t->id_tugas; }
                
                $monthly_volumes = [];
                foreach ($raw_volumes as $v) {
                    if (isset($map_id_to_name[$v->id_tugas]) && isset($map_name_to_template_id[$map_id_to_name[$v->id_tugas]])) {
                        $template_id = $map_name_to_template_id[$map_id_to_name[$v->id_tugas]];
                        $bulan_int = (int)$v->bulan;
                        if (!isset($monthly_volumes[$template_id][$bulan_int])) $monthly_volumes[$template_id][$bulan_int] = 0;
                        $monthly_volumes[$template_id][$bulan_int] += $v->volume;
                    }
                }
                $data['monthly_volumes'] = $monthly_volumes;
                $data['allowance_pct'] = isset($data['jabatan_info']->allowance) ? (float)$data['jabatan_info']->allowance : 0;
            }
        }

        $this->load->view('admin/layout/header', $data);
        $this->load->view('admin/layout/sidebar', $data);
        $this->load->view('admin/monitoring_individu', $data);
        $this->load->view('admin/layout/footer', $data);
    }

    public function hasil() {
        $data['user_name'] = $this->session->userdata('user_name');
        $data['cabang'] = $this->Organisasi_model->get_cabang();
        $data['filter_tahun'] = $this->input->get('tahun') ?: date('Y');
        $data['filter_cabang'] = $this->input->get('id_cabang');

        if ($data['filter_cabang']) {
            $wke_default = 9888;
            $id_cabang = $data['filter_cabang'];
            $tahun = $data['filter_tahun'];

            // 1. Ambil semua jabatan & pegawai di cabang terpilih
            $units_in_cabang = $this->db->where('id_cabang', $id_cabang)->get('unit')->result();
            $unit_ids = array_column($units_in_cabang, 'id_unit');
            
            $jabatans = [];
            if(!empty($unit_ids)) {
                $this->db->select('j.*, u.nama_unit, u.kode_unit, c.nama_cabang, c.kode_cabang');
                $this->db->from('jabatan j');
                $this->db->join('unit u', 'u.id_unit = j.id_unit');
                $this->db->join('cabang c', 'c.id_cabang = u.id_cabang');
                $this->db->where_in('j.id_unit', $unit_ids);
                $this->db->order_by('u.kode_unit', 'ASC');
                $this->db->order_by('j.id_jabatan', 'ASC');
                $jabatans = $this->db->get()->result();
            }
            
            $jabatan_ids = array_column($jabatans, 'id_jabatan');
            $pegawais = !empty($jabatan_ids) ? $this->db->where_in('id_jabatan', $jabatan_ids)->get('user')->result() : [];
            $pegawai_by_jabatan = [];
            foreach($pegawais as $p) { $pegawai_by_jabatan[$p->id_jabatan][] = $p; }

            // 2. Ambil semua tugas & volume di tahun tersebut untuk jabatan-jabatan terkait
            $all_tugas_year = !empty($jabatan_ids) ? $this->db->where('tahun', $tahun)->where_in('id_jabatan', $jabatan_ids)->get('wla_uraian_tugas')->result() : [];
            $all_volumes_year = !empty($jabatan_ids) ? $this->db->select('b.nip, b.id_tugas, b.volume, t.id_jabatan')->from('wla_beban_kerja b')->join('wla_uraian_tugas t', 't.id_tugas = b.id_tugas')->where('b.tahun', $tahun)->where_in('t.id_jabatan', $jabatan_ids)->get()->result() : [];

            $volumes_by_nip_tugas = [];
            foreach($all_volumes_year as $v) { $volumes_by_nip_tugas[$v->nip][$v->id_tugas] = $v->volume; }

            // 3. Proses kalkulasi untuk setiap jabatan
            $results = [];
            foreach($jabatans as $j) {
                $pegawai_list = $pegawai_by_jabatan[$j->id_jabatan] ?? [];
                $emp_count = count($pegawai_list) > 0 ? count($pegawai_list) : 1;
                $nips = array_column($pegawai_list, 'nip');

                $tugas_jabatan = array_filter($all_tugas_year, function($t) use ($j) { return $t->id_jabatan == $j->id_jabatan; });

                // Buat template tugas dari bulan terbaru & mapping ID
                $total_rpm = 0;
                if (!empty($tugas_jabatan)) {
                    $latest_month = '01';
                    foreach ($tugas_jabatan as $t) { if ($t->bulan > $latest_month) $latest_month = $t->bulan; }
                    $template_tugas = array_values(array_filter($tugas_jabatan, function($t) use ($latest_month) { return $t->bulan == $latest_month; }));
                    
                    $map_name_to_template_id = [];
                    foreach($template_tugas as $tt) { $map_name_to_template_id[strtolower(trim($tt->nama_tugas))] = $tt->id_tugas; }
                    $map_id_to_name = [];
                    foreach($tugas_jabatan as $t) { $map_id_to_name[$t->id_tugas] = strtolower(trim($t->nama_tugas)); }

                    // Akumulasi volume tahunan per template tugas
                    $yearly_volume_per_template = [];
                    foreach($nips as $nip) {
                        foreach($volumes_by_nip_tugas[$nip] ?? [] as $id_tugas => $vol) {
                            if (isset($map_id_to_name[$id_tugas]) && isset($map_name_to_template_id[$map_id_to_name[$id_tugas]])) {
                                $template_id = $map_name_to_template_id[$map_id_to_name[$id_tugas]];
                                if(!isset($yearly_volume_per_template[$template_id])) $yearly_volume_per_template[$template_id] = 0;
                                $yearly_volume_per_template[$template_id] += $vol;
                            }
                        }
                    }

                    foreach($template_tugas as $tt) {
                        $total_vol_task = $yearly_volume_per_template[$tt->id_tugas] ?? 0;
                        $avg_monthly_vol_per_emp = ($total_vol_task / 12) / $emp_count;
                        $rata_rata_transaksi = ceil($avg_monthly_vol_per_emp);
                        $total_rpm += $rata_rata_transaksi * (float)$tt->standar_waktu;
                    }
                }

                // Hitung FTE dan status
                $allowance_pct = (float)($j->allowance ?? 0);
                $fte = ($total_rpm * (1 + $allowance_pct / 100)) / $wke_default;
                $kebutuhan = round($fte);
                $ej = ($emp_count > 0) ? $fte / $emp_count : 0;

                if (empty($tugas_jabatan)) {
                    $status = '-';
                    $ket_status = '-';
                    $fte = null;
                    $kebutuhan = null;
                    $ej = null;
                } else {
                    $status = 'Normal'; $ket_status = 'Ideal';
                    if ($ej > 1.20) { $status = 'Overload'; $ket_status = 'Perlu penambahan SDM atau review proses bisnis'; }
                    elseif ($ej >= 1.01) { $status = 'Stretch'; $ket_status = 'Bisa diatasi dengan manajemen waktu atau pelatihan efisiensi'; }
                    elseif ($ej < 0.80) { $status = 'Underload'; $ket_status = 'Alihkan sebagian tugas lain atau review distribusi kerja'; }
                }

                $results[] = [
                    'jabatan' => $j,
                    'fte' => $fte,
                    'kebutuhan' => $kebutuhan,
                    'jumlah_pegawai' => count($pegawai_list),
                    'selisih' => ($kebutuhan !== null) ? count($pegawai_list) - $kebutuhan : null,
                    'ej' => $ej,
                    'status' => $status,
                    'ket_status' => $ket_status,
                    'kode_induk_unit' => $j->kode_cabang,
                    'kode_unit' => $j->kode_unit,
                    'unit_kantor' => $j->nama_unit
                ];
            }
            $data['results'] = $results;

            // 4. Buat data untuk tabel summary
            $summary = [];
            foreach($results as $r) {
                $nama_jab = $r['jabatan']->nama_jabatan;
                if(!isset($summary[$nama_jab])) $summary[$nama_jab] = ['Overload'=>0, 'Stretch'=>0, 'Normal'=>0, 'Underload'=>0, '-'=>0, 'Total'=>0];
                $summary[$nama_jab][$r['status']]++;
                $summary[$nama_jab]['Total']++;
            }
            $data['summary'] = $summary;
        }

        $this->load->view('admin/layout/header', $data);
        $this->load->view('admin/layout/sidebar', $data);
        $this->load->view('admin/wla_hasil', $data);
        $this->load->view('admin/layout/footer', $data);
    }
}