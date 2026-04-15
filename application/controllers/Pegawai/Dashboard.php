<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Dashboard extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->library('session');
        if (!$this->session->userdata('logged_in') || $this->session->userdata('user_role') !== 'pegawai') {
            redirect('auth');
        }
        $this->load->model(['User_model', 'UraianTugas_model', 'BebanKerja_model']);
    }

    public function index() {
        $nip = $this->session->userdata('user_nip');
        
        // Ambil data detail pegawai
        $this->db->select('user.*, cabang.nama_cabang, unit.nama_unit, jabatan.nama_jabatan');
        $this->db->join('cabang', 'cabang.id_cabang = user.id_cabang', 'left');
        $this->db->join('unit', 'unit.id_unit = user.id_unit', 'left');
        $this->db->join('jabatan', 'jabatan.id_jabatan = user.id_jabatan', 'left');
        $user = $this->db->where('nip', $nip)->get('user')->row();
        
        $data['user'] = $user;
        $data['filter_tahun'] = $this->input->get('tahun') ?: date('Y');
        $data['filter_bulan'] = $this->input->get('bulan') ?: date('m');

        // Ambil Uraian Tugas khusus untuk jabatan pegawai ini
        $data['tugas'] = [];
        if($user && $user->id_jabatan) {
             $data['tugas'] = $this->UraianTugas_model->get_by_filter($data['filter_tahun'], $data['filter_bulan'], $user->id_cabang, $user->id_unit, $user->id_jabatan);
        }
        $data['volumes'] = $this->BebanKerja_model->get_volume($nip, $data['filter_tahun'], $data['filter_bulan']);

        // --- LOGIKA DASHBOARD METRICS ---
        $parents = array_filter($data['tugas'], function($t) { return empty($t->id_parent); });
        $children = array_filter($data['tugas'], function($t) { return !empty($t->id_parent); });
        $has_children_array = array_column($children, 'id_parent');
        
        $fillable_tasks = 0;
        $filled_tasks = 0;
        $total_volume_month = 0;

        foreach($data['tugas'] as $t) {
            if(empty($t->id_parent) && in_array($t->id_tugas, $has_children_array)) continue; // Tugas Induk yang punya anak tidak dihitung
            $fillable_tasks++;
            $vol = isset($data['volumes'][$t->id_tugas]) ? (float)$data['volumes'][$t->id_tugas] : 0;
            if($vol > 0) $filled_tasks++;
            $total_volume_month += $vol;
        }
        $data['total_tasks'] = $fillable_tasks;
        $data['filled_tasks'] = $filled_tasks;
        $data['total_volume_month'] = $total_volume_month;

        // --- LOGIKA GRAFIK TREN (CHART) ---
        $yearly_volumes = $this->db->select('bulan, SUM(volume) as total_vol')->where('nip', $nip)->where('tahun', $data['filter_tahun'])->group_by('bulan')->get('wla_beban_kerja')->result();
        $chart_data = array_fill(0, 12, 0); // Array bulan 0-11
        foreach($yearly_volumes as $yv) { $chart_data[(int)$yv->bulan - 1] = (float)$yv->total_vol; }
        $data['chart_data'] = json_encode($chart_data);

        $this->load->view('pegawai/layout/header', $data);
        $this->load->view('pegawai/index', $data);
        $this->load->view('pegawai/layout/footer', $data);
    }

    public function save() {
        $nip = $this->session->userdata('user_nip');
        $tahun = $this->input->post('tahun');
        $bulan = $this->input->post('bulan');
        $volumes = $this->input->post('volume'); // Array volume dari form

        if($volumes && is_array($volumes)) {
            foreach($volumes as $id_tugas => $vol) {
                $this->BebanKerja_model->save_volume($nip, $tahun, $bulan, $id_tugas, $vol === '' ? 0 : $vol);
            }
        }
        $this->session->set_flashdata('success', 'Data Volume Transaksi berhasil disimpan!');
        redirect("pegawai/dashboard?tahun={$tahun}&bulan={$bulan}");
    }
}