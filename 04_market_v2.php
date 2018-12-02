<?php
require_once("libraries/my_pdo.php");
require_once("z_local_db.php");
require_once("z_const.php");

echo "****************************************\n";
echo "****MARKETPLACE MANAGEMENT**************\n";
echo "****************************************\n";

class MarketDB extends LocalDB {
    
    
    function __construct() {
        parent::__construct();
    }
    
    function find_offer($id_place, $id_player, $op_type, $id_good) {
        $sql = "select id, amount, price from marketplace where " .
                   "id_place = $id_place and id_player = $id_player and op_type = '$op_type' " . 
                   "and id_good = $id_good and op_scope = 'L'";
        return $this->query($sql);
    }
    
    function find_buy_offer($id_place, $id_player, $id_good) {
        return $this->find_offer($id_place,$id_player,'B',$id_good);
    }
    
    function get_whouse_goods() {
        return $this->query("select * from v_major_warehouses_goods");
    }
    
    function insert_order($ord) {
        // test if order exists
        $i = $this->query_field_prepared("select id  from marketplace where id_place = ? and id_player = ? and op_type = ? and op_scope = ? and id_good = ?",
                                            array($ord->id_place, $ord->id_player, $ord->op_type, $ord->op_scope, $ord->id_good), "id");
        if ($i){
            $nord = new stdClass();
            
            $nord->id = $i;
            $nord->quantity = (int)$ord->quantity;
            $nord->price = $ord->price;
            $this->update_object("marketplace",$nord);
        } else {
            $ord->quantity = (int)$ord->quantity;
            $this->insert_object("marketplace",$ord);
        }
    }
    
    function retire_all_orders($id_place, $id_player, $id_good) {
        // sets all the default orders to zero
        $t = $this->exec("update marketplace set quantity = 0 where id_place = $id_place and id_player = $id_player and id_good = $id_good " . 
                    "and op_type = 'B' and op_scope = 'L'");
        if (!$t) {
            // there is no default order for this item
            $mkt = new stdClass();
            $mkt->id_place = $id_place;
            $mkt->id_player = $id_player;
            $mkt->op_type = 'B';
            $mkt->op_scope = 'L';
            $mkt->id_good = $id_good;
            $mkt->quantity = 0;
            $mkt->price = 0; 
            $this->insert_order($mkt);
        }
    }
    /*
    function get_place_ids_market() {
        // returns an array with the place_ids that have something in the marketplace
        return $this->column("select distinct id_place as P1 from marketplace order by 1 asc");
    }
    */
    
    // stock market management
    function get_buy_orders($id_place, $id_good) {
        $sql = "select id, id_place, id_player, id_good, quantity, price from marketplace " .
            "where id_place = $id_place and id_good = $id_good and quantity > 0 and price > 0 " .
            "and op_type = 'B' and op_scope = 'L' order by price desc, id asc";
        return $this->query($sql);
    }
    
    function get_sell_orders($id_place, $id_good, $price_limit = 999999) {
        $sql = "select id, id_place, id_player, id_good, quantity, price from marketplace " .
            "where id_place = $id_place and id_good = $id_good and price < $price_limit and quantity > 0" .
            "and op_type = 'S' and op_scope = 'L' order by price asc, id asc";
        return $this->query($sql);
    }

    function execute_buy($id_buyer, $quantity, $sell) { 
        // $sell represents the sell order from which I buy a specific quantity 
        $remaining = 0;
        $this->beginTransaction();
        if ($quantity >= $sell->quantity) {
            $remaining = $quantity - $sell->quantity;
            $this->exec("delete from marketplace where id = {$sell->id}");
        } elseif ($buy->quantity < $sell->quantity) {
            $this->exec("update marketplace set quantity = quantity - {$buy->quantity} where id = {$sell->id}");
        } 

        $this->exec("update players set gold = gold + " . (string)($sell->quantity * $sell->price) . " where id = {$sell->id_player}");
        $this->exec("update players set gold = gold - " . (string)($sell->quantity * $sell->price) . " where id = {$id_buyer}");
        //put the goods in the warehouse of the buyer
        $wh = $this->query_field("select id_whouse as P1 from v_places_whouse_players where " . 
                                    "id_player = $id_buyer and id_place = {$sell->id_place}");
        $this->exec("update warehouses_goods set quantity = quantity + {$sell->quantity} where id_warehouse = $wh and id_good = {$sell->id_good}");
        //remove goods from the warehouse of the seller 
        $wh = $this->query_field("select id_whouse as P1 from v_places_whouse_players where " . 
                                    "id_player = {$sell->id_player} and id_place = {$sell->id_place}");
        $this->exec("update warehouses_goods set quantity = quantity - {$sell->quantity}, locked = locked - {$sell->quantity} where id_warehouse = $wh and id_good = {$sell->id_good}");

        $this->commit();
        return $remaining;
    }
    
    
    
