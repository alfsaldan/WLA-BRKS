<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Organisasi_model extends CI_Model {
    public function get_cabang() {
        return $this->db->get('cabang')->result();
    }
    
    public function get_unit() {
        $this->db->select('unit.*, cabang.nama_cabang');
        $this->db->from('unit');
        $this->db->join('cabang', 'cabang.id_cabang = unit.id_cabang', 'left');
        return $this->db->get()->result();
    }

    public function get_jabatan() {
        $this->db->select('jabatan.*, unit.nama_unit, unit.kode_unit, cabang.nama_cabang, cabang.kode_cabang');
        $this->db->from('jabatan');
        $this->db->join('unit', 'unit.id_unit = jabatan.id_unit', 'left');
        $this->db->join('cabang', 'cabang.id_cabang = unit.id_cabang', 'left');
        return $this->db->get()->result();
    }

    public function insert($table, $data) {
        return $this->db->insert($table, $data);
    }

    public function update($table, $data, $where) {
        return $this->db->where($where)->update($table, $data);
    }

    public function delete($table, $where) {
        return $this->db->where($where)->delete($table);
    }

    // Untuk Dropdown AJAX
    public function get_unit_by_cabang($id_cabang) {
        return $this->db->where('id_cabang', $id_cabang)->get('unit')->result();
    }
    public function get_jabatan_by_unit($id_unit) {
        return $this->db->where('id_unit', $id_unit)->get('jabatan')->result();
    }
}