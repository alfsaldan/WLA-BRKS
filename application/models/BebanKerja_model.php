<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class BebanKerja_model extends CI_Model {
    
    public function get_volume($nip, $tahun, $bulan) {
        $res = $this->db->where(['nip' => $nip, 'tahun' => $tahun, 'bulan' => $bulan])->get('wla_beban_kerja')->result();
        $data = [];
        foreach($res as $r) { $data[$r->id_tugas] = $r->volume; }
        return $data;
    }

    public function get_all_volumes($tahun, $bulan) {
        $this->db->where('tahun', $tahun);
        if ($bulan !== 'all') {
            $this->db->where('bulan', $bulan);
        }
        $res = $this->db->get('wla_beban_kerja')->result();
        $data = [];
        // Format array multi-dimensi: $data['nip_pegawai']['id_tugas'] = volume;
        foreach($res as $r) { 
            if (!isset($data[$r->nip][$r->id_tugas])) $data[$r->nip][$r->id_tugas] = 0;
            $data[$r->nip][$r->id_tugas] += $r->volume; 
        }
        return $data;
    }

    public function save_volume($nip, $tahun, $bulan, $id_tugas, $volume) {
        $exists = $this->db->where(['nip' => $nip, 'tahun' => $tahun, 'bulan' => $bulan, 'id_tugas' => $id_tugas])->get('wla_beban_kerja')->row();
        if($exists) {
            $this->db->where('id_beban', $exists->id_beban)->update('wla_beban_kerja', ['volume' => $volume, 'updated_at' => date('Y-m-d H:i:s')]);
        } else {
            $this->db->insert('wla_beban_kerja', ['nip' => $nip, 'tahun' => $tahun, 'bulan' => $bulan, 'id_tugas' => $id_tugas, 'volume' => $volume, 'created_at' => date('Y-m-d H:i:s')]);
        }
    }
}