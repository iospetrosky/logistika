
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
    
	public function index($data = array())
	{
        $data["url"] = explode("/", $this->uri->uri_string());
        //used to build the URL without the parameters sent to the functions
        $data["naked_url"] = config_item('index_page_url') . "/" . $data["url"][0];
        if (isset($data['url'][1])) $data["naked_url"] .= "/" . $data["url"][1];
        
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
    
    public function traderoutes($action = false, $id = false)
    {
        switch($action) {
            case 'save':
                $this->editor_model->save_traderoute($this->input->post(NULL,false));
                break;
            case 'new':
                $this->editor_model->new_traderoute();
                break;
            case 'del':
                $this->editor_model->delete_traderoute($id);
                break;
        }
        //print_r($this->players_model->players_list());
        $data['list'] = $this->sets_model->traderoutes_list(); 
        $this->index();
        $this->load->view('traderoutes_form',$data);
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
        //pagination management
        $data["page_title"] = "Goods management"; // <<-- edit this
        $data["cookyname"] = "PG_" . substr(md5($data["page_title"]),0,10);
        $data["page"] = get_cookie($data["cookyname"]);
        if (!$data["page"]) {
            $data["page"] = 1;
            set_cookie($data["cookyname"],1);
        }
	    $data['list'] = $this->sets_model->goods_list($data["page"],10);  // <<-- edit this
        $data["last_page"] = count($data["list"])<10?true:false;
        //end of pagination management
        $data['pptypes'] = array("0" => "Not selected");
        foreach($this->sets_model->get_prodpoint_types() as $ppt) {
            $data['pptypes'][$ppt->id] = $ppt->pptype;
        }

        $this->index($data);
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

    public function prodptmajors($action = false, $id = false)
    {   
        switch($action) {
            case 'save':
                $this->editor_model->save_prodpoint_major($this->input->post(NULL,false));
                break;
            case 'new':
                $this->editor_model->new_prodpoint_major(); 
                break;
            case 'del':
                $this->editor_model->delete_place($id);
                break;
        }
        $data['list'] = $this->sets_model->prodpoints_majors_list(); 
        $data['majors'] = array();
        $data['goods'] = array();
        //$data['places'] = array();
        foreach($this->sets_model->majors_list() as $item) {
            $data['majors'][$item->id] = $item->fullname;
        }
        /*foreach($this->sets_model->places_list() as $item) {
            $data['places'][$item->id] = $item->pname;
        }*/
        foreach($this->sets_model->basic_goods() as $item) {
            $data['goods'][$item->id] = $item->description;
        }
        
        
        
        
        $this->index();
        $this->load->view('prodptmajors_form',$data);
    }
    
    public function prodpoints($action = false, $id = false)
    {   
        switch($action) {
            case 'save':
                $this->editor_model->save_prodpoint_type($this->input->post(NULL,false));
                break;
            case 'new':
                $this->editor_model->new_prodpoint_type();
                break;
            case 'del':
                $this->editor_model->delete_prodpoint_type($id);
                break;
            case 'newmat':
                $this->editor_model->new_prodpoint_mat($id);
                break;
        }
        

        $data['list'] = $this->sets_model->prodpoints_types_list(); 
        foreach($data['list'] as &$item) {
            //load the materials needed 
            $item->mat_needed = $this->sets_model->prdpt_mat_needed($item->id);
        }
        // these are for the dropdown boxes
        $data['goods'] = array();

        foreach($this->sets_model->goods_list() as $xx) {
            $data['goods'][$xx->id] = $xx->gname;
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
        //pagination management
        $data["page_title"] = "Warehouses goods"; // <<-- edit this
        $data["cookyname"] = "PG_" . substr(md5($data["page_title"]),0,10);
        $data["page"] = get_cookie($data["cookyname"]);
        if (!$data["page"]) {
            $data["page"] = 1;
            set_cookie($data["cookyname"],1);
        }
	    $data['list'] = $this->editor_model->warehouses_goods($data["page"],10);  // <<-- edit this
        $data["last_page"] = count($data["list"])<10?true:false;
        //end of pagination management
        $this->index($data);
        $this->load->view('wh_goods_form',$data);
    }
    
    public function items($action = false, $id = false)
    {
        switch($action) {
            case 'save':
                $this->editor_model->save_itemprod($this->input->post(NULL,false));
                break;
            case 'new':
                $this->editor_model->new_itemprod();
                break;
            case 'del':
                $this->editor_model->delete_itemprod($id);
                break;
        }



        $data['list'] = $this->editor_model->items_production();
        $data['items'] = array("0"=>"None"); 
        $data['goods'] = array("0"=>"None");
        $this->index();
        // transforming for the dropdown lists
        foreach($this->sets_model->goods_list() as $xx) {
            $data['goods'][$xx->id] = $xx->gname;
        }
        foreach($this->sets_model->items_list() as $xx) {
            $data['items'][$xx->id] = $xx->tname;
        }
        $this->load->view('items_form',$data);
    }
    
    
    
}
    