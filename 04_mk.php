<?php
require_once("libraries/my_pdo.php");
require_once("z_local_db.php");
require_once("z_const.php");

echo "****************************************\n";
echo "****MARKETPLACE MANAGEMENT**************\n";
echo "****************************************\n";

class MarketDB extends LocalDB {
    
    function get_workers_place($id_place) {
        //returns the number of workers required by the place
        return $this->query_field("select sum(gd.workers) as P1 from productionpoints pp ". 
                                "inner join goods gd on pp.id_good=gd.id " .
                                "where pp.id_place = $id_place group by pp.id_place ");
    }
    
    function get_price_of($good, $terrain = 'plains') {
        return $this->query_field("select {$terrain}_prices as P1 from goods where id = $good");
    }
    
    function get_buy_orders($id) {
        return $this->query("select * from marketplace where op_type='B' and op_scope='L' and id_place=$id and quantity > 0 order by price DESC");
    }
    
    function get_matching_sell_orders($id_place, $id_good, $price_max) {
        echo " -- Place: " . $id_place, "  Good: ", $id_good, " Price: ",$price_max, "\n";
        //if ($id_good == FOOD) {
            // match all the equivalents of Food
            $sql = "select * from v_marketplace_equiv where op_type='S' and op_scope='L' and equiv_quantity>0 and id_place=%s and id_good in (select id_original from equivalent where id_equiv = %s) and equiv_price<=%s";
        //} else {
        //    $sql = "select * from marketplace where op_type='S' and op_scope='L' and quantity>0 and id_place=%s and id_good=%s and price<=%s";
        //}
        $sql = $sql . " order by price ASC";
        //echo sprintf($sql, $id_place,$id_good,floatval($price_max)) . "\n";
        //die(sprintf($sql, $id_place,$id_good,floatval($price_max)));
        return $this->query(sprintf($sql, $id_place,$id_good,floatval($price_max)));
    }
    
