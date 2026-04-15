<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Profil extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->library(['session', 'upload']);
        if (!$this->session->userdata('logged_in') || $this->session->userdata('user_role') !== 'admin') {
            redirect('auth');
        }
        $this->load->model('User_model');
    }

    public function index() {
        $nip = $this->session->userdata('user_nip');
        
        $this->db->select('user.*, cabang.nama_cabang, unit.nama_unit, jabatan.nama_jabatan');
        $this->db->join('cabang', 'cabang.id_cabang = user.id_cabang', 'left');
        $this->db->join('unit', 'unit.id_unit = user.id_unit', 'left');
        $this->db->join('jabatan', 'jabatan.id_jabatan = user.id_jabatan', 'left');
        $data['user'] = $this->db->where('nip', $nip)->get('user')->row();
        
        $data['user_name'] = $this->session->userdata('user_name'); // Untuk nama di header
        
        // Cek apakah password saat ini sama dengan NIP (Password Default)
        $data['is_default_password'] = password_verify($nip, $data['user']->password);

        $this->load->view('admin/layout/header', $data);
        $this->load->view('admin/layout/sidebar', $data);
        $this->load->view('admin/profil', $data);
        $this->load->view('admin/layout/footer', $data);
    }

    public function update_password() {
        $nip = $this->session->userdata('user_nip');
        $user = $this->User_model->get_by_nip($nip);

        $old_password = $this->input->post('old_password');
        $new_password = $this->input->post('new_password');
        $confirm_password = $this->input->post('confirm_password');

        if (!password_verify($old_password, $user->password)) {
            $this->session->set_flashdata('error_pwd', 'Password lama yang Anda masukkan salah!');
            redirect('admin/profil');
        }
        if ($new_password !== $confirm_password) {
            $this->session->set_flashdata('error_pwd', 'Konfirmasi password baru tidak cocok!');
            redirect('admin/profil');
        }

        $this->db->where('nip', $nip)->update('user', ['password' => password_hash($new_password, PASSWORD_BCRYPT)]);
        $this->session->set_flashdata('success_pwd', 'Password berhasil diperbarui! Gunakan password baru ini untuk login berikutnya.');
        redirect('admin/profil');
    }

    public function upload_foto() {
        $nip = $this->session->userdata('user_nip');
        $config['upload_path']   = './assets/img/profil/';
        $config['allowed_types'] = 'gif|jpg|jpeg|png';
        $config['max_size']      = 3048; // 3MB
        $config['file_name']     = 'foto_' . $nip . '_' . time();
        
        if (!is_dir($config['upload_path'])) mkdir($config['upload_path'], 0777, true);
        $this->upload->initialize($config);

        if (!$this->upload->do_upload('foto')) {
            $this->session->set_flashdata('error_pwd', strip_tags($this->upload->display_errors()));
        } else {
            $old = $this->db->select('foto')->where('nip', $nip)->get('user')->row();
            if (!empty($old->foto) && file_exists($config['upload_path'] . $old->foto)) unlink($config['upload_path'] . $old->foto);
            $this->db->where('nip', $nip)->update('user', ['foto' => $this->upload->data('file_name')]);
            $this->session->set_flashdata('success_pwd', 'Foto profil berhasil diperbarui!');
        }
        redirect('admin/profil');
    }
}
