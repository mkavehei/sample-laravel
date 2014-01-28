create database kixeye;

use kixeye;
CREATE TABLE `users` (
  `user_id`         int(11) NOT NULL AUTO_INCREMENT,
  `user_fname`     varchar(255) DEFAULT NULL,
  `user_lname`     varchar(255) DEFAULT  NULL,
  `user_name`       varchar(255) NOT NULL,
  `user_email`      varchar(255) NOT NULL,
  `user_passwd`     varchar(255) NOT NULL,          
  `fb_user_id`      int(15)    DEFAULT 0, 
  `fb_user_country` varchar(5) DEFAULT NULL,
  `fb_user_locale`  varchar(5) DEFAULT NULL,   
  `fb_user_min_age` int(2)     DEFAULT 0,   
  PRIMARY KEY (`user_id`),
  CONSTRAINT uq_fb_user_id UNIQUE ( `fb_user_id` )
) ENGINE=InnoDB AUTO_INCREMENT=100 DEFAULT CHARSET=latin1;

CREATE TABLE `users_score` (
  `score_id`   int(11) NOT NULL AUTO_INCREMENT,
  `game_id`    int(11) NOT NULL,
  `user_id`    int(11) NOT NULL, 
  `fb_user_id` int(15) DEFAULT 0,
  `user_score` int(5)  DEFAULT 0,    
  `game_started_at`   int(11)  DEFAULT 0,
  `game_ended_at`     int(11)  DEFAULT 0,     
  `score_posted_at`   int(11)  DEFAULT 0, 
  PRIMARY KEY (`score_id`)
) ENGINE=InnoDB AUTO_INCREMENT=100 DEFAULT CHARSET=latin1;

CREATE TABLE `games` (
  `game_id`    int(11) NOT NULL AUTO_INCREMENT,
  `game_name`  varchar(255) DEFAULT NULL,
  PRIMARY KEY (`game_id`)
) ENGINE=InnoDB AUTO_INCREMENT=100 DEFAULT CHARSET=latin1;

create view total_players_view as 
  select count(distinct user_id) from users_score;
  
create view  total_players_today_view as
  select count(distinct user_id) from users_score where `score_posted_at` = now();
  
create view  top_ten_players_by_score_view as
  select distinct user_id from users_score order by `user_score` DESC Limit 10;  
