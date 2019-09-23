<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Simulator extends CI_Controller {
    
    public function __construct()
    {
        parent::__construct();
        $this->load->helper('html_gen_helper');
        $this->load->helper('url');
        $this->load->model('simulator_model');
        $this->load->model('sets_model');
    }
    
	public function index()
	{
	    // load all the data needed in the views in variables to be passed as second parameter
	    
        $data["url"] = explode("/", $this->uri->uri_string());
        $data["current_id"] = $this->input->cookie("current_id");
        $data["current_player"] =  $this->simulator_model->get_player_name($data["current_id"]);
        $data["players_list"] = array();
        foreach($this->sets_model->get_human_players() as $pl) {
            $data["players_list"][$pl->id] = $pl->fullname;
        }
        $this->load->view('intro',$data);
		$this->load->view('simulator_form',$data);
	}
    
    public function fleet() {
        $user_id = $this->input->cookie("current_id");
        $data["url"] = explode("/", $this->uri->uri_string());
        $data["player"] = $user_id;
        
        $data["list"] = $this->simulator_model->get_fleet_info($user_id);

        $this->load->view('intro',$data);
        $this->load->view('fleet_form',$data);
    }

    public function cancelroute($id_route) {
        $res = $this->simulator_model->cancel_route($id_route);
        echo "OK"; // manage errors also in the function below
    }
    
    public function createtransport() {
        $user_id = $this->input->cookie("current_id");
        $place_id = $this->input->cookie("market_id");
        //the rest in the POST
        $res = $this->simulator_model->create_transport($this->input->cookie("current_id"),
                                           $this->input->cookie("market_id"), 
                                           $this->input->post_get("capacity"),
                                           $this->input->post_get("whtype"),
                                           $this->input->post_get("mov_points")
                                           );
        echo "OK";
    }
    
    public function marketplace($place_id = 0) {
        /*
        This function can be called with or without a place
        WITHOUT: find the list of potential markets for the player
        WITH: display the list of deals of the market at this place
        */
        $user_id = $this->input->cookie("current_id");
        $data["url"] = explode("/", $this->uri->uri_string());
        if ($place_id == 0) {
            //try to get the cookie
            $place_id = get_cookie("market_id");
        } elseif ($place_id == -1) {
            //delete the cookie
            $place_id = 0;
            delete_cookie("market_id");
        } else {
            // set the cookie
            set_cookie("market_id",$place_id,30000);
        }
        $data["place"] = $this->simulator_model->get_place_name($place_id);
        $data["player"] = $user_id;
        if (!$place_id)  { 
            $data["list"] = $this->simulator_model->get_places_whouse_player($user_id);
        } else {
            $data["list"] = $this->simulator_model->get_deals_at($place_id);
            //now fix the equivalences
            //print_r($data["list"]); die;
            foreach($data["list"] as &$entry) {
                if ($entry->id_good != $entry->id_equiv) {
                    switch($entry->id_equiv) {
                        case 1:
                            $entry->equiv = sprintf("Sold as %s FOOD at %s", $entry->equiv_quantity, $entry->equiv_price);
                            break;
                        default:
                            //really?
                            $entry->equiv = "This should not happen";
                            break;
                    }
                } 
            }
        }
        $this->load->view('intro',$data);
        $this->load->view('market_form',$data);
    }
    
    public function cancelorder($id_order) {
        $user_id = $this->input->cookie("current_id");
        if ($this->simulator_model->cancel_order($id_order, $user_id)) {
            echo "OK";
        } else {
            echo "Something weird happened, but the order can't be cancelled";
        }
    }
    
    public function updatemarketprice($id, $newprice) {
        $json = new stdClass();
        if ($this->simulator_model->update_market_price($id,$newprice)) {
            $json->retcode = 'OK';
            $json->id = $id;
            $json->line = "#line_" . $id; //returns the name of the line to be restored in normal color
            $entry = $this->simulator_model->get_marketplace_data($id);
            $json->equiv = sprintf("Sold as %s FOOD at %s", $entry->equiv_quantity, $entry->equiv_price);
        } else {
            $json->retcode = 'X1';
            $json->message =  "Something bad happened. Reload the page";
        }
        echo json_encode($json);
    }
    
    
    public function storage(/*...$params*/) {
        //manages the storage of the current selected player
        /*
        if(sizeof($params) > 0) {
            //switch($params[0]) {
                print_r($params);
            //}
        }
        */
        //finally get the storage and return it
        $data["url"] = explode("/", $this->uri->uri_string());
        $data["list"] = $this->simulator_model->get_storage_of($this->input->cookie("current_id"));
        $routes = $this->simulator_model->get_available_routes($this->input->cookie("market_id"));
        if($routes) {
            $data["routes"] = array();
            $x = 0;
            foreach($routes as $rt) {
                $data["routes"][$rt->route_id] = $rt->description;
            }
        }
        $this->load->view('intro',$data);
        $this->load->view('storage_form',$data);
    }
    
    public function movegoods($amount,$from,$to) {
        //attention. FROM is the ID in warehouses_goods
        //           TO is the ID of the warehouse because it may be empty if it's a SHIP for example
        $user_id = $this->input->cookie("current_id");
        //get date of $from and $to
        $goods_from = $this->simulator_model->get_wh_goods($from,'id');
        $goods_to = $this->simulator_model->get_wh_goods($to,'id_whouse');
        if($goods_from->id_whouse == $goods_to->id_whouse) {
            die("Same warehouse");
        }
        if(($goods_from->id_player!=$user_id) || ($goods_from->id_player!=$user_id)) {
            die("Both warehouses must belong to the same player");// this should not happen by normal means
        }
        if($goods_from->avail_quantity < $amount) {
            //we don't move locked quantities as well
            die("Trying to move too many items");
        }
        if($goods_to->capacity - $goods_to->avail_quantity - $goods_to->locked < $amount) {
            die("Not enough space in the destination warehouse");
        }
        //finally we do the movement
        if ($this->simulator_model->movegoods($amount, $goods_from, $goods_to)) {
            die("OK");
        } else {
            die("An error occurred during actual transfer"); //maybe the data changed in the meanwhile
        }
    } // movegoods
    
    public function createsellorder($wh_goods_id, $amount, $price) {
        $user_id = $this->input->cookie("current_id");
        $ret = $this->simulator_model->create_sell_order($wh_goods_id, $amount, $price, $user_id);
        echo $ret;
        
    }
}