    function get_goods_at_marketplace($id_place) {
        return $this->column("select distinct id_good as P1 from marketplace where id_place = $id_place");
    }
    
}


$db = new MarketDB();


/*
The major tries to buy the necessary to grow, at a reasonable price and a little surplus
and the necessary to avoid the downsizeing at ANY price
*/

$goods = $db->get_whouse_goods();
foreach ($goods as $g) {
    $db->beginTransaction();
    // delete the existing orders and replace with new ones based on the new values of the stores
    $db->retire_all_orders($g->id_place, $g->id_player, $g->id_good);

    $mkt = new stdClass();
    $mkt->id_place = $g->id_place;
    $mkt->id_player = $g->id_player;
    $mkt->op_type = 'B';
    $mkt->op_scope = 'L';
    $mkt->id_good = $g->id_good;
    
    // place the growth order
    // determine the quantity needed to grow and place an order at the default price
    switch($g->id_good) {
        case FOOD: $mkt->quantity = ($g->population+100)*4 + 1000 - $g->avail_quantity; break;
        case IRON: $mkt->quantity = 20 - $g->avail_quantity; break;
        case WOOD: $mkt->quantity = 50 - $g->avail_quantity; break;
        case BRICK: $mkt->quantity = 50 - $g->avail_quantity; break;
        default: $mkt->quantity = 0;
    }
    if ($mkt->quantity > 0) {
        // else the min production of food is enough
        echo "Growth material {$g->gname} for {$g->pname}\n";
        /*
        Only majors store groceries as FOOD.
        */
        if ($g->id_good == FOOD) {
            // try to buy some equivalents
            $equivs = $db->get_food_equivalent();
            $qty = $mkt->quantity;
            foreach($equivs as $eq) {
                $mkt->id_good = $eq->id_original;
                $mkt->quantity = $qty / $eq->quantity;
                $mkt->price = $db->get_default_price($g->id_good, $g->ptype); 
                $db->insert_order($mkt);
            }
        } else {
            $mkt->price = $db->get_default_price($g->id_good, $g->ptype); 
            $db->insert_order($mkt);
        }
    }
    $db->commit();
}

/*
Now the major of each place tries to buy the materials to avoid starvation
*/
// transform to FOOD
$sql = "select wg.id_warehouse, wg.id_good, g.gname, wg.quantity as wh_stored, w.player_id, p.fullname, e.quantity as eq_conv
        from warehouses_goods wg
        inner join warehouses w on wg.id_warehouse = w.id
        inner join players p on w.player_id = p.id
        inner join equivalent e on wg.id_good = e.id_original
        inner join goods g on e.id_original = g.id
        where p.ptype = 'AI' and e.id_equiv = 1
";
if($convs = $db->query($sql)) {
    foreach($convs as $conv) {
        $db->beginTransaction();
        $db->exec_prepared("insert into warehouses_goods (id_warehouse, id_good) values (?,?)",array($conv->id_warehouse,FOOD));
        $db->exec_prepared("update warehouses_goods set quantity = quantity + ? where id_warehouse = ? and id_good = ?",
                            array($conv->wh_stored*$conv->eq_conv, $conv->id_warehouse, FOOD));
        $db->exec_prepared("delete from warehouses_goods where id_warehouse = ? and id_good = ?",
                            array($conv->id_warehouse, $conv->id_good));
        $db->commit();
        echo sprintf("Converted %d %s to %d Food for %s\n",$conv->wh_stored, $conv->gname,$conv->wh_stored*$conv->eq_conv,$conv->fullname);
    }
}

//buy from local market
foreach($db->get_all_places() as $place){
    echo "Non starving purchases for {$place->pname} - population {$place->population}\n";
    foreach(array(FOOD,IRON,WOOD) as $good) {
        if($good == FOOD) {
            $needed = $place->population;
            $equivs = $db->get_food_equivalent();
            foreach($equivs as $eq) {
                $maxprice = $db->get_default_price($eq->id_original, $place->ptype) * 3;
                $equiv_needed = (int)($needed / $eq->quantity);
                if (($equiv_needed > 0) && ($orders = $db->get_sell_orders($place->id,$eq->id_original,$maxprice))) {
                    //if there are orders, the major of THIS place buys
                    foreach($orders as $ord) {
                        $equiv_needed = $db->execute_buy($place->major,$equiv_needed,$ord);
                        if ($equiv_needed == 0) { break; }
                    }
                }
                $needed = $equiv_needed * $eq->quantity;
            }
        } else {
            //$needed = depends on the type of material
            $maxprice = $db->get_default_price($good, $place->ptype) * 3;
            if ($orders = $db->get_sell_orders($place->id,$good,$maxprice)) {
                //if there are orders, the major buys
            }
        }
    }    
}
