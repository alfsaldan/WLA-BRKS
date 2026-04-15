<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class UraianTugas_model extends CI_Model {
    protected $table = 'wla_uraian_tugas';

    public function get_all() {
        $this->db->select('wla_uraian_tugas.*, cabang.nama_cabang, unit.nama_unit, jabatan.nama_jabatan');
        $this->db->from($this->table);
        $this->db->join('cabang', 'cabang.id_cabang = wla_uraian_tugas.id_cabang', 'left');
        $this->db->join('unit', 'unit.id_unit = wla_uraian_tugas.id_unit', 'left');
        $this->db->join('jabatan', 'jabatan.id_jabatan = wla_uraian_tugas.id_jabatan', 'left');
        $this->db->order_by('id_tugas', 'DESC');
        return $this->db->get()->result();
    }

    public function get_by_filter($tahun, $bulan, $id_cabang, $id_unit, $id_jabatan) {
        $this->db->select('wla_uraian_tugas.*, cabang.nama_cabang, unit.nama_unit, jabatan.nama_jabatan');
        $this->db->from($this->table);
        $this->db->join('cabang', 'cabang.id_cabang = wla_uraian_tugas.id_cabang', 'left');
        $this->db->join('unit', 'unit.id_unit = wla_uraian_tugas.id_unit', 'left');
        $this->db->join('jabatan', 'jabatan.id_jabatan = wla_uraian_tugas.id_jabatan', 'left');
        $this->db->where('wla_uraian_tugas.tahun', $tahun);
        if ($bulan !== 'all') {
            $this->db->where('wla_uraian_tugas.bulan', $bulan);
        }
        $this->db->where('wla_uraian_tugas.id_cabang', $id_cabang);
        $this->db->where('wla_uraian_tugas.id_unit', $id_unit);
        $this->db->where('wla_uraian_tugas.id_jabatan', $id_jabatan);
        return $this->db->order_by('id_tugas', 'ASC')->get()->result();
    }

    public function insert($data) { return $this->db->insert($this->table, $data); }
    
    public function update($id, $data) { return $this->db->where('id_tugas', $id)->update($this->table, $data); }
    
    public function delete($id) { 
        return $this->db->where('id_tugas', $id)->update($this->table, ['is_active' => 0]); 
    }
}