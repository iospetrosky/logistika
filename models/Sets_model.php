<?php
// this is the DATA layer. No output here!!!

defined('BASEPATH') OR exit('No direct script access allowed');

class Sets_model extends CI_Model {

    public function __construct()    {
        //$this->load->database(); // autoloaded
    }

    public function get_human_players() {
        $query = $this->db->select('id, fullname')
                            ->from('players')
                            ->where('ptype','HU')
                            ->get();
        return $query->result();
    }
    
    public function majors_list() {
        $query = $this->db->select("id, fullname")
                          ->from('players')
                          ->where('ptype',"AI")
                          ->get();    
        return $query->result();
    }
    
    public function players_list() {
        $query = $this->db->select("id, fullname, ptype, gold, diamond")
                          ->from('players')
                          ->order_by("ID")
                          ->get();    
        return $query->result();
    }

    public function goods_list() {
        $query = $this->db->select("*")
                          ->from('goods')
                          ->order_by("ID")
                          ->get();    
        return $query->result();
    }

    public function prod_wf_list() {
        $query = $this->db->select("*")
                          ->from('productionworkflow')
                          ->order_by("ID")
                          ->get();    
        return $query->result();
    }
   
    public function equivalent_list() {
        $query = $this->db->select("*")
                          ->from('equivalent')
                          ->order_by("ID")
                          ->get();    
        return $query->result();
    }

    public function places_list() {
        $query = $this->db->select("id, pname, major, population, mapx, mapy, ptype")
                          ->from('places')
                          ->get();    
        return $query->result();
    }
    
    public function productionpoints_list() {
        $query = $this->db->select("*")
                          ->from('productionpoints')
                          ->order_by("ID")
                          ->get();    
        return $query->result();

    }
    
    
    
    
}
    