<?php
require_once("../inc/my_pdo.php");
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
        $this->insert_object("marketplace",$ord);
    }
    
    function retire_all_orders($id_place, $id_player, $id_good) {
        $this->exec("delete from marketplace where id_place = $id_place and id_player = $id_player and id_good = $id_good " . 
                    "and op_type = 'B' and op_scope = 'L'");
    }
    
    function get_place_ids_market() {
        // returns an array with the place_ids that have something in the marketplace
        return $this->column("select distinct id_place as P1 from marketplace order by 1 asc");
    }
    
    // stock market management
    function get_buy_orders($id_place, $id_good, $at_market) {
        if ($at_market) {
            $sql = "select id, id_place, id_player, id_good, quantity, price from marketplace " .
                "where id_place = $id_place and id_good = $id_good " .
                "and op_type = 'B' and op_scope = 'L' order by price desc, id asc";
        } else {
            $sql = "select id, id_place, id_player, id_good, quantity, price from marketplace " .
                "where id_place = $id_place and id_good = $id_good and price < 999999 " .
                "and op_type = 'B' and op_scope = 'L' order by price desc, id asc";
        }
        return $this->query($sql);
    }
    
    function get_sell_orders($id_place, $id_good, $at_market) {
        if ($at_market) {
            $sql = "select id, id_place, id_player, id_good, quantity, price from marketplace " .
                "where id_place = $id_place and id_good = $id_good " .
                "and op_type = 'S' and op_scope = 'L' order by price asc, id asc";
        } else {
            $sql = "select id, id_place, id_player, id_good, quantity, price from marketplace " .
                "where id_place = $id_place and id_good = $id_good and price > 0 " .
                "and op_type = 'S' and op_scope = 'L' order by price asc, id asc";
        }
        return $this->query($sql);
    }

    function execute_buy($buy, $sell, $price) {
        $this->beginTransaction();
        if ($buy->quantity > $sell->quantity) {
            $this->exec("delete from marketplace where id = {$sell->id}");
            $this->exec("update marketplace set quantity = quantity - {$sell->quantity} where id = {$buy->id}");
            $this->exec("update players set gold = gold + " . (string)($sell->quantity * $price) . " where id = {$sell->id_player}");
            $this->exec("update players set gold = gold - " . (string)($sell->quantity * $price) . " where id = {$buy->id_player}");
            $wh = $this->query_field("select id_whouse as P1 from v_places_whouse_players where " . 
                                        "id_player = {$buy->id_player} and id_place = {$buy->id_place}");
            $this->exec("update warehouses_goods set quantity = quantity + {$sell->quantity} where id_warehouse = $wh and id_good = {$buy->id_good}");
        } elseif ($buy->quantity < $sell->quantity) {
            $this->exec("delete from marketplace where id = {$buy->id}");
            $this->exec("update marketplace set quantity = quantity - {$buy->quantity} where id = {$sell->id}");
            $this->exec("update players set gold = gold + " . (string)($buy->quantity * $price) . " where id = {$sell->id_player}");
            $this->exec("update players set gold = gold - " . (string)($buy->quantity * $price) . " where id = {$buy->id_player}");
            $wh = $this->query_field("select id_whouse as P1 from v_places_whouse_players where " . 
                                        "id_player = {$buy->id_player} and id_place = {$buy->id_place}");
            $this->exec("update warehouses_goods set quantity = quantity + {$buy->quantity} where id_warehouse = $wh and id_good = {$buy->id_good}");
        } elseif ($buy->quantity == $sell->quantity) {
            $this->exec("delete from marketplace where id = {$buy->id}");
            $this->exec("delete from marketplace where id = {$sell->id}");
            $this->exec("update players set gold = gold + " . (string)($buy->quantity * $price) . " where id = {$sell->id_player}");
            $this->exec("update players set gold = gold - " . (string)($buy->quantity * $price) . " where id = {$buy->id_player}");
            $wh = $this->query_field("select id_whouse as P1 from v_places_whouse_players where " . 
                                        "id_player = {$buy->id_player} and id_place = {$buy->id_place}");
            $this->exec("update warehouses_goods set quantity = quantity + {$buy->quantity} where id_warehouse = $wh and id_good = {$buy->id_good}");
        }
        $this->commit();
    }
    
    
    
    function get_goods_at_marketplace($id_place) {
        return $this->column("select distinct id_good as P1 from marketplace where id_place = $id_place");
    }
    
}

