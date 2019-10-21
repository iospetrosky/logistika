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
        if ($res) {
            echo "OK"; // manage errors also in the function below
        } else {
            echo "The route can be cancelled only when the trasport is near a place";
        }
    }
    
    public function checkprodpoint($pp_id) {
        $materials = $this->simulator_model->check_prodpoint_requisites($pp_id,
                                                                        $this->input->cookie("current_id"),
                                                                        $this->input->cookie("market_id")
                                                                        );
        $json = new stdClass();
        $lines = array();
        $json->result = 'OK';
        foreach($materials as $mat) {
            if($mat->avail_quantity < $mat->need_quantity) {
                $l = "<font color=red>";
                $json->result = 'KO';
            } else {
                $l = "<font color=green>";
            }
            $l .= "<B>" . $mat->gname . "</b> Av: " . $mat->avail_quantity . " Rq: " . $mat->need_quantity;
            $l .= "</font>";
            $lines[] = $l;
        }
        $json->message = implode(" - ", $lines);
        echo json_encode($json);
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
    
    public function buyorder() {
        $this->simulator_model->place_buy_order_from_market($this->input->post_get(NULL,false));
        $this->marketplace(get_cookie("market_id"));
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
    
    public function setupstorage() {
        //retrieves all the data from cookies and the database
        //adds space or buys a new storage
        if($this->simulator_model->setup_static_warehouse($this->input->cookie("market_id"),
                                                        $this->input->cookie("current_id"))) {
            echo "OK";
        } else {
            echo "Something went wrong!";
        }
    }
    
    public function prodpoints(...$params) {
        if(sizeof($params) > 0) {
            //print_r($params); 
            switch($params[0]) {
                case 'new':
                    $this->simulator_model->new_production_point($params[1],
                                                                 $this->input->cookie("current_id"),
                                                                 $this->input->cookie("market_id"));
                    break;
                case 'save': //this comes as a form
                    $ret = $this->simulator_model->save_production_point($this->input->post(NULL,false));
                    switch($ret) {
                        case 1: $data['last_message'] = "Production point updated correctly"; break;
                        case -1: $data['last_message'] = "No money to convert the production"; break;
                        case -2: $data['last_message'] = "No money to upgrade to the selected level"; break;
                        case -3: $data['last_message'] = "Error updating the production point"; break;
                        case -4: $data['last_message'] = "You can't update the point at this state"; break;
                    }
                    break;
            }
        }
        // the default behaviour after the possible activities
        $data["url"] = explode("/", $this->uri->uri_string());
        $data["list"] = $this->simulator_model->get_player_prodpoints($this->input->cookie("current_id"),
                                                                        $this->input->cookie("market_id"));
        $data["place"] = $this->simulator_model->get_place_name($this->input->cookie("market_id"));
        $pptypes = $this->sets_model->get_available_prodpoints();
        $data["pptypes"] = array("0" => "Undefined");
        $data["goods"] = array();
        foreach($pptypes as $rt) {
            $data["pptypes"][$rt->id] = $rt->pptype;
            $data["goods"][$rt->pptype] = array("0" => "Nothing set yet") ;
            $goods = $this->simulator_model->get_goods_per_prodpoint_type($rt->id);
            foreach($goods as $gd) {
                $data["goods"][$rt->pptype][$gd->id] = $gd->description;
            }
        }

        $this->load->view('intro',$data);
        $this->load->view('userprodpoints_form',$data);
    }
    
    public function storage() {
        //finally get the storage and return it
        $data["url"] = explode("/", $this->uri->uri_string());
        $data["list"] = $this->simulator_model->get_storage_of($this->input->cookie("current_id"));
        $routes = $this->simulator_model->get_available_routes($this->input->cookie("market_id"));
        $data["place"] = $this->simulator_model->get_place_name($this->input->cookie("market_id"));
        //check if the user has a static storage in this place
        $data["warehouse"] = $this->simulator_model
                                ->get_static_warehouse($this->input->cookie("market_id"),
                                                        $this->input->cookie("current_id"));
        if($routes) {
            //transform to an array to be passed to the dropdown list
            $data["routes"] = array();
            foreach($routes as $rt) {
                $data["routes"][$rt->route_id] = $rt->description;
            }
        } else {
            $data["routes"] = false;
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
        if($goods_from->id_place != $goods_to->id_place) {
            die("Warehouses must be in the same location");
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
    
    public function begintravel($trans_id, $route_id) {
        echo $this->simulator_model->begin_travel($trans_id, $route_id);
    }
}