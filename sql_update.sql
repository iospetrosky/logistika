--check if the index is created on mysql
create unique index wg_whgoods on warehouses_goods(id_warehouse,id_good);
-- check table production points and add "default 0" TO ALL FIELDS

create view v_player_warehouses_goods as
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
     WHERE y.ptype = 'HU';

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

CREATE VIEW v_player_warehouses_goods AS
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
           g.locked,
           w.whtype
      FROM places p
           INNER JOIN
           warehouses w ON p.id = w.place_id
           INNER JOIN
           players y ON w.player_id = y.id
           INNER JOIN
           warehouses_goods g ON w.id = g.id_warehouse
           INNER JOIN
           goods o ON g.id_good = o.id
     WHERE y.ptype = 'HU';

-- market equivalents keep only v_marketplace_equiv AND drop the other
create table traderoutes (
    id       INTEGER      PRIMARY KEY AUTOINCREMENT,
    hexlength int default 0,
    starthex int default 0,
    endhex int default 0,
    hexcost int default 0,
    description varchar(200),
    traveltype varchar(20) default 'ROAD'
);

in table places drop columns mapx and mapy and replace with hexmap varchar(10)

