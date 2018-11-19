<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Display extends CI_Controller {
    
    public function __construct()
    {
        parent::__construct();
        $this->load->helper('html_gen_helper');
        $this->load->model('display_model');
        $this->load->model('sets_model');
    }
    
    public function index()
    {
        // load all the data needed in the views in variables to be passed as second parameter
        //$data['tile_sets'] = $this->display_model->some_method(); 

        $data["url"] = explode("/", $this->uri->uri_string());
        $this->load->view('intro',$data);
        //$this->load->view('display_form',$data);
    }

    public function marketplace($field = false, $value = false) {
        $data['list'] = $this->display_model->marketplace($field, $value); 
        //$data['list'] = $this->display_model->marketplace("id_place", "1"); 
        $data['columns'] = array (
                array("ID", 50),
                array("ID place", 50),
                array("Place name", 150),
                array("ID player", 60),
                array("Player name", 150),
                array("Pl. tyoe", 70),
                array("Gold", 90),
                array("Op. type", 90),
                array("Op. scope", 70),
                array("ID good", 50),
                array("Good name", 150),
                array("Good type", 70),
                array("Quantity", 90),
                array("Price", 90)
            );
        $this->index();
        $this->load->view('display_form',$data);
    }
    
    
    public function majorwarehouses($field = false, $value = false) {
        $data['list'] = $this->display_model->majorwarehouses($field, $value); 
        $data['columns'] = array (
                array("ID place", 50),
                array("Place name", 150),
                array("Population", 90),
                array("Terrain", 90),
                array("ID whouse", 70),
                array("ID player", 70),
                array("Player name", 150),
                array("ID good", 50),
                array("Good name", 150),
                array("Available", 90),
                array("Locked", 90)
            );
        $this->index();
        $this->load->view('display_form',$data);
    }
}
