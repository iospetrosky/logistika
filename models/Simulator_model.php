<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Simulator_model extends CI_Model {

    public function __construct()    {
        //$this->load->database(); // loaded by default
    }
    public function get_fleet_info($user_id) {
        $query = $this->db->select("id,whtype,description,traveltype,mov_points,curr_points,hexmap,current_location,is_landed")
                            ->from("v_transports_locations")
                            ->where("player_id",$user_id)
                            ->get();
        return $query->result();
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
    
    public function get_place_name($id_place) {
        if ($id_place == 0) return "";
        return $this->db->select("pname")
                        ->from("places")
                        ->where("id",$id_place)
                        ->get()->result()[0]->pname;
    }

    public function get_wh_goods($some_id, $id_of) {
        $query = $this->db->select('id_whouse,id_player,capacity,id_good,avail_quantity,locked,id_place')
                            ->from("v_player_warehouses_goods")
                            ->where($id_of,$some_id)
                            ->get();
        return $query->result()[0];
    }
    
    public function get_places_whouse_player($player_id) {
        //returns the markeplaces where the player has a storage
        return $this->db->select("id_place, pname")->distinct()
                        ->from("v_places_whouse_players")
                        ->where("id_player",$player_id)
                        ->get()->result();
    }
    
    public function get_deals_at($place) {
        $query = $this->db->select("id,fullname,op_type,gname,quantity,price,'' as equiv,id_good,id_player,id_equiv,equiv_quantity,equiv_price")
                        //->from("v_marketplace")
                        ->from("v_marketplace_equiv")
                        ->where("id_place",$place)
                        ->get();
        return $query->result();
    }
    
    public function get_available_routes($id_place) {
        //get the hexagon of the place
        $hex = $this->db->select("hexmap")->from("places")
                        ->where("id",$id_place)->get()->result();
        if ($hex) {
            $hex = $hex[0]->hexmap;
        } else {
            return false;
        }
        //get the possible routes from this hex
        $routes = $this->db->query("select id as route_id, description from traderoutes where starthex = '$hex' UNION " . 
                                   "select id * -1 as route_id, description from traderoutes where endhex = '$hex'")->result();
        return $routes;
    }
    
    public function get_marketplace_data($id) {
        return $this->db->query("select * from v_marketplace_equiv where id = $id limit 1")
                        ->result()[0];
    }
    
    public function create_transport($player_id, $place_id, $capacity, $whtype, $mov_points) {
        $wh = new stdClass;
        $tm = new stdClass;
        
        $wh->player_id = $player_id;
        $wh->place_id = $place_id;
        $wh->capacity = $capacity;
        $wh->whtype = $whtype;
        
        $this->db->trans_begin();
        $this->db->insert("warehouses",$wh);
        $tm->id = $this->db->insert_id();
        
        $tm->mov_points = $mov_points;
        $tm->hexmap = $this->db->select("hexmap")->from("places")
                            ->where("id",$place_id)->get()->result()[0]->hexmap;
        $tm->route_id = 0;
        $this->db->insert("transport_movements",$tm);
        $this->db->trans_commit();
        return true;
    }
    
    public function cancel_route($id_transportroute) {
        //this can be done only if there's a city in the current tile
        $location = $this->db->select("current_location, location_id")
                                ->from("v_transports_locations")
                                ->where("id",$id_transportroute)
                                ->get()->result()[0];
        if ($location->current_location == 'travel') return false;
        //otherwise there is a location along the way
        $this->db->trans_begin();
        $this->db->set("route_id",0)
                    ->where("id", $id_transportroute)
                    ->update("transport_movements");
        $this->db->set("place_id",$location->location_id)
                    ->where("id", $id_transportroute)
                    ->update("warehouses");
        $this->db->trans_commit();
        return true;
    }        

    public function movegoods($amount,$from,$to) {
        //$from and $to are records collected with get_wh_goods
        $this->db->trans_begin();
        $sql = sprintf("update warehouses_goods set quantity = quantity - %s, locked = locked - %s where quantity-locked >= %s and id_warehouse = %s and id_good = %s",
                            $amount,$amount,$amount,$from->id_whouse,$from->id_good);
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
    
    public function cancel_order($id, $user_id) {
        $this->db->trans_begin();
        $changed_rows = 0;
        // unlock the amount of goods
        $order = $this->db->select('id_good, id_place, quantity')
                        ->from('marketplace')
                        ->where('id',$id)
                        ->get()->result()[0];
        $good = $this->db->select("id, locked")
                        ->from("v_player_warehouses_goods")
                        ->where('id_good',$order->id_good)
                            ->where('id_place',$order->id_place)
                            ->where('id_player',$user_id)
                            ->where('locked >= ' . $order->quantity)
                        ->order_by("locked ASC")
                        ->get();
        //echo $this->db->last_query();
        if ($good->num_rows() > 0) {
            $good = $good->result()[0];
            $this->db->set("locked", $good->locked - $order->quantity)
                        ->where('id',$good->id)
                        ->update("warehouses_goods");
            $changed_rows += $this->db->affected_rows();
        }
        
        $this->db->delete('marketplace',array('id'=>$id,'id_player'=>$user_id));
        $changed_rows += $this->db->affected_rows();
        if ($changed_rows == 2) {
            $this->db->trans_commit();
            return true;
        } else {
            $this->db->trans_rollback();
            return false;
        }
    }
    
    public function update_market_price($id,$newprice) {
        $this->db->trans_begin();
        $this->db->set("price", $newprice)
                    ->where("id", $id)
                    ->update("marketplace");
        if ($this->db->affected_rows() == 1) {
            $this->db->trans_commit();
            return true;
        } else {
            $this->db->trans_rollback();
            return false;
        }
    }
    
    public function create_sell_order($wh_goods_id, $amount, $price, $user_id) {
        $pwg = $this->db->select("*")->from("v_player_warehouses_goods")
                            ->where("id",$wh_goods_id)
                            ->get()->result()[0];
        if(!$pwg) return "Error selecting the item";
        //cheching conditions
        if($pwg->id_player != $user_id) return "Player mismatch. Possible cheating attempt!";
        if($pwg->avail_quantity < $amount) return "Not enough unlocked materials";
        $order = new stdClass();
        $order->id_place = $pwg->id_place;
        $order->id_player = $user_id;
        $order->op_type = 'S';
        $order->id_good = $pwg->id_good;
        $order->quantity = $amount;
        $order->price = $price;
        
        $this->db->trans_begin();
        $this->db->insert('marketplace',$order);
        if ($this->db->affected_rows() != 1) {
            $this->db->trans_rollback();
            return "An error occurred while placing the order";
        }
        $this->db->query("update warehouses_goods set locked = locked + $amount where id = $wh_goods_id");
        if ($this->db->affected_rows() != 1) {
            $this->db->trans_rollback();
            return "An error occurred while updating the storage data";
        }
        $this->db->trans_commit();
        return "OK";
    }
    
    public function begin_travel($trans_id, $route_id) {
        /*
        Get the hexmap of the place where the transport is standing.
        Decide if the route is made forward or backwards
        For now let's assume the player selects a correct type of route (SEA-ROAD-ETC)
        */
        if ($transport = $this->db->select("id, place_id")->from("warehouses")
                                ->where("id", $trans_id)->get()->result()) {
            $transport = $transport[0];
        } else {
            return "Can't identify the transport";
        }
        if ($trans_move = $this->db->select("id,hexmap,route_id")->from("transport_movements")
                                    ->where("id", $trans_id)->get()->result()) {
            $trans_move = $trans_move[0];
        } else {
            return "Can't identify the transport movement rules";
        }
        if ($place = $this->db->select("id, hexmap")->from("places")
                                ->where("id", $transport->place_id)->get()->result()) {
            $place = $place[0];
        } else {
            return "Can't identify the place";
        }
        if ($route = $this->db->select("id, starthex, endhex, traveltype")
                                ->from("traderoutes")->where("id",$route_id)
                                ->get()->result()) {
            $route = $route[0];
        } else {
            return "Can't identify the route";
        }
        //All fine, make the changes
        $this->db->trans_begin();
        $transport->place_id = 0;
        $this->db->where("id", $trans_id)->update("warehouses",$transport);
        
        $trans_move->hexmap = $place->hexmap;
        if ($place->hexmap == $route->starthex) {
            $trans_move->route_id = $route_id;
        } elseif ($place->hexmap == $route->endhex) {
            $trans_move->route_id = -$route_id;
        } else {
            $this->db->trans_rollback();
            return "Unmatched start or end point on travel route";
        }
        $this->db->where("id",$trans_id)->update("transport_movements", $trans_move);
        $this->db->trans_commit();
        //$this->db->trans_rollback();
        return "OK";
    }
}