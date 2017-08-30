-- These SQL statements are for creating a user, DB, and table on a MySQL instance.

-- Create user 'test' and allow it to access the DB from any host
create user 'test'@'%' identified by 'test';
	
-- Grant user 'test' all privileges
grant all privileges on *.* to 'test'@'%' with grant option;
	
-- Create DB 'test'
create database test;
	
-- Use 'test' DB
use test;

-- Create table 'user'
create table if not exists `test`.`user`
(`id` integer not null auto_increment primary key,
`email` varchar(200) not null unique,
`phone_number` varchar(20) not null unique,
`full_name` varchar(200),
`password` varchar(100) not null,
`key` varchar(100) not null unique,
`account_key` varchar(100) unique,
`metadata` varchar(2000));
