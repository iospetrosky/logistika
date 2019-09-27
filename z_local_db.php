<?php
class LocalDB extends my_pdo {
	private $__admin = false;
	protected  $user_id = 0;

	function __construct() {
        //let's avoid to change the code at home/work
        switch (gethostname()) {
            case "raspberrypi": 
                parent::__construct("mysql","localhost;charset=utf8","logistika","pi","emberlee1");
                break;
            case "L-NAY-60623": //office PC
                parent::__construct("sqlite","/users/LPEDR/Documents/SAP/Util/logistika.db");
                break;
        }
	}
    
    function get_default_price($id_good, $type) {
        return $this->query_field("select {$type}_prices as P1 from goods where id = $id_good");
    }
    
    function get_food_equivalent() {
        //returns the goods that can be traded as food
        return $this->query("select id_original, quantity from equivalent where id_equiv = 1"); 
    }
    
    function get_all_places() {
        return $this->query("select * from places");
    }
    
}
