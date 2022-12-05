
CREATE DATABASE IF NOT EXISTS social;

-- DROP TABLE IF EXISTS social.user;
-- DROP TABLE IF EXISTS social.interest;
-- DROP TABLE IF EXISTS social.user_interest;
-- DROP TABLE IF EXISTS social.user_page;
-- DROP TABLE IF EXISTS social.friends;

CREATE TABLE IF NOT EXISTS social.user
(
    id int unsigned auto_increment primary key,
    firstName text not null,
    lastName text not null,
    years tinyint unsigned NOT NULL,
    sex enum('male', 'female') null,
    city text not null,
    password char(60) not null
);

CREATE TABLE IF NOT EXISTS social.interest
(
    id int unsigned auto_increment primary key,
    name text not null
);

CREATE TABLE IF NOT EXISTS social.user_interest
(
    userId     int unsigned not null,
    interestId int unsigned not null,
    primary key (userId, interestId)
);

CREATE TABLE IF NOT EXISTS social.user_page
(
    id int unsigned auto_increment primary key,
    userId int unsigned not null,
    content text not null
);

CREATE TABLE IF NOT EXISTS social.friends
(
    userId       int unsigned not null,
    friendUserId int unsigned not null,
    primary key (userId, friendUserId)
);

