<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Pegawai_model extends CI_Model {
    public function get_all() {
        $this->db->select('user.*, cabang.nama_cabang, unit.nama_unit, jabatan.nama_jabatan');
        $this->db->from('user');
        $this->db->join('cabang', 'cabang.id_cabang = user.id_cabang', 'left');
        $this->db->join('unit', 'unit.id_unit = user.id_unit', 'left');
        $this->db->join('jabatan', 'jabatan.id_jabatan = user.id_jabatan', 'left');
        $this->db->where('role', 'pegawai');
        return $this->db->get()->result();
    }

    public function get_by_jabatan($id_jabatan) {
        return $this->db->where('id_jabatan', $id_jabatan)->where('role', 'pegawai')->get('user')->result();
    }
}