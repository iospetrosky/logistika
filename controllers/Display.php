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

    public function majorwarehouses() {
        $data['list'] = $this->display_model->majorwarehouses(); 

        $this->index();
        $this->load->view('majorwarehouses_form',$data);
    }
}
