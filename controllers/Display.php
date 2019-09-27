<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Display extends CI_Controller {
    
    public function __construct()
    {
        parent::__construct();
        $this->load->helper('html_gen_helper');
        $this->load->library('table');
        $this->load->model('display_model');
        $this->load->model('sets_model');
    }
    
    public function index($data = false)
    {
        // load all the data needed in the views in variables to be passed as second parameter
        //$data['tile_sets'] = $this->display_model->some_method(); 

        $data["url"] = explode("/", $this->uri->uri_string());
        $this->load->view('intro',$data);
    }
    
    public function map($action = '', $id = 0) {
        $data["mapname"] = "demo";
        $data["hex_wdt"] = 90;
        $data["hex_hgt"] = 104;
        $data["map_wdt"] = 630;
        $data["map_hgt"] = 800;
        
        $data["tiles"] = $this->display_model->getmap($data["mapname"]);
        $data['action'] = $action;
        switch($action) {
            case 'draw':
                $data['routepath'] = $this->display_model->get_routepath($id);
                $data['path_id'] = $id;
                break;
            case 'showtransp':
                $data['transports'] = $this->display_model->get_transport_infos($id);
                break;
        }
        $this->index($data);
        $this->load->view('map_view',$data);
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

    // these are actually NOT display functions
    public function add_tile($id_route, $token) {
        //adds the token in the specified path 
        $res = $this->display_model->add_tile_to_path($id_route, $token);
        echo $res;
    }
    
    public function del_tile($id_tile) {
        //removes the tile from the path
        $this->display_model->del_tile_from_path($id_tile);
    }
    
    public function update_sequence($id_tile, $newseq)     {
        // changes the sequence of a tile
        $json = new stdClass();
        if ($this->display_model->update_sequence($id_tile, $newseq)) {
            $json->retcode = 'OK';
            $json->id = $id_tile;
            $json->line = "#line_" . $id_tile; //returns the name of the line to be restored in normal color
        } else {
            $json->retcode = 'X1';
            $json->message =  "Something bad happened. Reload the page";
        }
        echo json_encode($json);
    }
    
    
    
    
}
