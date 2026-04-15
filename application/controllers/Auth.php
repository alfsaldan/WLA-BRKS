<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Auth extends CI_Controller {

    public function __construct()
    {
        parent::__construct();
        $this->load->model('User_model');
        $this->load->library('form_validation');
        $this->load->library('session');
        $this->load->helper(['url', 'form']);
    }

    public function index()
    {
        // Jika sudah login, redirect sesuai role
        if ($this->session->userdata('logged_in')) {
            $this->_redirect_based_on_role();
        }

        $this->load->view('Auth/Login');
    }

    public function process()
    {
        $this->form_validation->set_rules('nip', 'NIP', 'required|numeric|exact_length[6]');
        $this->form_validation->set_rules('password', 'Password', 'required');

        if ($this->form_validation->run() == FALSE) {
            $this->load->view('Auth/Login');
            return;
        }

        $nip = $this->input->post('nip');
        $password = $this->input->post('password');
        $user = $this->User_model->get_by_nip($nip);

        if (!$user) {
            $this->session->set_flashdata('error', 'NIP tidak ditemukan');
            redirect('auth');
        }

        if (password_verify($password, $user->password)) {
            $this->session->set_userdata([
                'user_nip'  => $user->nip,
                'user_name' => $user->nama,
                'user_role' => $user->role,
                'logged_in' => TRUE
            ]);
            $this->_redirect_based_on_role();
        }

        $this->session->set_flashdata('error', 'Password salah');
        redirect('auth');
    }

    private function _redirect_based_on_role()
    {
        if ($this->session->userdata('user_role') === 'admin') {
            redirect('admin');
        } else {
            redirect('pegawai/dashboard');
        }
    }

    public function logout()
    {
        $this->session->sess_destroy();
        redirect('auth');
    }
}