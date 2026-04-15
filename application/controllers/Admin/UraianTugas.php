<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class UraianTugas extends CI_Controller {
    public function __construct() {
        parent::__construct();
        $this->load->library('session');
        $this->load->model(['UraianTugas_model', 'Organisasi_model', 'Pegawai_model', 'BebanKerja_model']);
        if (!$this->session->userdata('logged_in') || $this->session->userdata('user_role') !== 'admin') redirect('auth');
    }

    public function index() {
        $data['cabang'] = $this->Organisasi_model->get_cabang();
        $data['unit'] = $this->Organisasi_model->get_unit();
        $data['jabatan'] = $this->Organisasi_model->get_jabatan();
        $data['user_name'] = $this->session->userdata('user_name');
        
        // Auto detect Periode Sekarang
        $data['filter_tahun'] = $this->input->get('tahun') ?: date('Y');
        $data['filter_bulan'] = $this->input->get('bulan') ?: date('m');
        $data['filter_cabang'] = $this->input->get('id_cabang');
        $data['filter_unit'] = $this->input->get('id_unit');
        $data['filter_jabatan'] = $this->input->get('id_jabatan');

        if ($data['filter_jabatan']) {
            $data['tugas'] = $this->UraianTugas_model->get_by_filter($data['filter_tahun'], $data['filter_bulan'], $data['filter_cabang'], $data['filter_unit'], $data['filter_jabatan']);
            $data['pegawai_list'] = $this->Pegawai_model->get_by_jabatan($data['filter_jabatan']);
            $data['all_volumes'] = $this->BebanKerja_model->get_all_volumes($data['filter_tahun'], $data['filter_bulan']);
        } else {
            $data['tugas'] = [];
            $data['pegawai_list'] = [];
            $data['all_volumes'] = [];
        }

        $this->load->view('admin/layout/header', $data);
        $this->load->view('admin/layout/sidebar', $data);
        $this->load->view('admin/master_uraian_tugas', $data);
        $this->load->view('admin/layout/footer', $data);
    }

    public function store() {
        $id_cabang = $this->input->post('id_cabang');
        $id_unit = $this->input->post('id_unit');
        $id_jabatan = $this->input->post('id_jabatan');
        $tahun = $this->input->post('tahun');
        $bulan_input = $this->input->post('bulan');
        $id_parent_input = empty($this->input->post('id_parent')) ? NULL : $this->input->post('id_parent');
        $nama_tugas = $this->input->post('nama_tugas');
        $output_pekerjaan = empty($this->input->post('output_pekerjaan')) ? NULL : $this->input->post('output_pekerjaan');
        $standar_waktu = $this->input->post('standar_waktu') !== '' ? $this->input->post('standar_waktu') : NULL;
        $keterangan = empty($this->input->post('keterangan')) ? NULL : $this->input->post('keterangan');
        $tindakan_petugas = empty($this->input->post('tindakan_petugas')) ? NULL : $this->input->post('tindakan_petugas');
        $is_active = $this->input->post('is_active');

        // Jika ada parent, cari namanya
        $nama_parent = '';
        if ($id_parent_input) {
            $p_row = $this->db->where('id_tugas', $id_parent_input)->get('wla_uraian_tugas')->row();
            if ($p_row) $nama_parent = strtolower(trim($p_row->nama_tugas));
        }

        // Loop 12 bulan untuk menerapkan Uraian Tugas ke seluruh bulan di tahun tersebut
        for ($i = 1; $i <= 12; $i++) {
            $m = str_pad($i, 2, '0', STR_PAD_LEFT);
            
            // Hindari duplikat
            $cek = $this->db->where([
                'tahun' => $tahun, 'bulan' => $m,
                'id_cabang' => $id_cabang, 'id_unit' => $id_unit, 'id_jabatan' => $id_jabatan,
                'nama_tugas' => $nama_tugas
            ])->get('wla_uraian_tugas')->row();

            if (!$cek) {
                // Cari ID parent di bulan yang bersangkutan
                $current_parent_id = NULL;
                if (!empty($nama_parent)) {
                    $p_cek = $this->db->where([
                        'tahun' => $tahun, 'bulan' => $m,
                        'id_cabang' => $id_cabang, 'id_unit' => $id_unit, 'id_jabatan' => $id_jabatan,
                        'LOWER(TRIM(nama_tugas))' => $nama_parent
                    ])->get('wla_uraian_tugas')->row();
                    if ($p_cek) $current_parent_id = $p_cek->id_tugas;
                }

                $this->UraianTugas_model->insert([
                    'id_cabang' => $id_cabang, 'id_unit' => $id_unit, 'id_jabatan' => $id_jabatan,
                    'tahun' => $tahun, 'bulan' => $m, 'id_parent' => $current_parent_id,
                    'nama_tugas' => $nama_tugas, 'output_pekerjaan' => $output_pekerjaan,
                    'standar_waktu' => $standar_waktu, 'keterangan' => $keterangan,
                    'tindakan_petugas' => $tindakan_petugas, 'is_active' => $is_active
                ]);
            }
        }

        $this->session->set_flashdata('success', 'Master Uraian Tugas berhasil ditambahkan dan otomatis diterapkan ke seluruh bulan.');
        redirect("admin/uraiantugas?tahun={$tahun}&bulan={$bulan_input}&id_cabang={$id_cabang}&id_unit={$id_unit}&id_jabatan={$id_jabatan}");
    }

    public function update() {
        $id = $this->input->post('id_tugas');
        $data = $this->input->post();
        unset($data['id_tugas']); // Hapus ID dari array update
        
        // Format nullable fields
        if(empty($data['id_parent'])) $data['id_parent'] = NULL;
        if($data['standar_waktu'] === '') $data['standar_waktu'] = NULL;
        if(empty($data['tindakan_petugas'])) $data['tindakan_petugas'] = NULL;

        $old_task = $this->db->where('id_tugas', $id)->get('wla_uraian_tugas')->row();

        if ($old_task) {
            // Update semua bulan yang namanya sama dengan old_task agar sinkron
            $this->db->where([
                'tahun' => $old_task->tahun,
                'id_cabang' => $old_task->id_cabang,
                'id_unit' => $old_task->id_unit,
                'id_jabatan' => $old_task->id_jabatan,
                'nama_tugas' => $old_task->nama_tugas
            ])->update('wla_uraian_tugas', [
                'nama_tugas' => $data['nama_tugas'],
                'output_pekerjaan' => $data['output_pekerjaan'],
                'standar_waktu' => $data['standar_waktu'],
                'keterangan' => $data['keterangan'],
                'tindakan_petugas' => $data['tindakan_petugas'],
                'is_active' => $data['is_active']
            ]);
            
            // Khusus untuk bulan saat ini (yang di-edit dari modal), update id_parent-nya juga (karena parent beda id tiap bulan)
            $this->db->where('id_tugas', $id)->update('wla_uraian_tugas', ['id_parent' => $data['id_parent']]);
        }

        $this->session->set_flashdata('success', 'Master Uraian Tugas berhasil diperbarui dan disinkronisasi ke seluruh bulan.');
        redirect($_SERVER['HTTP_REFERER']);
    }

    public function delete($id) {
        // Soft Delete: Hanya merubah is_active menjadi 0
        $this->UraianTugas_model->delete($id);
        $this->session->set_flashdata('success', 'Uraian Tugas dinonaktifkan (Soft Delete).');
        redirect($_SERVER['HTTP_REFERER']);
    }

    public function sync_months() {
        $tahun = $this->input->post('tahun');
        $bulan = $this->input->post('bulan');
        $id_cabang = $this->input->post('id_cabang');
        $id_unit = $this->input->post('id_unit');
        $id_jabatan = $this->input->post('id_jabatan');

        $source_tasks = $this->db->where([
            'tahun' => $tahun, 'bulan' => $bulan,
            'id_cabang' => $id_cabang, 'id_unit' => $id_unit, 'id_jabatan' => $id_jabatan
        ])->get('wla_uraian_tugas')->result();

        if (empty($source_tasks)) {
            $this->session->set_flashdata('error', 'Tidak ada tugas di bulan ini untuk disalin.');
            redirect($_SERVER['HTTP_REFERER']);
        }

        $parents = array_filter($source_tasks, function($t) { return empty($t->id_parent); });
        $children = array_filter($source_tasks, function($t) { return !empty($t->id_parent); });

        $count = 0;

        for ($i = 1; $i <= 12; $i++) {
            $m = str_pad($i, 2, '0', STR_PAD_LEFT);
            if ($m === $bulan) continue;

            $parent_map = [];
            foreach ($parents as $p) {
                $cek = $this->db->where(['tahun' => $tahun, 'bulan' => $m, 'id_cabang' => $id_cabang, 'id_unit' => $id_unit, 'id_jabatan' => $id_jabatan, 'nama_tugas' => $p->nama_tugas])->get('wla_uraian_tugas')->row();
                if (!$cek) {
                    $this->db->insert('wla_uraian_tugas', [
                        'id_cabang' => $id_cabang, 'id_unit' => $id_unit, 'id_jabatan' => $id_jabatan, 'tahun' => $tahun, 'bulan' => $m, 'id_parent' => NULL,
                        'nama_tugas' => $p->nama_tugas, 'output_pekerjaan' => $p->output_pekerjaan, 'standar_waktu' => $p->standar_waktu, 'keterangan' => $p->keterangan,
                        'tindakan_petugas' => $p->tindakan_petugas, 'is_active' => $p->is_active
                    ]);
                    $parent_map[$p->id_tugas] = $this->db->insert_id();
                    $count++;
                } else {
                    $parent_map[$p->id_tugas] = $cek->id_tugas;
                }
            }

            foreach ($children as $c) {
                // Dapatkan ID parent yang benar untuk bulan target dari map yang sudah dibuat
                $new_parent_id = isset($parent_map[$c->id_parent]) ? $parent_map[$c->id_parent] : NULL;

                // Jika parent tidak ditemukan di map, lewati proses untuk anak ini.
                if ($new_parent_id === NULL) continue;

                // Cek apakah anak sudah ada DI BAWAH PARENT YANG BENAR
                $cek = $this->db->where([
                    'tahun' => $tahun, 'bulan' => $m, 'id_cabang' => $id_cabang, 'id_unit' => $id_unit, 'id_jabatan' => $id_jabatan, 
                    'nama_tugas' => $c->nama_tugas,
                    'id_parent' => $new_parent_id
                ])->get('wla_uraian_tugas')->row();

                if (!$cek) {
                    $this->db->insert('wla_uraian_tugas', [
                        'id_cabang' => $id_cabang, 'id_unit' => $id_unit, 'id_jabatan' => $id_jabatan, 'tahun' => $tahun, 'bulan' => $m, 'id_parent' => $new_parent_id,
                        'nama_tugas' => $c->nama_tugas, 'output_pekerjaan' => $c->output_pekerjaan, 'standar_waktu' => $c->standar_waktu, 'keterangan' => $c->keterangan,
                        'tindakan_petugas' => $c->tindakan_petugas, 'is_active' => $c->is_active
                    ]);
                    $count++;
                }
            }
        }

        $this->session->set_flashdata('success', "Berhasil menerapkan data Uraian Tugas ke seluruh bulan di tahun {$tahun}.");
        redirect($_SERVER['HTTP_REFERER']);
    }

    public function import_excel() {
        require FCPATH . 'vendor/autoload.php';
        $file_mimes = ['application/octet-stream', 'application/vnd.ms-excel', 'application/x-csv', 'text/x-csv', 'text/csv', 'application/csv', 'application/excel', 'application/vnd.msexcel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'];
        
        $tahun = $this->input->post('tahun');
        $id_cabang = $this->input->post('id_cabang');
        $id_unit = $this->input->post('id_unit');
        $id_jabatan = $this->input->post('id_jabatan');

        if(isset($_FILES['file_excel']['name']) && in_array($_FILES['file_excel']['type'], $file_mimes)) {
            $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReaderForFile($_FILES['file_excel']['tmp_name']);
            $spreadsheet = $reader->load($_FILES['file_excel']['tmp_name']);
            $sheetData = $spreadsheet->getActiveSheet()->toArray();

            // Auto-detect header columns mapping intelligently
            $header = ['uraian' => 1, 'output' => 2, 'standar' => 16, 'keterangan' => 18];
            foreach($sheetData as $key => $row) {
                if ($key < 4) {
                    foreach($row as $idx => $val) {
                        $v = strtolower(trim((string)$val));
                        if (strpos($v, 'uraian') !== false) $header['uraian'] = $idx;
                        if (strpos($v, 'hasil') !== false || strpos($v, 'output') !== false) $header['output'] = $idx;
                        if (strpos($v, 'standar waktu') !== false || strpos($v, 'waktu penyelesaian') !== false) $header['standar'] = $idx;
                        if (strpos($v, 'keterangan') !== false) $header['keterangan'] = $idx;
                    }
                }
            }

            $success_count = 0;
            $current_parent_id = []; // array mapping untuk 12 bulan

            foreach($sheetData as $key => $row) {
                $no_col = trim((string)($row[0] ?? ''));
                $uraian = trim((string)($row[$header['uraian']] ?? ''));
                $output = trim((string)($row[$header['output']] ?? ''));
                
                // Skip baris kosong atau baris judul Header
                if (empty($uraian) || strtolower($uraian) == 'uraian tugas') continue;

                $standar_waktu = trim((string)($row[$header['standar']] ?? ''));
                if (!is_numeric($standar_waktu)) {
                    if (is_numeric(trim((string)($row[$header['standar'] - 1] ?? '')))) $standar_waktu = trim((string)($row[$header['standar'] - 1]));
                    elseif (is_numeric(trim((string)($row[$header['standar'] + 1] ?? '')))) $standar_waktu = trim((string)($row[$header['standar'] + 1]));
                    else $standar_waktu = NULL;
                }
                
                $keterangan = trim((string)($row[$header['keterangan']] ?? ''));

                // Deteksi Parent/Child dari Penomoran
                $is_parent = is_numeric(preg_replace('/[^0-9]/', '', $no_col)) || (empty($no_col) && empty($current_parent_id)); 

                for ($i = 1; $i <= 12; $i++) {
                    $m = str_pad($i, 2, '0', STR_PAD_LEFT);
                    $cek = $this->db->where(['tahun' => $tahun, 'bulan' => $m, 'id_cabang' => $id_cabang, 'id_unit' => $id_unit, 'id_jabatan' => $id_jabatan, 'nama_tugas' => $uraian])->get('wla_uraian_tugas')->row();

                    if (!$cek) {
                        $this->db->insert('wla_uraian_tugas', [
                            'id_cabang' => $id_cabang, 'id_unit' => $id_unit, 'id_jabatan' => $id_jabatan, 'tahun' => $tahun, 'bulan' => $m, 
                            'id_parent' => $is_parent ? NULL : (isset($current_parent_id[$m]) ? $current_parent_id[$m] : NULL),
                            'nama_tugas' => $uraian, 'output_pekerjaan' => empty($output) ? NULL : $output,
                            'standar_waktu' => $standar_waktu, 'keterangan' => empty($keterangan) ? NULL : $keterangan,
                            'is_active' => 1
                        ]);
                        if ($is_parent) $current_parent_id[$m] = $this->db->insert_id();
                    } else {
                        if ($is_parent) $current_parent_id[$m] = $cek->id_tugas;
                    }
                }
                $success_count++;
            }
            $this->session->set_flashdata('success', "Berhasil mengimport {$success_count} baris Uraian Tugas dan otomatis diterapkan ke seluruh bulan di tahun {$tahun}.");
        } else {
            $this->session->set_flashdata('error', 'Gagal Import: File tidak valid atau format tidak sesuai (.xlsx)');
        }
        redirect($_SERVER['HTTP_REFERER']);
    }
}