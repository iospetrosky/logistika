
<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Display extends CI_Controller {
    
    public function __construct()
    {
        parent::__construct();
        $this->load->model('display_model');
    }
    
	public function index()
	{
	    // load all the data needed in the views in variables to be passed as second parameter
	    //$data['tile_sets'] = $this->display_model->some_method(); 
	    
		//$this->load->view('top_menu');
		$this->load->view('display_form',$data);
	}
}
    