function process_goods_at($p) {
    global $db;
    
    if ($goods = $db->get_goods_at_marketplace($p)) {
        foreach($goods as $g) {
            echo "Good $g\n";
            $executed = false;
            $buy = $db->get_buy_orders($p, $g, false);
            $sell = $db->get_sell_orders($p, $g, false);
            // first check if there are potential matching trades
            if (($buy) && ($sell)) {
                foreach($sell as $s) {
                    if ($s->price <= $buy[0]->price) {
                        $db->execute_buy($buy[0], $s, $s->price);
                        echo "Executed trade at {$s->price}\n";
                        $executed = true;
                        break;
                    }
                }
            }
            if ((!$executed) && ($buy)) {
                $sell = $db->get_sell_orders($p, $g, true);
                if (($buy) && ($sell)) {
                    if($sell[0]->price == 0) {
                        $db->execute_buy($buy[0], $sell[0], $buy[0]->price);
                        echo "Executed - at buy market {$buy[0]->price}\n";
                        $executed = true;
                    }
                }
            }
            if (!$executed) {
                $buy = $db->get_buy_orders($p, $g, true);
                $sell = $db->get_sell_orders($p, $g, false);
                if (($buy) && ($sell)) {
                    if ($buy[0]->price == 999999) {
                        $db->execute_buy($buy[0], $sell[0], $sell[0]->price);
                        echo "Executed - at sell market {$sell[0]->price}\n";
                        $executed = true;
                    }
                }
            }
            
            if ($executed) {
                process_goods_at($p);
                break; // need to re-query
            } 
        }
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
    //if ($g->population > 100) {
        switch($g->id_good) {
            case FOOD: $mkt->quantity = ($g->population+100)*4 + 1000 - $g->avail_quantity; break;
            case IRON: $mkt->quantity = 20 - $g->avail_quantity; break;
            case WOOD: $mkt->quantity = 50 - $g->avail_quantity; break;
            case BRICK: $mkt->quantity = 50 - $g->avail_quantity; break;
            default: $mkt->quantity = 0;
        }
        if ($mkt->quantity > 0) {
            echo "Growth material {$g->gname} for {$g->pname}\n";
            // else the min production of food is enough
            $mkt->price = $db->get_default_price($g->id_good, $g->ptype); 
            $db->insert_order($mkt);
        }
    //}
    // place the NO starving order
    // are there materials for the next 12 rounds (3 turns)?
    switch($g->id_good) {
        case FOOD: $mkt->quantity = $g->population * 12 - $g->avail_quantity; break;
        case IRON: $mkt->quantity = IRON_XROUND * 12 - $g->avail_quantity; break;
        case WOOD: $mkt->quantity = WOOD_XROUND * 12 - $g->avail_quantity; break;
        default: $mkt->quantity = 0;
    }
    if ($mkt->quantity > 0) {
        echo "No Starving material {$g->gname} for {$g->pname}\n";
        // else the min production of food is enough
        $mkt->price = 999999; // buy at any cost
        $db->insert_order($mkt);
    }
    $db->commit();
}

/*
Matching orders
for each place and material get the list of buy sorted desc and sell sorted asc
*/
if ($places = $db->get_place_ids_market()) {
    foreach($places as $p) {
        echo "Processing goods at $p \n";
        process_goods_at($p);
    }
}




