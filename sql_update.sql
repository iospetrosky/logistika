drop view if exists v_transports_locations;

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
           w.place_id AS is_landed,
           coalesce(x.id, 0) AS location_id
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
