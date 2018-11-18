
<?php
//this is the presentation layer - get data from a model and return to the caller
//this is an interface between views and models
defined('BASEPATH') OR exit('No direct script access allowed');

class Editor extends CI_Controller {
    
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
    
    public function players($action = false, $id = false)
    {   
        switch($action) {
            case 'save':
                $this->editor_model->save_player($this->input->post(NULL,false));
                break;
            case 'new':
                $this->editor_model->new_player();
                break;
            case 'del':
                $this->editor_model->delete_player($id);
                break;
        }
        //print_r($this->players_model->players_list());
        $data['list'] = $this->sets_model->players_list(); 
        $this->index();
        $this->load->view('players_form',$data);
    }

    public function goods($action = false, $id = false)
    {   
        switch($action) {
            case 'save':
                $this->editor_model->save_good($this->input->post(NULL,false));
                break;
            case 'new':
                $this->editor_model->new_good();
                break;
            case 'del':
                $this->editor_model->delete_good($id);
                break;
        }
        //print_r($this->players_model->players_list());
        $data['list'] = $this->sets_model->goods_list(); 
        $this->index();
        $this->load->view('goods_form',$data);
    }


    public function places($action = false, $id = false)
    {   
        switch($action) {
            case 'save':
                $this->editor_model->save_place($this->input->post(NULL,false));
                break;
            case 'new':
                $this->editor_model->new_place();
                break;
            case 'del':
                $this->editor_model->delete_place($id);
                break;
        }
        //print_r($this->players_model->players_list());
        $data['list'] = $this->sets_model->places_list(); 
        $data['majors'] = array();
        foreach($this->sets_model->majors_list() as $mj) {
            $data['majors'][$mj->id] = $mj->fullname;
        }
        $this->index();
        $this->load->view('places_form',$data);
    }

    public function prodpoints($action = false, $id = false)
    {   
        switch($action) {
            case 'save':
                $this->editor_model->save_prodpoint($this->input->post(NULL,false));
                break;
            case 'new':
                $this->editor_model->new_prodpoint();
                break;
            case 'del':
                $this->editor_model->delete_prodpoint($id);
                break;
        }
        

        $data['list'] = $this->sets_model->productionpoints_list(); 
        // these are for the dropdown boxes
        $data['players'] = array();
        $data['goods'] = array();
        $data['places'] = array();

        foreach($this->sets_model->players_list() as $xx) {
            $data['players'][$xx->id] = $xx->fullname;
        }
        foreach($this->sets_model->goods_list() as $xx) {
            $data['goods'][$xx->id] = $xx->gname;
        }
        foreach($this->sets_model->places_list() as $xx) {
            $data['places'][$xx->id] = $xx->pname;
        }
        $this->index();
        $this->load->view('prodpoints_form',$data);
    }

    public function prod_wf($action = false, $id = false)
    {   
        switch($action) {
            case 'save':
                $this->editor_model->save_workflow($this->input->post(NULL,false));
                break;
            case 'new':
                $this->editor_model->new_workflow();
                break;
            case 'del':
                $this->editor_model->delete_workflow($id);
                break;
        }
        $data['list'] = $this->sets_model->prod_wf_list(); 
        // these are for the dropdown boxes
        $data['goods'] = array();

        foreach($this->sets_model->goods_list() as $xx) {
            $data['goods'][$xx->id] = $xx->gname;
        }
        $this->index();
        $this->load->view('workflows_form',$data);
    }

    public function equivalent($action = false, $id = false)
    {   
        switch($action) {
            case 'save':
                $this->editor_model->save_equivalent($this->input->post(NULL,false));
                break;
            case 'new':
                $this->editor_model->new_equivalent();
                break;
            case 'del':
                $this->editor_model->delete_equivalent($id);
                break;
        }
        $data['list'] = $this->sets_model->equivalent_list(); 
        // these are for the dropdown boxes
        $data['goods'] = array();

        foreach($this->sets_model->goods_list() as $xx) {
            $data['goods'][$xx->id] = $xx->gname;
        }
        $this->index();
        $this->load->view('equivalent_form',$data);
    }

    public function wh_goods($action = false, $id = false) 
    {
        switch($action) {
            case 'save':
                $this->editor_model->save_whgoods($this->input->post(NULL,false));
                break;
            case 'new':
                $this->editor_model->new_whgoods();
                break;
            case 'del':
                $this->editor_model->delete_whgoods($id);
                break;
        }
        
        
	    $data['list'] = $this->editor_model->warehouses_goods(); 
        $this->index();
        $this->load->view('wh_goods_form',$data);
        
        
    }
    
    
    
    
    
}
    