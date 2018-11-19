<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Display_model extends CI_Model {

    public function __construct()    {
        //$this->load->database(); // loaded by default
    }

    public function majorwarehouses() {
        $query = $this->db->get('v_major_warehouses_goods');
        return $query->result();
    }
    
    public function marketplace() {
        $query = $this->db->get('v_marketplace');
        return $query->result();
    }
    
}
    