<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class KelolaRumus extends CI_Controller {
    public function __construct() {
        parent::__construct();
        $this->load->library('session');
        if (!$this->session->userdata('logged_in') || $this->session->userdata('user_role') !== 'admin') redirect('auth');
        
        // Auto-add column allowance to jabatan if not exists
        if (!$this->db->field_exists('allowance', 'jabatan')) {
            $this->db->query("ALTER TABLE jabatan ADD allowance DECIMAL(5,2) DEFAULT 0");
        }
    }

    public function index() {
        $this->db->select('TRIM(nama_jabatan) as nama_jabatan, MAX(allowance) as allowance');
        $this->db->from('jabatan');
        $this->db->group_by('TRIM(nama_jabatan)');
        $data['jabatan'] = $this->db->get()->result();
        $data['user_name'] = $this->session->userdata('user_name');

        $this->load->view('admin/layout/header', $data);
        $this->load->view('admin/layout/sidebar', $data);
        $this->load->view('admin/kelolarumus', $data);
        $this->load->view('admin/layout/footer', $data);
    }

    public function update_bulk() {
        $namas = $this->input->post('nama_jabatan');
        $allowances = $this->input->post('allowance');
        
        if (!empty($namas) && !empty($allowances)) {
            for ($i = 0; $i < count($namas); $i++) {
                $nama_jabatan = trim($namas[$i]);
                $val = str_replace(',', '.', $allowances[$i]);
                if ($val === '') $val = 0;
                $this->db->where('TRIM(nama_jabatan)', $nama_jabatan)->update('jabatan', ['allowance' => $val]);
            }
            $this->session->set_flashdata('success', 'Rumus Allowance berhasil diperbarui dan diterapkan ke seluruh cabang/unit');
        }
        redirect('admin/kelolarumus');
    }
}