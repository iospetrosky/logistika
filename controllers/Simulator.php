<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Simulator extends CI_Controller {
    
    public function __construct()
    {
        parent::__construct();
        $this->load->helper('html_gen_helper');
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
}