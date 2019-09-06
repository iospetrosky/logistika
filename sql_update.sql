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
