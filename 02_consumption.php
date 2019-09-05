<?php
require_once("libraries/my_pdo.php");
require_once("z_local_db.php");
require_once("z_const.php");

echo "****************************************\n";
echo "******** CONSUMPTION *******************\n";
echo "****************************************\n";


$db = new LocalDB();

$rows = $db->query("select id, pname, population from places order by id ASC");
foreach($rows as $r) {
    echo sprintf("Processing %s with population %d\n", $r->pname, $r->population);
    //retrieve the warehouse to be used for goods needed by this place
    //the place consumes goods from the major warehouse
    $curr = $r->id;
    $cwh = $db->query_field("select id_whouse as P1 from v_places_whouse_players where id_place=$curr and ptype='AI'");
    if (!$cwh) {
        echo "No warehouse for the major of {$r->pname}\n";
        die();
    } else {
        echo "Using warehouse $cwh \n";
    }
    $mult = $r->population / 100; // multiplier for consumption of non-food materials
    // most important there must be food otherwise the population drops
    $food_avail = $db->query_field("select quantity as P1 from warehouses_goods where id_warehouse = $cwh and id_good = " . FOOD );
    if ($food_avail >= $r->population/ROUNDS) {
        $db->beginTransaction();
        $sql = "update warehouses_goods set quantity = quantity - ? where id_warehouse = ? and id_good = ?";
        $db->exec_prepared($sql,array($r->population/ROUNDS,$cwh,FOOD));
        $db->exec_prepared($sql,array($mult*IRON_XROUND,$cwh,IRON));
        $db->exec_prepared($sql,array($mult*WOOD_XROUND,$cwh,WOOD));
        // alla peggio wood e iron vanno sotto zero e sistemo in coda
        $db->commit();
    } else {
        // not enough food -> population decrease
        echo "Population decrease\n";
        $db->beginTransaction();
        // can't go below 100
        $c = $db->exec("update places set population = population-100 where id = {$r->id} and population > 100");
        $sql = "update warehouses_goods set quantity = quantity - ? where id_warehouse = ? and id_good = ?";
        $db->exec_prepared($sql,array($r->population/ROUNDS,$cwh,FOOD));
        $db->exec_prepared($sql,array($mult*IRON_XROUND,$cwh,IRON));
        $db->exec_prepared($sql,array($mult*WOOD_XROUND,$cwh,WOOD));
        $db->commit();
        // raise buy price for goods
    }
}
// set to ZERO whatever went to a negative number
$db->exec("update warehouses_goods set quantity = 0 where quantity < 0");

