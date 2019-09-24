drop view if exists v_major_warehouses_goods;

CREATE VIEW v_major_warehouses_goods AS
    SELECT p.id AS id_place,
           p.pname,
           p.population,
           p.ptype,
           w.id AS id_whouse,
           y.id AS id_player,
           y.fullname,
           g.id_good,
           o.gname,
           g.quantity - g.locked AS avail_quantity,
           g.locked
      FROM places p
           INNER JOIN
           warehouses w ON p.id = w.place_id
           INNER JOIN
           players y ON w.player_id = y.id
           INNER JOIN
           warehouses_goods g ON w.id = g.id_warehouse
           INNER JOIN
           goods o ON g.id_good = o.id
     WHERE y.ptype = 'AI';

     
     
drop VIEW if exists v_marketplace     ;

CREATE VIEW v_marketplace AS
    SELECT m.id,
           m.id_place,
           p.pname,
           m.id_player,
           y.fullname,
           y.ptype,
           y.gold,
           m.op_type,
           m.op_scope,
           m.id_good,
           g.gname,
           g.gtype,
           m.quantity,
           m.price
      FROM marketplace m
           INNER JOIN
           places p ON m.id_place = p.id
           INNER JOIN
           players y ON m.id_player = y.id
           INNER JOIN
           goods g ON m.id_good = g.id;     
           
drop VIEW if exists v_marketplace_equiv;
CREATE VIEW v_marketplace_equiv AS
    SELECT m.id,
           m.id_place,
           p.pname,
           m.id_player,
           y.fullname,
           y.ptype,
           y.gold,
           m.op_type,
           m.op_scope,
           m.id_good,
           coalesce(e.id_equiv, m.id_good) AS id_equiv,
           g.gname,
           g.gtype,
           m.quantity,
           m.price,
           coalesce(e.quantity, 1) * m.quantity AS equiv_quantity,
           m.price / coalesce(e.quantity, 1) AS equiv_price
      FROM marketplace m
           INNER JOIN
           places p ON m.id_place = p.id
           INNER JOIN
           players y ON m.id_player = y.id
           INNER JOIN
           goods g ON m.id_good = g.id
           LEFT JOIN
           equivalent e ON m.id_good = e.id_original;         

           
drop VIEW if exists v_places_production;           
CREATE VIEW v_places_production AS
    SELECT a.id AS id_place,
           a.pname,
           b.good_id AS id_good,
           c.gname,
           b.quantity
      FROM places a
           INNER JOIN
           placeproduction b ON a.id = b.place_id
           INNER JOIN
           goods c ON b.good_id = c.id;

drop VIEW if exists v_places_whouse_players;
CREATE VIEW v_places_whouse_players AS
    SELECT p.id AS id_place,
           p.pname,
           p.population,
           w.id AS id_whouse,
           w.capacity,
           w.whtype,
           y.id AS id_player,
           y.fullname,
           y.ptype,
           y.gold,
           y.diamond
      FROM places p
           INNER JOIN
           warehouses w ON p.id = w.place_id
           INNER JOIN
           players y ON w.player_id = y.id;

drop VIEW if exists v_player_warehouses_goods ;
CREATE VIEW v_player_warehouses_goods AS
    SELECT g.id,
           p.id AS id_place,
           p.pname,
           p.population,
           p.ptype,
           w.id AS id_whouse,
           y.id AS id_player,
           w.capacity,
           y.fullname,
           coalesce(g.id_good, 0) AS id_good,
           o.gname,
           coalesce(g.quantity - g.locked, 0) AS avail_quantity,
           coalesce(g.locked, 0) AS locked,
           w.whtype
      FROM places p
           LEFT JOIN
           warehouses w ON p.id = w.place_id
           LEFT JOIN
           players y ON w.player_id = y.id
           LEFT JOIN
           warehouses_goods g ON w.id = g.id_warehouse
           LEFT JOIN
           goods o ON g.id_good = o.id
     WHERE y.ptype = 'HU';


     
drop VIEW if exists v_prodpoints_players ;

CREATE VIEW v_prodpoints_players AS
    SELECT pp.id,
           coalesce(pwf.id_wf, 0) AS id_wf,
           pp.rnd_order,
           pp.active,
           pp.plevel,
           pp.id_player,
           p.fullname,
           pp.id_place,
           pl.pname,
           pp.id_good,
           g.gname,
           g.gtype,
           g.workers,
           coalesce(pwf.req_good, 'undefined') AS req_id,
           coalesce(g2.gname, '') AS req_good,
           coalesce(pwf.quantity, 0) AS prod_quantity
      FROM productionpoints pp
           INNER JOIN
           players p ON pp.id_player = p.id
           INNER JOIN
           places pl ON pp.id_place = pl.id
           INNER JOIN
           goods g ON pp.id_good = g.id
           LEFT JOIN
           productionworkflow pwf ON pp.id_good = pwf.id_good
           LEFT JOIN
           goods g2 ON pwf.req_good = g2.id
     WHERE p.ptype = 'HU'
     ORDER BY g.gtype ASC,
              pp.rnd_order ASC,
              pp.id_good ASC;


drop VIEW if exists v_transports_locations;

CREATE VIEW v_transports_locations AS
    SELECT w.id,
           w.player_id,
           w.whtype,
           p.fullname,
           tm.route_id,
           coalesce(t.description, 'not set') AS description,
           coalesce(t.traveltype, 'none') AS traveltype,
           t.hexcost,
           tm.mov_points,
           tm.curr_points,
           tm.hexmap,
           coalesce(x.pname, 'travel') AS current_location,
           place_id AS is_landed
      FROM warehouses w
           INNER JOIN
           transport_movements tm ON w.id = tm.id
           INNER JOIN
           players p ON w.player_id = p.id
           LEFT JOIN
           traderoutes t ON abs(tm.route_id) = t.id
           LEFT JOIN
           places x ON x.hexmap = tm.hexmap
     WHERE whtype <> 'STATIC';

drop view if exists v_workflows;

CREATE VIEW v_workflows AS
    SELECT pw.id_wf,
           pw.id_good,
           g1.gname,
           pw.req_good,
           g2.gname,
           pw.quantity
      FROM productionworkflow pw
           LEFT JOIN
           goods g1 ON pw.id_good = g1.id
           LEFT JOIN
           goods g2 ON pw.req_good = g2.id;

CREATE TABLE transport_movements (
    id          INT      PRIMARY KEY AUTO_INCREMENT,
    mov_points  FLOAT        DEFAULT 1,
    curr_points FLOAT        DEFAULT 0,
    hexmap      VARCHAR (10) DEFAULT 'X0Y0', --create an index on this field
    route_id    INT          DEFAULT 0 
);
























           