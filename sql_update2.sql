--Work updated
--To be imported on home PC

use logistika;

INSERT INTO places (id, pname, major, population, ptype, hexmap) VALUES (3, 'Tiny port', 7, 100, 'PLAINS', 'R2C27');

alter table goods add description varchar(200) default 'No description set';

delete from transport_movements;

INSERT INTO transport_movements (id, mov_points, curr_points, hexmap, route_id, transpname) VALUES (6, 1.0, 0.0, 'R4C26', 0, 'Bagnarola');
INSERT INTO transport_movements (id, mov_points, curr_points, hexmap, route_id, transpname) VALUES (7, 1.0, 1.2, 'R4C26', 0, 'Carretto 1');
INSERT INTO transport_movements (id, mov_points, curr_points, hexmap, route_id, transpname) VALUES (8, 1.0, 0.7, 'R4C26', 2, 'Carretto 2');
INSERT INTO transport_movements (id, mov_points, curr_points, hexmap, route_id, transpname) VALUES (10, 1.0, 0.0, 'R4C26', 0, 'Tinozza');

delete from placeproduction where good_id <> 1; -- leave only an automatic production of FOOD

delete from productionpoints;


create table prodpoint_types (
    id integer primary key auto_increment,
    pptype varchar(10) not null unique,
    conv_cost int default 0
) engine = innodb;

alter table productionpoints add pptype_id int default 0;
alter table goods add pptype_req int default 0;
alter table places add avail_areas int default 100;

/*
create TABLE tools_undercontruction (
    id int primary key auto increment,
    
) engine=innodb;

*/