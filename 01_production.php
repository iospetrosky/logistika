<?php
require_once("libraries/my_pdo.php");
require_once("z_local_db.php");
require_once("z_const.php");
// Production is PER DAY (game day = 1hr - 4 turns)

echo "****************************************\n";
echo "********* PRODUCTION *******************\n";
echo "****************************************\n";

class ProductionDB extends LocalDB {
    function __construct() {
        parent::__construct();
    }

    function get_production_rate($id_good, $terrain) {
        $sql = "select {$terrain}_prod as P1 from goods where id = {$id_good}";
        return $this->query_field($sql);
    }

    function retrieve_goods($player,$place,$good,$quantity) {
        //retrieve the warehouse where the good is stored (the biggest amount if stored in more that one point)
        $sql = "select id_whouse from v_player_warehouses_goods where id_place = $place and id_player = $player and id_good = $good order by avail_quantity DESC";
        $wh = $this->query_field($sql,"id_whouse");
        if(!$wh) return 0;
        $sql = "update warehouses_goods set quantity = quantity - %s where id_warehouse = %s and id_good = %s and quantity-locked >= %s";
        $sql = sprintf($sql,$quantity,$wh,$good,$quantity);
        if ($this->exec($sql)) {
            return 1;
        } else {
            return 0;
        }
    }
    
    function get_player_warehouse($id_player, $id_place) {
        //returns the warehouse with more available space of the player/place couple
        $sql = <<<SQL
            select w.id,   coalesce(w.capacity - sum(quantity),w.capacity) as free_space
            from warehouses w
            left join warehouses_goods wg on w.id = wg.id_warehouse
            where w.player_id = $id_player and w.place_id = $id_place
            group by w.id 
            order by 2 desc
SQL;
        return $this->query_field($sql,"id");
    }
    
    function store_goods($wh,$good,$quantity) {
        $sql = "insert into warehouses_goods (id_warehouse,id_good,quantity) values (?,?,?)";
        if (!$this->exec_prepared($sql,array($wh,$good,$quantity))) {
            //insert failed, try update
            $sql = "update warehouses_goods set quantity = quantity + ? where id_warehouse = ? and id_good = ?";
            $this->exec_prepared($sql,array($quantity,$wh,$good));
        }
        // test for warehouse overflow
        $total = $this->query_field("select sum(quantity) as P1 from warehouses_goods where id_warehouse = $wh");
        $avail = $this->query_field("select capacity as P1 from warehouses where id = $wh");
        return ($avail >= $total);
        // is the caller that will rollback, if necessary
    }
    
}


$db = new ProductionDB();

$rows = $db->query("select id_place, pname, id_good, gname, quantity from v_places_production order by id_place ASC");
$curr = 0; //current place
$cwh = 0; // current warehouse
echo "*** DEFAULT PRODUCTION ***\n";
foreach($rows as $r) {
    if ($r->id_place != $curr) {
        //retrieve the warehouse of the major of this place
        $curr = $r->id_place;
        $cwh = $db->query_field("select id_whouse as P1 from v_places_whouse_players where id_place=$curr and ptype='AI'");
        if (!$cwh) {
            echo "No warehouse for the major of {$r->pname}\n";
            die();
        } else {
            echo "Switching to warehouse $cwh \n";
        }
    }
    echo sprintf("Processing %s %s %d", $r->pname, $r->gname, $r->quantity) . "\n";
    $db->store_goods($cwh,$r->id_good,$r->quantity);
}

echo "*** PLAYER PRODUCTION - Prime materials ***\n";
$plcs = $db->query("select id, pname, population, ptype from places");
foreach($plcs as $plc) {
    // randomize the order of production
    $db->exec("update productionpoints set rnd_order = FLOOR(RAND() * 800) + 100 where id_place = {$plc->id}");
    // v_prodpoints_players is already sorted by gtype and rnd_order so that the materials follow the Prime-Semi-Finished workflow
    // first manage the production of prime materials (A)
    if ($prods = $db->query("select * from v_prodpoints_players where id_place = {$plc->id} and active = 1 and gtype = 'A'")) {
        foreach($prods as $pp) {
            // keep count of the workers involved
            if ($plc->population >= $pp->workers) {
                $plc->population -= $pp->workers;
                
                $rt = $db->get_production_rate($pp->id_good, $plc->ptype);
                $pr = $rt * $pp->plevel;
                $db->beginTransaction();
                if($id_warehouse = $db->get_player_warehouse($pp->id_player, $pp->id_place)) {
                    if ($db->store_goods($id_warehouse,$pp->id_good,$pr)) {
                        echo "Storage OK of $pr {$pp->gname} for {$pp->fullname} at {$pp->pname}\n";
                        $db->commit();
                    } else {
                        echo "Error storing $pr {$pp->gname} for {$pp->fullname} at {$pp->pname} - WH full!\n";
                        $db->rollback();
                    }
                } else {
                    $db->rollback();
                    echo "No storage space found for {$pp->fullname} at {$pp->pname}\n";
                }
            } else {
                echo "No more workers\n";
                break;
            }
        }
    }
}

