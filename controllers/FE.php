<?php
class FE extends CI_Controller {
/*
This is a generic field editor
Given a table a field and a value it performs a direct update
and returns the result
*/
    public function __construct()
    {
        parent::__construct();
        $this->load->model('fieldeditor_model');
    }
    
    public function A($id)  {
        $table = $this->input->post_get('table');
        $field = $this->input->post_get('field');
        $value = $this->input->post_get('newval');
        $ret_id = $this->input->post_get('ret_id');
        $r = $this->fieldeditor_model->exec_edit($table,$field,$value,$id);
        $json = new stdClass();
        $json->message = $r?'OK':'Error';
        $json->item = $ret_id;
        echo json_encode($json);
    }
}