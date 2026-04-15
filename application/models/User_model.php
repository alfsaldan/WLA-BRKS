<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class User_model extends CI_Model {

    protected $table = 'user';

    public function __construct()
    {
        parent::__construct();
        $this->load->database();
    }

    public function get_by_nip($nip)
    {
        return $this->db->where('nip', $nip)->get($this->table)->row();
    }

    public function get_all()
    {
        return $this->db->order_by('created_at', 'DESC')->get($this->table)->result();
    }

    public function insert($data)
    {
        return $this->db->insert($this->table, $data);
    }

    public function update($nip, $data)
    {
        return $this->db->where('nip', $nip)->update($this->table, $data);
    }

    public function delete($nip)
    {
        return $this->db->where('nip', $nip)->delete($this->table);
    }

    public function count_all()
    {
        return $this->db->count_all($this->table);
    }

}
