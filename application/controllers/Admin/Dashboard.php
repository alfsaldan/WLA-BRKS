<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Dashboard extends CI_Controller {

    public function __construct()
    {
        parent::__construct();
        $this->load->library('session');
        $this->load->helper('url');
        $this->load->model('User_model');

        // simple auth: only allow logged in users
        if (!$this->session->userdata('logged_in')) {
            redirect('auth');
        }

        // Role protection: Only admin can access this controller
        if ($this->session->userdata('user_role') !== 'admin') {
            redirect('pegawai'); // Lempar kembali ke area pegawai jika bukan admin
        }
    }

    public function index()
    {
        $data = [];
        $data['user_name'] = $this->session->userdata('user_name');

        // Setup Periode Bulan Ini
        $tahun = date('Y');
        $bulan = date('m');
        $data['tahun'] = $tahun;
        $bulan_indo = ['', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
        $data['bulan_nama'] = $bulan_indo[(int)$bulan];

        // Summary Stats
        $data['total_pegawai'] = $this->db->where('role', 'pegawai')->count_all_results('user');
        $data['total_jabatan'] = $this->db->count_all_results('jabatan');
        $data['total_unit'] = $this->db->count_all_results('unit');
        
        $vol_bulan = $this->db->select_sum('volume')->where('tahun', $tahun)->where('bulan', $bulan)->get('wla_beban_kerja')->row();
        $data['total_volume_bulan'] = $vol_bulan->volume ? $vol_bulan->volume : 0;

        // Chart 1: Tren Volume Pekerjaan (Line Chart)
        $chart_tren_labels = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Ags', 'Sep', 'Okt', 'Nov', 'Des'];
        $chart_tren_data = array_fill(0, 12, 0);
        if ($this->db->table_exists('wla_beban_kerja')) {
            $tren_data = $this->db->select('bulan, SUM(volume) as total')
                                  ->where('tahun', $tahun)
                                  ->group_by('bulan')
                                  ->get('wla_beban_kerja')->result();
            foreach($tren_data as $td) {
                $chart_tren_data[(int)$td->bulan - 1] = (float)$td->total;
            }
        }
        $data['chart_tren_labels'] = json_encode($chart_tren_labels);
        $data['chart_tren_data'] = json_encode($chart_tren_data);

        // === Kalkulasi WLA Global untuk Ringkasan Dashboard ===
        $wke_default = 9888; // WKE Tahunan
        $all_jabatan = $this->db->get('jabatan')->result();
        $jabatan_ids = array_column($all_jabatan, 'id_jabatan');
        
        $pegawais = !empty($jabatan_ids) ? $this->db->where_in('id_jabatan', $jabatan_ids)->get('user')->result() : [];
        $pegawai_by_jabatan = [];
        foreach($pegawais as $p) { $pegawai_by_jabatan[$p->id_jabatan][] = $p; }

        $all_tugas_year = !empty($jabatan_ids) ? $this->db->where('tahun', $tahun)->where_in('id_jabatan', $jabatan_ids)->get('wla_uraian_tugas')->result() : [];
        $all_volumes_year = !empty($jabatan_ids) ? $this->db->select('b.nip, b.id_tugas, SUM(b.volume) as total_volume, t.id_jabatan')->from('wla_beban_kerja b')->join('wla_uraian_tugas t', 't.id_tugas = b.id_tugas')->where('b.tahun', $tahun)->where_in('t.id_jabatan', $jabatan_ids)->group_by('b.nip, b.id_tugas, t.id_jabatan')->get()->result() : [];
        
        $volumes_by_nip_tugas = [];
        foreach($all_volumes_year as $v) { $volumes_by_nip_tugas[$v->nip][$v->id_tugas] = $v->total_volume; }

        $wla_results = [];
        foreach($all_jabatan as $j) {
            $pegawai_list = $pegawai_by_jabatan[$j->id_jabatan] ?? [];
            $emp_count = count($pegawai_list) > 0 ? count($pegawai_list) : 1;
            $nips = array_column($pegawai_list, 'nip');
            $tugas_jabatan = array_filter($all_tugas_year, function($t) use ($j) { return $t->id_jabatan == $j->id_jabatan; });

            $total_rpm_yearly = 0;
            if (!empty($tugas_jabatan)) {
                $latest_month = '01';
                foreach ($tugas_jabatan as $t) { if ($t->bulan > $latest_month) $latest_month = $t->bulan; }
                $template_tugas = array_values(array_filter($tugas_jabatan, function($t) use ($latest_month) { return $t->bulan == $latest_month; }));
                $map_name_to_template_id = [];
                foreach($template_tugas as $tt) { $map_name_to_template_id[strtolower(trim($tt->nama_tugas))] = $tt->id_tugas; }
                $map_id_to_name = [];
                foreach($tugas_jabatan as $t) { $map_id_to_name[$t->id_tugas] = strtolower(trim($t->nama_tugas)); }

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
                    $total_vol_task_yearly = $yearly_volume_per_template[$tt->id_tugas] ?? 0;
                    $total_rpm_yearly += $total_vol_task_yearly * (float)$tt->standar_waktu;
                }
            }

            $allowance_pct = (float)($j->allowance ?? 0);
            $fte = ($total_rpm_yearly * (1 + $allowance_pct / 100)) / $wke_default;
            $ej = ($emp_count > 0) ? $fte / $emp_count : 0;

            $status = 'Normal';
            if ($ej > 1.20) { $status = 'Overload'; }
            elseif ($ej >= 1.01) { $status = 'Stretch'; }
            elseif ($ej < 0.80 && $fte > 0) { $status = 'Underload'; }
            elseif ($fte == 0) { $status = 'N/A'; }

            $wla_results[] = ['nama_jabatan' => $j->nama_jabatan, 'ej' => $ej, 'status' => $status, 'fte' => $fte, 'pegawai' => count($pegawai_list)];
        }

        // Proses hasil WLA untuk chart dan list
        $wla_status_counts = ['Overload' => 0, 'Stretch' => 0, 'Normal' => 0, 'Underload' => 0];
        foreach($wla_results as $r) { if (isset($wla_status_counts[$r['status']])) $wla_status_counts[$r['status']]++; }
        
        usort($wla_results, function($a, $b) { return $b['ej'] <=> $a['ej']; });
        $top_overload = array_filter($wla_results, function($r) { return $r['status'] == 'Overload' || $r['status'] == 'Stretch'; });
        $top_underload = array_filter($wla_results, function($r) { return $r['status'] == 'Underload'; });
        usort($top_underload, function($a, $b) { return $a['ej'] <=> $b['ej']; });

        $data['wla_summary_labels'] = json_encode(array_keys($wla_status_counts));
        $data['wla_summary_data'] = json_encode(array_values($wla_status_counts));
        $data['top_overload'] = array_slice($top_overload, 0, 5);
        $data['top_underload'] = array_slice($top_underload, 0, 5);

        // Kepatuhan Input
        $pegawai_input = $this->db->select('COUNT(DISTINCT nip) as total')->where('tahun', $tahun)->where('bulan', $bulan)->get('wla_beban_kerja')->row()->total;
        $pegawai_total = $data['total_pegawai'] > 0 ? $data['total_pegawai'] : 1;
        $data['kepatuhan_persen'] = round(($pegawai_input / $pegawai_total) * 100);

        // Daftar Pegawai Belum Input
        $subquery = $this->db->select('DISTINCT(nip)')->where('tahun', $tahun)->where('bulan', $bulan)->get_compiled_select('wla_beban_kerja');
        $data['pegawai_belum_input_list'] = $this->db
            ->select('nip, nama')
            ->from('user')
            ->where('role', 'pegawai')
            ->where("nip NOT IN ($subquery)", NULL, FALSE)
            ->order_by('nama', 'ASC')
            ->get()
            ->result();

        $this->load->view('admin/layout/header', $data);
        $this->load->view('admin/layout/sidebar', $data);
        $this->load->view('admin/dashboard', $data);
        $this->load->view('admin/layout/footer', $data);
    }

}
