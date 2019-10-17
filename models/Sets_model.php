<?php
// this is the DATA layer. No output here!!!

defined('BASEPATH') OR exit('No direct script access allowed');

class Sets_model extends CI_Model {

    public function __construct() {
        //$this->load->database(); // autoloaded
    }

    public function prdpt_mat_needed($id) {
        $query = $this->db->select("*")->from("prodpoint_reqmaterials")
                            ->where("pp_id",$id)
                            ->get();
        return $query->result();   
    }
    
    public function get_human_players() {
        $query = $this->db->select('id, fullname')
                            ->from('players')
                            ->where('ptype','HU')
                            ->get();
        return $query->result();
    }
    public function get_ai_players() {
        $query = $this->db->select('id, fullname')
                            ->from('players')
                            ->where('ptype','AI')
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
    
    public function basic_goods() {
        //only goods from 999999 conv_cost
        $query = $this->db->select("g.id, g.description")
                          ->from("goods g")
                          ->join("prodpoint_types pt","g.pptype_req = pt.id and conv_cost = 999999")
                          ->order_by("g.description asc")
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
        $query = $this->db->select("id, pname, major, population, hexmap, ptype, avail_areas")
                          ->from('places')
                          ->get();    
        return $query->result();
    }
    
    public function prodpoints_majors_list() {
        $query = $this->db->select("pp.id, pp.id_player, pp.id_good, pp.active, pp.plevel")
                          ->from('productionpoints pp')
                          ->join('players y', "y.id = pp.id_player")
                          ->where('y.ptype','AI')
                          ->order_by('pp.id')
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

    public function prodpoints_types_list() {
        $query = $this->db->select("*")
                          ->from('prodpoint_types')
                          ->order_by("pptype ASC")
                          ->where("conv_cost < 999999")
                          ->get();    
        return $query->result();
    }
    
    public function get_prodpoint_types() {
        $query = $this->db->select("*")
                          ->from('prodpoint_types')
                          ->order_by("pptype ASC")
                          ->get();    
        return $query->result();
    }
    
    public function traderoutes_list() {
        $query = $this->db->select("id, description, starthex, endhex, hexlength, hexcost, traveltype")
                        ->from("traderoutes")
                        ->order_by("id")
                        ->get();
        return $query->result();
    }
    
    public function items_list() {
        $query = $this->db->select("*")
                        ->from("items")
                        ->order_by("tname asc")
                        ->get();
        return $query->result();
    }

    public function get_available_prodpoints() {
        $query = $this->db->select("ppt.id, pptype")
                        ->from("prodpoint_types ppt")
                        ->order_by("pptype asc")
                        ->join("prodpoint_reqmaterials","pp_id = ppt.id")
                        ->get();
        return $query->result();
    }
}
    