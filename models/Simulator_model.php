
    <?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Simulator_model extends CI_Model {

    public function __construct()    {
        //$this->load->database(); // loaded by default
    }
    
    public function get_storage_of($id) {
        $query = $this->db->select('*')
                            ->from('v_player_warehouses_goods')
                            ->where('id_player',$id)
                            ->orderby('pname ASC')
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

}
    