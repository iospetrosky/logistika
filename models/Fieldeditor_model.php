<?php
// this is the DATA layer. No output here!!!

defined('BASEPATH') OR exit('No direct script access allowed');

class Fieldeditor_model extends CI_Model {

    public function __construct()    {
        //$this->load->database(); // autoloaded
    }

    public function exec_edit($table,$field,$value,$id) {
        $this->db->set($field, $value);
        $this->db->where("id",$id);
        $this->db->update($table);
        return $this->db->affected_rows();
    }
    
}    