echo "*** PLAYER PRODUCTION - Products ***\n";
for($rounds=0; $rounds<2; $rounds++) {
    echo "Round: " . $rounds . "\n";
    foreach($plcs as $plc) {
        $db->exec("update productionpoints set rnd_order = FLOOR(RAND() * 800) + 100 where id_place = {$plc->id}");
        // get the ACTIVE production points in this place 
        $sql = "select p.id, p.rnd_order, p.id_good, g.gtype, g.workers
                    from productionpoints p
                    inner join goods g on p.id_good=g.id
                    where g.gtype in ('B','C') and p.id_place = {$plc->id} and active = 1
                    order by g.gtype, rnd_order";
        //echo $sql . "\n";
        if($items = $db->query($sql)) {
            foreach($items as $it) {
                if ($plc->population >= $it->workers) {
                    $plc->population -= $it->workers;
                    $prods = $db->query("select * from v_prodpoints_players where id = {$it->id}");
                    $db->beginTransaction();
                    $ok = true;
                    //try to retrieve all necessary goods from the storage points
                    foreach($prods as $pp) {
                        if ($db->retrieve_goods($pp->id_player,$pp->id_place,$pp->req_id, MIN_QUANTITY * $pp->plevel) != 1) {
                            $db->rollback();
                            $ok = false;
                            echo "Error producing {$pp->gname} for {$pp->fullname} at {$pp->pname} - no materials!\n";
                            break;
                        } 
                    }
                    if ($ok) {
                        $pr = $pp->prod_quantity * $pp->plevel;
                        if($id_warehouse = $db->get_player_warehouse($pp->id_player, $pp->id_place)) {
                            if ($db->store_goods($id_warehouse,$pp->id_good,$pr)) {
                                echo "Production OK of $pr {$pp->gname} for {$pp->fullname} at {$pp->pname}\n";
                                $db->commit();
                            } else {
                                // this should never happen since I removed materials before
                                // unless something is bought from the marketplace
                                echo "Error storing $pr {$pp->gname} for {$pp->fullname} at {$pp->pname} - WH full!\n";
                                $db->rollback();
                            }
                        } else {
                            $db->rollback();
                            echo "No storage space found for {$pp->fullname} at {$pp->pname}\n";
                        }
                    }
                } else {
                    echo "No more workers\n";
                    break;
                }
            }
        }
    }
} // rounds of production



echo "*** PLACES GROWTH ***\n";
// a place grows by 100 when food > 1000 + (population+100) *4
// that is the place can survive next turn if food does not come or is not produced
if ($rows = $db->query("select id_place, pname, id_whouse from v_major_warehouses_goods where avail_quantity > (population+100)*4+1000 and id_good = 1")) {
    foreach($rows as $r) {
        echo sprintf("Attempt to grow for place: %s ", $r->pname);
        $db->beginTransaction();
        $sql = "update places set population = population + 100 where id = ?";
        if ($db->exec_prepared($sql,array($r->id_place)) == 1) {
            $sql = "update warehouses_goods set quantity = quantity - ? where id_warehouse = ? and id_good = ? and quantity >= ?";
            $f = $db->exec_prepared($sql, array(1000,$r->id_whouse,FOOD,1000)); // 1000 food(1)
            $i = $db->exec_prepared($sql, array(20,$r->id_whouse,IRON,20)); // 20 iron(2)
            $w = $db->exec_prepared($sql, array(50,$r->id_whouse,WOOD,50)); // 50 wood(3)
            $b = $db->exec_prepared($sql, array(50,$r->id_whouse,BRICK,50)); // 50 brick(5)
            if ($f+$i+$w+$b == 4) {
                $db->commit();
                echo "... success\n";
            } else {
                $db->rollback();
                echo "... failed. Missing ";
                echo $f == 0?"Food ":"";
                echo $i == 0?"Iron ":"";
                echo $w == 0?"Wood ":"";
                echo $b == 0?"Bricks ":"";
                echo "\n";
            }
        } else {
            // this should NEVER happen actually
            $db->rollback();
        }
    }
} else {
    echo "No places have the requirements to attempt a growth\n";
}







