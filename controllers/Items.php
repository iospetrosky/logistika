
<?php
//this is the presentation layer - get data from a model and return to the caller
//this is an interface between views and models
defined('BASEPATH') OR exit('No direct script access allowed');

class Items extends CI_Controller {
    
    public function __construct()
    {
        parent::__construct();
        $this->load->helper('html_gen_helper');
        $this->load->model('editor_model');
        $this->load->model('sets_model');
    }
    
	public function index()
	{
	    // load all the data needed in the views in variables to be passed as second parameter
	    
        $data["url"] = explode("/", $this->uri->uri_string());
        $this->load->view('intro',$data);
	}
    
    
    
    
    
    
}
    