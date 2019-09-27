<?php
require_once("libraries/my_pdo.php");
require_once("z_local_db.php");
require_once("z_const.php");

echo "****************************************\n";
echo "*********** MOVEMENT *******************\n";
echo "****************************************\n";


$db = new LocalDB();

$sql_add_movpoints = "update transport_movements set curr_points = curr_points + mov_points where id = ?";

$rows = $db->query("select * from v_transports_locations where is_landed = 0 and route_id <> 0 order by route_id asc");
if($rows) {
    echo "Updating movement points\n";
    $db->beginTransaction();
    foreach($rows as $r) {
        echo sprintf("Processing %s of %s on '%s' currently at %s\n", $r->whtype, $r->fullname, $r->description, $r->hexmap);
        //id is the transport ID
        $db->exec_prepared($sql_add_movpoints,array($r->id));
    }
    $db->commit();
    //$db->rollback();
} else {
    echo "All transports are idle\n";
    die();
}

$rows = $db->query("select * from v_transports_locations where is_landed = 0 and route_id <> 0 and curr_points >= hexcost order by route_id asc");
$sql_move_transp = "update transport_movements set curr_points = curr_points - ?, hexmap = ? where id = ?";
$sql_reach_dest = "update warehouses set place_id = ? where id = ?";
$sql_cancel_route = "update transport_movements set route_id = 0 where id = ?";
if ($rows) {
    echo "Moving transports\n";
    $db->beginTransaction();
    foreach($rows as $r) {
        //retrieve next tile on the traderoute. Get the current tile, then the next or the previous
        //according to the direction (reverse direction is a negative id_route)
        $current_tile = $db->query("select * from routespaths where id_route = abs({$r->route_id}) and map_tile = '{$r->hexmap}'")[0];
        if ($r->route_id >0) {
            $next_tile = $db->query("select * from routespaths where id_route = abs({$r->route_id}) and pathsequence > {$current_tile->pathsequence} order by pathsequence ASC")[0];
        } else {
            $next_tile = $db->query("select * from routespaths where id_route = abs({$r->route_id}) and pathsequence < {$current_tile->pathsequence} order by pathsequence DESC")[0];
        }
        echo sprintf("Processing %s of %s on '%s' currently at %s moving to %s\n", $r->whtype, $r->fullname, $r->description, $r->hexmap, $next_tile->map_tile);
        $db->exec_prepared($sql_move_transp,array($r->hexcost, $next_tile->map_tile, $r->id));
        // check if the destination has been reached
        if  (($next_tile->pathsequence == 100) || ($next_tile->pathsequence == 1)) {
            //get the place ID
            $destination = $db->query("select * from places where hexmap = '{$next_tile->map_tile}'")[0];
            echo sprintf("The transport arrived in %s \n", $destination->pname);
            $db->exec_prepared($sql_reach_dest,array($destination->id, $r->id));
            $db->exec_prepared($sql_cancel_route,array($r->id));
        }
    }
    $db->commit();
} else {
    echo "No trasport has enough movement points\n";
}
