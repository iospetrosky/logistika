<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Display_model extends CI_Model {

    public function __construct()    {
        //$this->load->database(); // loaded by default
    }

    public function majorwarehouses($field = false, $value = false) {
        return $this->generic_select("v_major_warehouses_goods", $field, $value);
    }
    
    public function marketplace($field = false, $value = false) {
        return $this->generic_select("v_marketplace", $field, $value);
    }
    
    private function generic_select($table, $field = false, $value = false) {
        $this->db->select("*")->from($table);
        if ($field) {
            $this->db->where($field,$value);
        }
        $query = $this->db->get();
        return $query->result();
    }
}
    