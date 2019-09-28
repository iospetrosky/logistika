Updated home 28/09
To be imported on work PC


create table tools (
    id int primary key auto increment,
    tname varchar(40) 

) engine=innodb;



create table tools_production (
    id int primary key auto increment,
    id_tool_prod int default 0,
    id_tool_need int default 0,
    id_mat_need int default 0,
    quantity int  default 0
) engine=innodb;

create TABLE tools_undercontruction (
    id int primary key auto increment,
    
) engine=innodb;