
    <?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Simulator_model extends CI_Model {

    public function __construct()    {
        //$this->load->database(); // loaded by default
    }
    
    public function get_storage_of($id) {
        $query = $this->db->select('id, pname,id_whouse,capacity,gname,avail_quantity,locked,whtype')
                            ->from('v_player_warehouses_goods')
                            ->where('id_player',$id)
                            ->order_by('pname ASC')
                            ->get();
        return $query->result();
    }
    
    public function get_player_name($id) {
        if ($id) {
            $query = $this->db->select('fullname')
                                ->from('players')
                                ->where('id',$id)
                                ->get();
            return $query->result()[0]->fullname;
        } else {
            return false;
        }
    }

    public function get_wh_goods($some_id, $id_of) {
        $query = $this->db->select('id_whouse,id_player,capacity,id_good,avail_quantity,locked')
                            ->from("v_player_warehouses_goods")
                            ->where($id_of,$some_id)
                            ->get();
        return $query->result()[0];
    }
    
    public function movegoods($amount,$from,$to) {
        //$from and $to are records collected with get_wh_goods
        $this->db->trans_begin();
        $sql = sprintf("update warehouses_goods set quantity = quantity - %s where quantity-locked >= %s and id_warehouse = %s and id_good = %s",
                            $amount,$amount,$from->id_whouse,$from->id_good);
        $this->db->query($sql);
        if ($this->db->affected_rows() != 1) {
            $this->db->trans_rollback();
            return false;
        }
        $sql = sprintf("update warehouses_goods set quantity = quantity + %s where id_warehouse = %s and id_good = %s",
                            $amount,$to->id_whouse,$from->id_good);
        $this->db->query($sql);
        if ($this->db->affected_rows() != 1) {
            //try to insert
            $data = array("id_warehouse"=>$to->id_whouse, "id_good"=>$from->id_good, "quantity"=>$amount);
            $this->db->insert("warehouses_goods",$data);
            if ($this->db->affected_rows() != 1) {
                $this->db->trans_rollback();
                return false;
            }
        }
        //cleanups ... quantities that reach 0 for some reason
        $this->db->query("delete from warehouses_goods where quantity = 0");
        $this->db->trans_commit();
        return true;
    }
}
    