    function move_goods($location, $from, $to, $what, $quantity) {
        echo " -- Moving $quantity of $what from $from to $to \n";
//the seller (from) must have the quantity of good locked in one of the warehouses of the current location
$sql_1 = <<<SQL
    select wg.id as id, wg.id_good,wg.quantity,wg.locked
    from warehouses w
    left join warehouses_goods wg on w.id = wg.id_warehouse
    where w.player_id = $from and wg.id_good=$what and quantity >= $quantity 
        and locked >= $quantity  and w.place_id = $location
SQL;
//the buyer (to) must have a warehouse capable of receiving the quantity of goods
$sql_2 = <<<SQL
    select w.id as whid, w.capacity, sum( wg.quantity) as tot_quantity
    from warehouses w
    inner join warehouses_goods wg on w.id = wg.id_warehouse
    where w.player_id = $to and w.place_id = $location
    group by w.id, w.capacity
    having capacity - tot_quantity >= $quantity
SQL;
        //get the seller warehouse record. Can be any (static or not) having the goods available
        //get the first available
        if ($origin_wh = $this->query($sql_1,true)) {
            //print_r($origin_wh);
            //get the first buyer warehouse with enough space
            if ($dest_wh_id = $this->query_field($sql_2,"whid")) {
                //print_r($dest_wh_id);
                //try to retrieve an existing record for the good
                if ($dest_wh = $this->query("select * from warehouses_goods where id_warehouse = $dest_wh_id and id_good = $what",true)) {
                    //print_r($dest_wh);
                    $dest_wh->quantity += $quantity;
                    if (!$this->update_object("warehouses_goods",$dest_wh)) return false;
                } else {
                    //echo "new wh entry";
                    $dest_wh = new stdClass();
                    $dest_wh->id_warehouse = $dest_wh_id;
                    $dest_wh->id_good = $what;
                    $dest_wh->quantity = $quantity;
                    if (!$this->insert_object("warehouses_goods",$dest_wh)) return false;
                }
                $origin_wh->locked -= $quantity;
                $origin_wh->quantity -= $quantity;
                $this->update_object("warehouses_goods",$origin_wh);
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }
    
    function make_payment($from, $to, $gold) {
        echo " -- Payment of $gold from $from to $to \n";
        $sql_1 = "update players set gold = gold + ? where id = ?";
        $sql_2 = "update players set gold = gold - ? where id = ? and gold >= ?";
        $s1 = $this->exec_prepared($sql_1,array($gold,$to));
        $s2 = $this->exec_prepared($sql_2,array($gold,$from,$gold));
        return ($s1 & $s2);
    }
    
    function place_non_starving_orders($place) {
        echo "Placing NON-STARVING buy ORDERS\n";
        $mkt = new stdClass();
        $mkt->id_place = $place->id;
        $mkt->id_player = $place->major;
        $mkt->op_type = 'B'; //buy
        $mkt->op_scope = 'L'; //Local
        
        /* Since majors trade the raw materials they produce, they don't buy them anymore
           since these are also not consumed at each turn. Cities consume only food  */
        
        //$goods = array(FOOD,WOOD,IRON);
        $goods = array(FOOD);
        $this->beginTransaction();
        
        foreach($goods as $g) {
            $avail = $this->query_field("select avail_quantity as P1 from v_major_warehouses_goods where " . 
                                        "id_place = {$place->id} and id_good = $g and id_player = {$place->major}");
            switch($g) {
                case FOOD: $mkt->quantity = $place->population * 12 - $avail; break;
                //case IRON: $mkt->quantity = IRON_XROUND * 12 - $avail; break;
                //case WOOD: $mkt->quantity = WOOD_XROUND * 12 - $avail; break;
                default: $mkt->quantity = 0;
            }
            $mkt->id_good = $g;
            if ($mkt->quantity > 0) {
                // else the min production of food is enough
                $mkt->price = floor($this->get_price_of($g) * 1.1); // buy at normal price + 10%
                $this->insert_object("marketplace",$mkt);
            }
        }
        $this->commit();
    }
    
    function place_growth_buy_orders($place) {
        echo "Placing GROWTH buy orders\n";
        //places orders for Food, Wood, Iron, Bricks
        $mkt = new stdClass();
        $mkt->id_place = $place->id;
        $mkt->id_player = $place->major;
        $mkt->op_type = 'B'; //buy
        $mkt->op_scope = 'L'; //Local
        
        $this->beginTransaction();
        /*
        For growth we still have the buy orders of prime materials (because not every city produces everything)
        */
        $goods = array(FOOD,WOOD,IRON,BRICK);
        foreach($goods as $g) {
            $avail = $this->query_field("select avail_quantity as P1 from v_major_warehouses_goods where " . 
                                        "id_place = {$place->id} and id_good = $g and id_player = {$place->major}");
            switch($g) {
                case FOOD: $mkt->quantity = (1000 + ($place->population+100) *4) - $avail; break;
                case IRON: $mkt->quantity = 20 - $avail; break;
                case WOOD: $mkt->quantity = 50 - $avail; break;
                case BRICK: $mkt->quantity = 50 - $avail; break;
                default: $mkt->quantity = 0;
            }
            $mkt->id_good = $g;
            if ($mkt->quantity > 0) {
                // else the min production of food is enough
                $mkt->price = $this->get_price_of($g); // buy at normal price
                $this->insert_object("marketplace",$mkt);
            }
        }
        $this->commit();
    }
    
    function __construct() {
        parent::__construct();
    }
}


$db = new MarketDB();

/*
The major places a buy request for the items needed for a growth.
A growth is needed when the people used in production is almost at the limit
*/
$places = $db->query("select * from places");

echo "*** GROWTH ORDERS ***\n";
foreach($places as $place) {
    $db->exec("delete from marketplace where id_place = {$place->id} and id_player = {$place->major} and op_type = 'B'");
    // also delete all the SELL orders that will be reinserted
    // and restore all the locked materials
    $maj_wh = $db->query_field("select id as P1 from warehouses where player_id = {$place->major} and place_id = {$place->id}");
    $db->exec("delete from marketplace where id_place = {$place->id} and id_player = {$place->major} and op_type = 'S'");
    $db->exec("update warehouses_goods set locked = 0 where id_warehouse = $maj_wh and locked <> 0");
    
    $workers = $db->get_workers_place($place->id);
    echo sprintf("%s population %d workers %d\n", $place->pname, $place->population, $workers);
    if ($workers > $place->population-20) {
        $db->place_growth_buy_orders($place);
    }
    $db->place_non_starving_orders($place);
}

/*
The major places sell orders for goods (not FOOD) that are produced by the city
IF there's no BUY order for the same material (meaning that it's needed so don't sell the storage)
*/
echo "*** MAJOR SURPLUS ORDERS ***\n";
foreach($places as $place) {
    $maj_wh = $db->query_field("select id as P1 from warehouses where player_id = {$place->major} and place_id = {$place->id}");
    $goods = $db->query("select wg.id, wg.id_good, wg.quantity, g.gname, g.plains_prices as price " .
                        "from warehouses_goods wg " . 
                        "inner join goods g on wg.id_good = g.id " . 
                        "where wg.id_warehouse = $maj_wh and wg.id_good <> 1 and wg.quantity > 100");
    $order = new stdClass();
    $order->id_place = $place->id;
    $order->id_player = $place->major;
    $order->op_type = 'S';
    if ($goods) {
        $db->beginTransaction();
        foreach($goods as $good) {
            $order->id_good = $good->id_good;
            $order->quantity = $good->quantity - 100;
            $order->price = $good->price;
            echo "Major {$place->major} ordering {$order->quantity} {$good->gname} \n";
            $db->insert_object('marketplace',$order);
            $db->exec(sprintf("update warehouses_goods set locked = %s where id = %s",$order->quantity,$good->id));
        }
        $db->commit();
    }
}

// Stock market style loop
echo "*** Stock market loop ***\n";
foreach($places as $place) {
    /*
    For each buy order check if there's a corresponding sell order at same or
    lower price. 
    */
    if ($buy_orders = $db->get_buy_orders($place->id)) {
        echo "Processing orders for " . $place->pname . "\n";
        foreach($buy_orders as $buy) {
            echo "Buy: " . $buy->id;
            if($sells = $db->get_matching_sell_orders($buy->id_place, $buy->id_good, $buy->price)) {
                foreach($sells as $sell) {
                    echo " Sell: " . $sell->id;
                    $db->beginTransaction();
                    if ($buy->quantity > $sell->equiv_quantity) {
                        //equiv quantity and price are identical for products that don't have equivalents
                        //move the corresponding amount of money between buyer and seller
                        if ($db->make_payment($buy->id_player, $sell->id_player, $sell->quantity * $sell->price)===false) {
                            //no money?
                            $db->rollback();
                            echo " failed: no money\n";
                        } else {
                            //move goods between warehouses
                            if ($db->move_goods($place->id, $sell->id_player, $buy->id_player, $sell->id_good, $sell->quantity)) {
                                //update also the orders
                                $buy->quantity -= $sell->quantity;
                                $sell->quantity = 0;
                                $db->exec(sprintf("update marketplace set quantity=quantity-%s where id=%s",$buy->quantity,$buy->id));
                                $db->exec(sprintf("update marketplace set quantity=0 where id=%s",$sell->id));
                                $db->commit();
                                echo " closed partial buy\n";
                            } else {
                                $db->rollback();
                                echo " failed: no goods/space\n";
                            }
                        }
                        //since buy has still a quantity, we go on with the sells
                    } else {
                        $real_quantity = $buy->quantity;
                        $real_price = $sell->price * $real_quantity;
                        if($sell->id_good != $sell->id_equiv) {
                            //the quantity actually moved must be converted in case of equivalences
                            $mult = $sell->equiv_quantity / $sell->quantity; //I avoid getting it from the DB, I just invert the formula
                            $real_quantity = ceil($buy->quantity / $mult); //round UP to grant at least 1 buy
                            $real_price = $real_quantity * $sell->price;
                        }
                        
                        
                        if ($db->make_payment($buy->id_player, $sell->id_player, $real_price)===false) {
                            //no money?
                            $db->rollback();
                            echo " failed: no money\n";
                        } else {
                            if ($db->move_goods($place->id, $sell->id_player, $buy->id_player, $sell->id_good, $real_quantity)) {
                                //update also the orders
                                $sell->quantity -= $real_quantity;
                                $buy->quantity = 0;
                                $db->update_object("marketplace",$sell);
                                $db->update_object("marketplace",$buy);
                                $db->commit();
                                echo " closed full buy\n";
                            } else {
                                $db->rollback();
                                echo " failed: no goods/space\n";
                            }
                        }
                        break; //the buy order is completed, no need to check other sells
                    }
                } // loop on sells
            } else { // sells exist
                echo " no sells available\n";
            }
        } // loop on buys
    }
}

//remove all orders with 0 quantity
//transform all equivalents in majors' warehouses
echo "Cleanups\n";
$db->exec("delete from marketplace where quantity = 0");
$db->exec("delete from warehouses_goods where quantity = 0 and locked = 0"); 

if ($goods = $db->query("select * from v_major_warehouses_goods where id_good in (select id_original from equivalent where id_equiv = " . FOOD . ")")) {
    foreach($goods as $good) {
        echo sprintf("Transforming %s %s to Food for %s\n",$good->avail_quantity,$good->gname,$good->fullname);
        $db->beginTransaction();
        $a = $db->exec("update warehouses_goods set quantity = quantity + {$good->avail_quantity} where id_warehouse = {$good->id_whouse} and id_good = " . FOOD);
        $b = $db->exec("delete from warehouses_goods where id_warehouse = {$good->id_whouse} and id_good = $good->id_good");
        if ($a + $b == 2) {
            $db->commit();
        } else {
            $db->rollback();
            echo "Something went wrong\n";
        }
    }
}
