#用户uid到oid的映射表
CREATE DATABASE IF NOT EXISTS uid_oid_dbmapping_001 DEFAULT CHARACTER SET UTF8;
USE uid_oid_dbmapping_001;
CREATE TABLE uid_oid_dbmapping (
	`uid` bigint not null auto_increment primary key,
	`oid` varchar(64) not null default '',
	unique index `oid` (`oid`)
) ENGINE=InnoDB DEFAULT CHARACTER SET UTF8;	

#公司列表
CREATE DATABASE IF NOT EXISTS company_list_001 DEFAULT CHARACTER SET UTF8;
USE company_list_001;
CREATE TABLE company_list (
	`cid` bigint not null auto_increment primary key,
	`uid` bigint not null default 0,
	`create_time` bigint not null default 0
) ENGINE=InnoDB DEFAULT CHARACTER SET UTF8;

#明星列表
CREATE DATABASE IF NOT EXISTS star_list_001 DEFAULT CHARACTER SET UTF8;
USE star_list_001;
CREATE TABLE star_list (
	`sid` bigint not null auto_increment primary key,
	`create_time` bigint not null default 0
) ENGINE=InnoDB DEFAULT CHARACTER SET UTF8;
