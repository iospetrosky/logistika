
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
    
    public function storage() {
        //manages the storage of the current selected player
        $data["url"] = explode("/", $this->uri->uri_string());
        //no params, just get the storage and return it
        $data["warehouses"] = $this->simulator_model->get_storage_of($this->input->cookie("current_id"));
        $this->load->view('intro',$data);
        $this->load->view('storage_form',$data);
    }
    
    
    
    
}
    