
<?php
// this is the DATA layer. No output here!!!

defined('BASEPATH') OR exit('No direct script access allowed');

class Editor_model extends CI_Model {

    public function __construct()    {
        //$this->load->database(); // autoloaded
    }

    public function warehouses_goods() {
        $sql = "select wg.id, wg.id_warehouse, g.gname, wg.quantity, wg.locked,
                    w.capacity, w.whtype,
                    p.pname, y.fullname
                        from warehouses_goods wg
                        inner join warehouses w on wg.id_warehouse = w.id
                        inner join places p on w.place_id = p.id
                        inner join players y on w.player_id = y.id
                        inner join goods g on wg.id_good = g.id
                ";
        return $this->db->query($sql)->result();
    }  
    
    public function items_production() {
        /*
        $query = $this->db->select("pc.id, pc.id_item_prod, i.tname as 'item_name'")
                            ->select("pc.id_item_need, n.tname as 'item_need'")
                            ->select("pc.id_good_need, g.gname as 'good_need'")
                            ->from("items_prod_cost pc")
                            ->join("items i","pc.id_item_prod = i.id")
                            ->join("items n","pc.id_item_need = i.id","left")
                            ->join("goods g","pc.id_good_need = g.id","left")
                            ->order_by("3 asc")
                            ->get();
        */
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
    public function save_prodpoint($data) {
        $this->save_data("productionpoints",$data);
    }
    public function new_prodpoint() {
        $this->new_data("productionpoints","rnd_order","1");
    }
    public function delete_prodpoint($id) {
        if ($id == 0) return;
        $this->delete_data("productionpoints", $id);
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
    