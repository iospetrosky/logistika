<?php
// this is the DATA layer. No output here!!!

defined('BASEPATH') OR exit('No direct script access allowed');

class Editor_model extends CI_Model {

    public function __construct()    {
        //$this->load->database(); // autoloaded
    }

    public function warehouses_goods($page = 0, $psize = 10) {
        $this->db->select("wg.id, wg.id_warehouse, g.gname, wg.quantity, wg.locked")
                  ->select("w.capacity, w.whtype, p.pname, y.fullname")
                  ->from("warehouses_goods wg")
                  ->join("warehouses w","wg.id_warehouse = w.id")
                  ->join("places p","w.place_id = p.id")
                  ->join("players y","w.player_id = y.id")
                  ->join("goods g","wg.id_good = g.id");
        if ($page > 0) {
            $this->db->limit($psize,$psize*($page-1));
        }
        return $this->db->get()->result();
    }  
    
    public function items_production() {
        $query = $this->db->select("*")->from("items_prod_cost")
                            ->order_by("id asc")->get();
        return $query->result();                    
    }

    //************************************************************************
    public function save_itemprod($data) {
        $this->save_data("items_prod_cost",$data);
    }
    public function new_itemprod() {
        $this->new_data("items_prod_cost","id_item_prod",0);
    }
    public function delete_itemprod($id) {
        $this->delete_data("items_prod_cost", $id);
    }

    
    //************************************************************************
    public function save_traderoute($data) {
        $this->save_data("traderoutes",$data);
    }
    public function new_traderoute() {
        $this->new_data("traderoutes","description","New route");
    }
    public function delete_traderoute($id) {
        $this->delete_data("traderoutes", $id);
        $this->delete_data("routespaths", $id, "id_route");
    }

    //************************************************************************
    public function save_whgoods($data) {
        $this->save_data("warehouses_goods",$data);
    }
    public function new_whgoods() {
        $this->new_data("warehouses_goods","quantity","1");
    }
    public function delete_whgoods($id) {
        $this->delete_data("warehouses_goods", $id);
    }

    //************************************************************************
    public function save_good($data) {
        $this->save_data("goods",$data);
    }
    public function new_good() {
        $this->new_data("goods","gname","NO GOOD");
    }
    public function delete_good($id) {
        if ($id == 0) return;
        $this->delete_data("goods", $id);
    }

    //************************************************************************
    public function save_place($data) {
        $this->save_data("places",$data);
    }
    public function new_place() {
        $this->new_data("places","pname","new place");
    }
    public function delete_place($id) {
        if ($id == 0) return;
        $this->delete_data("places", $id);
    }    
    
    //************************************************************************
    public function save_player($data) {
        $this->save_data("players",$data);
    }
    public function new_player() {
        $this->new_data("players","fullname","new player");
    }
    public function delete_player($id) {
        if ($id == 0) return;
        $this->delete_data("players", $id);
    }

    //************************************************************************
    //prodpoint editing - major version (since players do it via the simulator)
    public function new_prodpoint_major() {
        // cheating on the fact that player 1 is Tyrsis major
        $this->new_data("productionpoints","id_player","1");
    }
    public function save_prodpoint_major($data) {
        //collect id_place and pptype_id that are not in the form
        //for major prod points the relationship is 1-1
        $data['id_place'] = $this->db->select("id")->from("places")
                                   ->where("major",$data['id_player'])
                                   ->get()->result()[0]->id;
//        echo $this->db->last_query();
        $data['pptype_id'] = $this->db->select("pptype_req as id")->from("goods")
                                   ->where("id",$data['id_good'])
                                   ->get()->result()[0]->id;
        $this->save_data("productionpoints",$data);                    
    }
    
    //************************************************************************
    public function save_prodpoint_type($data) {
        unset($data['mat_needed']); //this is a fake field, managed in a related table
        $this->save_data("prodpoint_types",$data);
    }
    public function new_prodpoint_type() {
        $this->new_data("prodpoint_types","pptype","AAA NEW TYPE");
    }
    public function delete_prodpoint_type($id) {
        if ($id == 0) return;
        $this->delete_data("prodpoint_types", $id);
    }
    public function new_prodpoint_mat($id) {
        $this->new_data("prodpoint_reqmaterials","pp_id", $id);
    }

    //************************************************************************
    public function save_workflow($data) {
        $this->save_data("productionworkflow",$data);
    }
    public function new_workflow() {
        $this->new_data("productionworkflow","quantity","1");
    }
    public function delete_workflow($id) {
        if ($id == 0) return; // this may be unnecessary
        $this->delete_data("productionworkflow", $id);
    }    
    
    //************************************************************************
    public function save_equivalent($data) {
        $this->save_data("equivalent",$data);
    }
    public function new_equivalent() {
        $this->new_data("equivalent","quantity","1");
    }
    public function delete_equivalent($id) {
        if ($id == 0) return; // this may be unnecessary
        $this->delete_data("equivalent", $id);
    }    

    //************************************************************************
    // the generic new - delete - save actions
    //************************************************************************
    private function new_data($table, $main_field, $def_value) {
        $this->db->set($main_field,$def_value);
        $this->db->insert($table);
    }
    private function delete_data($table, $key_val, $key_field = "id") {
        $this->db->where($key_field,$key_val);
        $this->db->delete($table);
    }
    private function save_data($table, $data) {
        //$DATA must have a row_id fields that maps with the ID field of a table
        //this because we can also edit the ID in some cases
        $this->db->where('id',$data['row_id']);
        unset($data["row_id"]);
        $this->db->update($table,$data);
    }
    
    
    
    
    
    
    
}
    