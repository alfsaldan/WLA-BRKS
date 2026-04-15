<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class User extends CI_Controller {

    public function __construct()
    {
        parent::__construct();
        $this->load->library(['session', 'form_validation']);
        $this->load->helper('url');
        $this->load->model('User_model');

        if (!$this->session->userdata('logged_in')) {
            redirect('auth');
        }

        if ($this->session->userdata('user_role') !== 'admin') {
            redirect('pegawai');
        }
    }

    public function index()
    {
        $data['users'] = $this->User_model->get_all();
        $data['user_name'] = $this->session->userdata('user_name');

        $this->load->view('admin/layout/header', $data);
        $this->load->view('admin/layout/sidebar', $data);
        $this->load->view('admin/kelolauser', $data);
        $this->load->view('admin/layout/footer', $data);
    }

    public function store()
    {
        // Validasi form: NIP harus 6 digit angka dan Unik
        $this->form_validation->set_rules('nip', 'NIP', 'required|numeric|exact_length[6]|is_unique[user.nip]');
        $this->form_validation->set_rules('nama', 'Nama', 'required');
        $this->form_validation->set_rules('password', 'Password', 'required|min_length[6]');
        $this->form_validation->set_rules('role', 'Role', 'required|in_list[admin,pegawai]');

        if ($this->form_validation->run() == FALSE) {
            $this->session->set_flashdata('error', validation_errors());
        } else {
            $data = [
                'nip' => $this->input->post('nip'),
                'nama' => $this->input->post('nama'),
                'password' => password_hash($this->input->post('password'), PASSWORD_BCRYPT),
                'role' => $this->input->post('role'),
                'created_at' => date('Y-m-d H:i:s')
            ];
            $this->User_model->insert($data);
            $this->session->set_flashdata('success', 'User berhasil ditambahkan.');
        }
        redirect('admin/user');
    }

    public function update()
    {
        $nip = $this->input->post('nip');
        $this->form_validation->set_rules('nama', 'Nama', 'required');
        $this->form_validation->set_rules('role', 'Role', 'required|in_list[admin,pegawai]');

        if ($this->form_validation->run() == FALSE) {
            $this->session->set_flashdata('error', validation_errors());
        } else {
            $data = [
                'nama' => $this->input->post('nama'),
                'role' => $this->input->post('role')
            ];
            
            // Jika user mengisi password baru (jika tidak kosong)
            if (!empty($this->input->post('password'))) {
                $data['password'] = password_hash($this->input->post('password'), PASSWORD_BCRYPT);
            }
            $this->User_model->update($nip, $data);
            $this->session->set_flashdata('success', 'Data User berhasil diperbarui.');
        }
        redirect('admin/user');
    }

    public function delete($nip)
    {
        // Proteksi: Tidak dapat menghapus diri sendiri
        if ($nip == $this->session->userdata('user_nip')) {
            $this->session->set_flashdata('error', 'Tidak dapat menghapus akun Anda sendiri yang sedang aktif.');
        } else {
            $this->User_model->delete($nip);
            $this->session->set_flashdata('success', 'User berhasil dihapus.');
        }
        redirect('admin/user');
    }

    public function change_role()
    {
        $nip = $this->input->post('nip');
        $role = $this->input->post('role');

        // Proteksi: Tidak dapat mengubah role diri sendiri
        if ($nip == $this->session->userdata('user_nip')) {
            echo json_encode(['status' => 'error', 'message' => 'Tidak dapat mengubah role diri sendiri yang sedang aktif.']);
            return;
        }

        if ($nip && in_array($role, ['admin', 'pegawai'])) {
            $this->User_model->update($nip, ['role' => $role]);
            echo json_encode(['status' => 'success', 'message' => 'Role berhasil diperbarui']);
        }
    }
}