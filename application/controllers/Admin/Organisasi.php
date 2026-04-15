<?php
defined('BASEPATH') OR exit('No direct script access allowed');
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class Organisasi extends CI_Controller {
    public function __construct() {
        parent::__construct();
        $this->load->library('session');
        $this->load->model('Organisasi_model');
        if (!$this->session->userdata('logged_in') || $this->session->userdata('user_role') !== 'admin') {
            redirect('auth');
        }
    }

    public function index() {
        $data['cabang'] = $this->Organisasi_model->get_cabang();
        $data['unit'] = $this->Organisasi_model->get_unit();
        $data['jabatan'] = $this->Organisasi_model->get_jabatan();
        $data['user_name'] = $this->session->userdata('user_name');

        $this->load->view('admin/layout/header', $data);
        $this->load->view('admin/layout/sidebar', $data);
        $this->load->view('admin/kelolaorganisasi', $data);
        $this->load->view('admin/layout/footer', $data);
    }

    public function store_cabang() {
        $this->Organisasi_model->insert('cabang', ['kode_cabang' => $this->input->post('kode'), 'nama_cabang' => $this->input->post('nama')]);
        $this->session->set_flashdata('success', 'Cabang berhasil ditambahkan');
        redirect('admin/organisasi');
    }

    public function store_unit() {
        $this->Organisasi_model->insert('unit', [
            'id_cabang' => $this->input->post('id_cabang'),
            'kode_unit' => $this->input->post('kode'),
            'nama_unit' => $this->input->post('nama')
        ]);
        $this->session->set_flashdata('success', 'Unit Kerja berhasil ditambahkan');
        redirect('admin/organisasi');
    }

    public function store_jabatan() {
        $this->Organisasi_model->insert('jabatan', [
            'id_unit' => $this->input->post('id_unit'),
            'nama_jabatan' => $this->input->post('nama')
        ]);
        $this->session->set_flashdata('success', 'Jabatan berhasil ditambahkan');
        redirect('admin/organisasi');
    }

    public function update_cabang() {
        $id = $this->input->post('id_cabang');
        $this->Organisasi_model->update('cabang', ['kode_cabang' => $this->input->post('kode'), 'nama_cabang' => $this->input->post('nama')], ['id_cabang' => $id]);
        $this->session->set_flashdata('success', 'Cabang berhasil diperbarui');
        redirect('admin/organisasi');
    }

    public function update_unit() {
        $id = $this->input->post('id_unit');
        $this->Organisasi_model->update('unit', [
            'id_cabang' => $this->input->post('id_cabang'),
            'kode_unit' => $this->input->post('kode'),
            'nama_unit' => $this->input->post('nama')
        ], ['id_unit' => $id]);
        $this->session->set_flashdata('success', 'Unit Kerja berhasil diperbarui');
        redirect('admin/organisasi');
    }

    public function update_jabatan() {
        $id = $this->input->post('id_jabatan');
        $this->Organisasi_model->update('jabatan', [
            'id_unit' => $this->input->post('id_unit'),
            'nama_jabatan' => $this->input->post('nama')
        ], ['id_jabatan' => $id]);
        $this->session->set_flashdata('success', 'Jabatan berhasil diperbarui');
        redirect('admin/organisasi');
    }

    // Endpoint AJAX untuk Relasi Dropdown
    public function ajax_get_unit() {
        echo json_encode($this->Organisasi_model->get_unit_by_cabang($this->input->post('id_cabang')));
    }
    
    public function ajax_get_jabatan() {
        echo json_encode($this->Organisasi_model->get_jabatan_by_unit($this->input->post('id_unit')));
    }

    public function import_excel() {
        require FCPATH . 'vendor/autoload.php';
        $file_mimes = ['application/octet-stream', 'application/vnd.ms-excel', 'application/x-csv', 'text/x-csv', 'text/csv', 'application/csv', 'application/excel', 'application/vnd.msexcel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'];
        
        if(isset($_FILES['file_excel']['name']) && in_array($_FILES['file_excel']['type'], $file_mimes)) {
            $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReaderForFile($_FILES['file_excel']['tmp_name']);
            $spreadsheet = $reader->load($_FILES['file_excel']['tmp_name']);
            $sheetData = $spreadsheet->getActiveSheet()->toArray();

            $current_id_cabang = null;
            $current_kode_cabang = '';
            $current_id_unit = null;
            $current_kode_unit = '';
            $current_nama_unit = '';
            $success_count = 0;

            $this->db->trans_start(); // Mulai transaksi database

            foreach($sheetData as $key => $row) {
                if($key == 0) continue; // Skip header (Baris ke-1)

                $kode_cabang = trim((string)($row[0] ?? ''));
                $kode_unit   = trim((string)($row[1] ?? ''));
                $unit_kantor = trim((string)($row[2] ?? ''));
                $unit_kerja  = trim((string)($row[3] ?? ''));
                $jabatan     = trim((string)($row[5] ?? '')); // Kolom F

                if(empty($jabatan) && empty($kode_cabang) && empty($kode_unit)) continue;

                // 1. PROSES CABANG / INDUK (Jika Kode Cabang di Excel ada isinya)
                if(!empty($kode_cabang)) {
                    $current_kode_cabang = $kode_cabang;
                    $nama_cabang = !empty($unit_kantor) ? $unit_kantor : "Cabang " . $kode_cabang;
                    // Cek apakah Cabang/Induk sudah ada
                    $cek_cabang = $this->db->get_where('cabang', ['kode_cabang' => $kode_cabang])->row();
                    if(!$cek_cabang) {
                        $this->db->insert('cabang', ['kode_cabang' => $kode_cabang, 'nama_cabang' => $nama_cabang]);
                        $current_id_cabang = $this->db->insert_id();
                    } else {
                        $current_id_cabang = $cek_cabang->id_cabang;
                    }
                }

                // 2. PROSES UNIT / SUB-INDUK
                if(!empty($kode_unit)) {
                    $current_kode_unit = $kode_unit;
                    if($current_kode_unit === $current_kode_cabang) {
                        $current_nama_unit = !empty($unit_kerja) ? $unit_kerja : $unit_kantor;
                    } else {
                        $current_nama_unit = !empty($unit_kantor) ? $unit_kantor : 'Sub Unit ' . $current_kode_unit;
                    }
                    
                    $cek_unit = $this->db->get_where('unit', ['kode_unit' => $current_kode_unit, 'id_cabang' => $current_id_cabang])->row();
                    if(!$cek_unit) {
                        $this->db->insert('unit', ['id_cabang' => $current_id_cabang, 'kode_unit' => $current_kode_unit, 'nama_unit' => $current_nama_unit]);
                        $current_id_unit = $this->db->insert_id();
                    } else {
                        $current_id_unit = $cek_unit->id_unit;
                    }
                }

                if(empty($jabatan)) continue;

                // 3. PROSES JABATAN
                $cek_jabatan = $this->db->get_where('jabatan', ['id_unit' => $current_id_unit, 'nama_jabatan' => $jabatan])->row();
                if(!$cek_jabatan) {
                    $this->db->insert('jabatan', [
                        'id_unit' => $current_id_unit,
                        'nama_jabatan' => $jabatan
                    ]);
                    $success_count++;
                }
            }

            $this->db->trans_complete(); // Selesaikan transaksi

            if ($this->db->trans_status() === FALSE) {
                $this->session->set_flashdata('error', 'Terjadi kesalahan sistem saat menyimpan data ke database.');
            } else {
                $this->session->set_flashdata('success', "Berhasil memproses dan menambahkan {$success_count} Jabatan beserta struktur organisasinya.");
            }
        } else {
            $this->session->set_flashdata('error', 'Format file tidak sesuai! Pastikan Anda mengupload .xlsx');
        }
        redirect('admin/organisasi');
    }
}