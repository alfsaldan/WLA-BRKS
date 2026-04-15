<?php
defined('BASEPATH') OR exit('No direct script access allowed');
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class Pegawai extends CI_Controller {
    public function __construct() {
        parent::__construct();
        $this->load->library('session');
        $this->load->model(['Pegawai_model', 'Organisasi_model']);
        if (!$this->session->userdata('logged_in') || $this->session->userdata('user_role') !== 'admin') redirect('auth');
    }

    public function index() {
        $data['pegawai'] = $this->Pegawai_model->get_all();
        $data['cabang']  = $this->Organisasi_model->get_cabang();
        $data['unit']    = $this->Organisasi_model->get_unit();
        $data['jabatan'] = $this->Organisasi_model->get_jabatan();
        $data['user_name'] = $this->session->userdata('user_name');

        $this->load->view('admin/layout/header', $data);
        $this->load->view('admin/layout/sidebar', $data);
        $this->load->view('admin/kelolapegawai', $data);
        $this->load->view('admin/layout/footer', $data);
    }

    public function store() {
        $data = [
            'nip' => $this->input->post('nip'),
            'nama' => $this->input->post('nama'),
            'id_cabang' => $this->input->post('id_cabang'),
            'id_unit' => $this->input->post('id_unit'),
            'id_jabatan' => $this->input->post('id_jabatan'),
            'password' => password_hash($this->input->post('password'), PASSWORD_BCRYPT),
            'role' => 'pegawai',
            'created_at' => date('Y-m-d H:i:s')
        ];
        $this->db->insert('user', $data);
        $this->session->set_flashdata('success', 'Pegawai berhasil ditambahkan');
        redirect('admin/pegawai');
    }

    public function update() {
        $nip = $this->input->post('nip');
        $data = [
            'nama' => $this->input->post('nama'),
            'id_cabang' => $this->input->post('id_cabang'),
            'id_unit' => $this->input->post('id_unit'),
            'id_jabatan' => $this->input->post('id_jabatan')
        ];
        
        if (!empty($this->input->post('password'))) {
            $data['password'] = password_hash($this->input->post('password'), PASSWORD_BCRYPT);
        }

        $this->db->where('nip', $nip)->update('user', $data);
        $this->session->set_flashdata('success', 'Data Pegawai berhasil diperbarui');
        redirect('admin/pegawai');
    }

    public function download_template() {
        $this->load->helper('download');
        // Menggunakan file fisik dari folder yang Anda sebutkan
        $path = FCPATH . 'assets/template/template_pegawai_wla.xlsx';
        if (file_exists($path)) {
            force_download($path, NULL);
        } else {
            $this->session->set_flashdata('error', 'File Template tidak ditemukan di server.');
            redirect('admin/pegawai');
        }
    }

    public function upload_excel() {
        require FCPATH . 'vendor/autoload.php';
        $file_mimes = ['application/octet-stream', 'application/vnd.ms-excel', 'application/x-csv', 'text/x-csv', 'text/csv', 'application/csv', 'application/excel', 'application/vnd.msexcel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'];
        
        if(isset($_FILES['file_excel']['name']) && in_array($_FILES['file_excel']['type'], $file_mimes)) {
            $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReaderForFile($_FILES['file_excel']['tmp_name']);
            $spreadsheet = $reader->load($_FILES['file_excel']['tmp_name']);
            $sheetData = $spreadsheet->getActiveSheet()->toArray();

            // Ambil semua data dari database untuk pencocokan nama
            $cabangs = $this->Organisasi_model->get_cabang();
            $units = $this->Organisasi_model->get_unit();
            $jabatans = $this->Organisasi_model->get_jabatan();
            
            // Ambil list NIP yang sudah ada untuk filter duplikat
            $existing_nips = array_column($this->db->select('nip')->get('user')->result_array(), 'nip');
            
            $success_count = 0;
            $failed_nips = [];

            foreach($sheetData as $key => $row) {
                if($key == 0 || empty(trim($row[0]))) continue; // Skip header / NIP kosong

                $nip = trim($row[0]);
                // Cek Duplikat NIP
                if(in_array($nip, $existing_nips)) {
                    $failed_nips[] = $nip;
                    continue; // Skip inputan baris ini
                }

                $nama = trim($row[1]);
                // Ambil string teks dari excel (huruf kecil semua, hilangkan spasi ganda)
                $cabang_str = strtolower(preg_replace('/\s+/', ' ', trim($row[2])));
                $unit_str = strtolower(preg_replace('/\s+/', ' ', trim($row[3])));
                $jabatan_str = strtolower(preg_replace('/\s+/', ' ', trim($row[4])));
                
                $password = trim($row[5]);
                // Jika password kosong atau bertuliskan note 'default/nip', atur default ke NIP
                if(empty($password) || stripos($password, 'default') !== false || stripos($password, 'nip') !== false) {
                    $password = $nip;
                }

                // Toleransi Typo/Spasi pada Pencocokan (Fuzzy Search dengan threshold > 80% mirip)
                $id_cabang = null;
                foreach($cabangs as $c) {
                    $db_str = strtolower(preg_replace('/\s+/', ' ', trim($c->nama_cabang)));
                    if($db_str == $cabang_str || similar_text($db_str, $cabang_str) >= (strlen($db_str) * 0.8)) { $id_cabang = $c->id_cabang; break; }
                }
                $id_unit = null;
                foreach($units as $u) {
                    $db_str = strtolower(preg_replace('/\s+/', ' ', trim($u->nama_unit)));
                    if($db_str == $unit_str || similar_text($db_str, $unit_str) >= (strlen($db_str) * 0.8)) { $id_unit = $u->id_unit; break; }
                }
                $id_jabatan = null;
                foreach($jabatans as $j) {
                    $db_str = strtolower(preg_replace('/\s+/', ' ', trim($j->nama_jabatan)));
                    if($db_str == $jabatan_str || similar_text($db_str, $jabatan_str) >= (strlen($db_str) * 0.8)) { $id_jabatan = $j->id_jabatan; break; }
                }

                $this->db->insert('user', [
                    'nip' => $nip, 'nama' => $nama,
                    'id_cabang' => $id_cabang, 'id_unit' => $id_unit, 'id_jabatan' => $id_jabatan,
                    'password' => password_hash($password, PASSWORD_BCRYPT), 'role' => 'pegawai',
                    'created_at' => date('Y-m-d H:i:s')
                ]);
                
                $existing_nips[] = $nip; // Daftarkan NIP yang baru masuk ke array referensi (mencegah duplikat di dalam file yg sama)
                $success_count++;
            }

            $msg = "Berhasil mengimport {$success_count} data pegawai.";
            if(count($failed_nips) > 0) {
                $msg .= "<br><br><span class='text-danger'><b>Gagal (NIP sudah terdaftar):</b><br>" . implode(', ', $failed_nips) . "</span>";
                $this->session->set_flashdata('warning', $msg);
            } else {
                $this->session->set_flashdata('success', $msg);
            }
        } else {
            $this->session->set_flashdata('error', 'Format file tidak sesuai!');
        }
        redirect('admin/pegawai');
    }
}