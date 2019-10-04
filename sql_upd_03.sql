--Changes for branch buyworkspace
CREATE table prodpoint_reqmaterials (
id        INTEGER      PRIMARY KEY AUTOINCREMENT,
pp_id int not null,
mat_id  int not null,
quantity int default 0

) engine = innodb;

create index ppr_ppid on prodpoint_reqmaterials(pp_id);
create index ppr_matid on prodpoint_reqmaterials(mat_id);