<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Display_model extends CI_Model {

    public function __construct()    {
        //$this->load->database(); // loaded by default
    }

    public function getmap($mapname) {
        return $this->generic_select("hex_maps","mname",$mapname);
    }
    
    public function get_routepath($route_id) {
        $path = $this->db->select('id,pathsequence,map_tile,id_route')->from('routespaths')
                        ->where('id_route',$route_id)
                        ->order_by('pathsequence ASC')
                        ->get()->result();
        if ($path) return $path;
        //create a default path with starthex and endhex from traderoutes
        $tiles = $this->generic_select('traderoutes','id',$route_id)[0];
        //BUG FIX: add field mapname o traderoutes, for now defaults to 'demo'
        $this->db->trans_begin();
        $path = new stdClass;
        $path->id_route = $route_id;
        $path->pathsequence = 1;
        $path->map_tile = $tiles->starthex;
        $this->db->insert('routespaths',$path);
        $path->pathsequence = 100;
        $path->map_tile = $tiles->endhex;
        $this->db->insert('routespaths',$path);
        $this->db->trans_commit();
        // at this point the minimal path must exist
        return $this->get_routepath($route_id);
    }
    
    public function get_transport_infos($player_id) {
        //used to display transport infos for a player on the map
        $this->db->select("tm.id,tm.hexmap,tm.route_id,w.player_id,w.whtype,coalesce(x.pname,'Travelling') as pname")
            ->select("coalesce(t.description,'Not on a route') as description")
            ->from("transport_movements tm")
            ->join("warehouses w", "tm.id = w.id")
            ->join("places x", "x.hexmap = tm.hexmap","left")
            ->join("traderoutes t","abs(tm.route_id) = t.id","left");
        if ($player_id) {
            $this->db->where("w.player_id",$player_id);
        } else {
            //the admin can see the transport of everybody
            $this->db->select("p.fullname")
                        ->join("players p", "p.id = w.player_id");
        }
        $data = $this->db->get()->result();
        //now collect the cargo info for every transport
        foreach($data as &$item) {
            $this->db->select("wg.id_good, wg.quantity, g.gname")
                        ->from("warehouses_goods wg")
                        ->join("goods g", "wg.id_good = g.id")
                        ->where("id_warehouse", $item->id);
            $query = $this->db->get();
            if(!$item->goods = $query->result()) {
                $item->goods = "Empty";
            }
        }
        
        
        return $data;
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
    
    //not really display, should be moved 
    public function add_tile_to_path($id_route, $token) {
        //first get the current path and detect the last token inserted (not the closing = 100)
        $query = $this->db->select("*")->from("routespaths")
                            ->where("id_route",$id_route)
                            ->where("pathsequence < 100")
                            ->order_by("pathsequence DESC")
                            ->get();
        if($route = $query->result()) {
            //since the sort is DESC the first item has the highest sequence number
            $newtoken = $route[0];
            $newtoken->pathsequence = $newtoken->pathsequence + 2;
            $newtoken->map_tile = $token;
            unset($newtoken->id);
            if ($this->db->insert("routespaths", $newtoken)) {
                return "OK";
            } else {
                return "INSERT ERROR " . $this->db->last_query();
            }
        } else {
            return "NO ROUTE ERROR " . $this->db->last_query();
        }
    }
    
    public function del_tile_from_path($id_tile) {
        $this->db->delete("routespaths", array("id"=>$id_tile));
    }
    
    public function update_sequence($id_tile, $newseq) {
        if ($this->db->set("pathsequence", $newseq)
                        ->where("id", $id_tile)
                        ->update("routespaths")) {
                            return true;
                        } else {
                            return false;
                        }
    }
}
    