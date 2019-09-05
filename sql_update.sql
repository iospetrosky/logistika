--check if the index is created on mysql
create unique index wg_whgoods on warehouses_goods(id_warehouse,id_good);
-- check table production points and add "default 0" TO ALL FIELDS