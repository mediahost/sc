-- Adminer 4.0.3 MySQL dump

SET NAMES utf8;
SET foreign_key_checks = 0;
SET time_zone = '+02:00';
SET sql_mode = 'NO_AUTO_VALUE_ON_ZERO';

DELIMITER ;;

DROP PROCEDURE IF EXISTS `test_multi_sets`;;
CREATE DEFINER=`root`@`localhost` PROCEDURE `test_multi_sets`()
    DETERMINISTIC
begin
        select user() as first_col;
        select user() as first_col, now() as second_col;
        select user() as first_col, now() as second_col, now() as third_col;
        end;;

DELIMITER ;

DROP TABLE IF EXISTS `address`;
CREATE TABLE `address` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `firstname` varchar(128) COLLATE utf8_unicode_ci NOT NULL,
  `surname` varchar(128) COLLATE utf8_unicode_ci NOT NULL,
  `company` varchar(128) COLLATE utf8_unicode_ci NOT NULL,
  `mail` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `phone` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
  `street` varchar(200) COLLATE utf8_unicode_ci NOT NULL,
  `city` varchar(200) COLLATE utf8_unicode_ci NOT NULL,
  `country` varchar(200) COLLATE utf8_unicode_ci NOT NULL,
  `zipcode` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


DROP TABLE IF EXISTS `comment`;
CREATE TABLE `comment` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `message_html` longtext COLLATE utf8_unicode_ci NOT NULL,
  `public` tinyint(1) NOT NULL,
  `task_id` int(11) DEFAULT NULL,
  `sender_id` int(11) DEFAULT NULL,
  `send_time` datetime NOT NULL,
  `time_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_9474526C5EEADD3B` (`time_id`),
  KEY `IDX_9474526C8DB60186` (`task_id`),
  KEY `IDX_9474526CF624B39D` (`sender_id`),
  CONSTRAINT `FK_9474526C5EEADD3B` FOREIGN KEY (`time_id`) REFERENCES `time` (`id`),
  CONSTRAINT `FK_9474526C8DB60186` FOREIGN KEY (`task_id`) REFERENCES `task` (`id`),
  CONSTRAINT `FK_9474526CF624B39D` FOREIGN KEY (`sender_id`) REFERENCES `user` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


DROP TABLE IF EXISTS `company`;
CREATE TABLE `company` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `address_id` int(11) DEFAULT NULL,
  `name` varchar(128) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_4FBF094FF5B7AF75` (`address_id`),
  CONSTRAINT `FK_4FBF094FF5B7AF75` FOREIGN KEY (`address_id`) REFERENCES `address` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

INSERT INTO `company` (`id`, `address_id`, `name`) VALUES
(1,	NULL,	'G.P. Farrell Ltd.'),
(2,	NULL,	'GRIFIN, s.r.o.');

DROP TABLE IF EXISTS `company_user`;
CREATE TABLE `company_user` (
  `company_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  PRIMARY KEY (`company_id`,`user_id`),
  KEY `IDX_CEFECCA7979B1AD6` (`company_id`),
  KEY `IDX_CEFECCA7A76ED395` (`user_id`),
  CONSTRAINT `FK_CEFECCA7A76ED395` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_CEFECCA7979B1AD6` FOREIGN KEY (`company_id`) REFERENCES `company` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

INSERT INTO `company_user` (`company_id`, `user_id`) VALUES
(1,	6),
(2,	5);

DROP TABLE IF EXISTS `project`;
CREATE TABLE `project` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(128) COLLATE utf8_unicode_ci NOT NULL,
  `company_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_2FB3D0EE979B1AD6` (`company_id`),
  CONSTRAINT `FK_2FB3D0EE979B1AD6` FOREIGN KEY (`company_id`) REFERENCES `company` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

INSERT INTO `project` (`id`, `name`, `company_id`) VALUES
(1,	'Source-Code',	1),
(2,	'MobilneTelefony.sk',	2),
(3,	'MobilneTelefony.cz',	2);

DROP TABLE IF EXISTS `role`;
CREATE TABLE `role` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(128) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

INSERT INTO `role` (`id`, `name`) VALUES
(1,	'programmer'),
(2,	'manager'),
(3,	'client'),
(4,	'admin');

DROP TABLE IF EXISTS `status`;
CREATE TABLE `status` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(128) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

INSERT INTO `status` (`id`, `name`) VALUES
(1,	'waiting for answer'),
(2,	'working on'),
(3,	'DONE');

DROP TABLE IF EXISTS `tag`;
CREATE TABLE `tag` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(128) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


DROP TABLE IF EXISTS `task`;
CREATE TABLE `task` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(128) COLLATE utf8_unicode_ci NOT NULL,
  `text_html` longtext COLLATE utf8_unicode_ci NOT NULL,
  `done` tinyint(1) NOT NULL,
  `in_process` tinyint(1) NOT NULL,
  `priority` smallint(6) NOT NULL,
  `due_date` datetime NOT NULL,
  `create_date` datetime NOT NULL,
  `project_id` int(11) DEFAULT NULL,
  `status_id` int(11) DEFAULT NULL,
  `solver_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_527EDB25166D1F9C` (`project_id`),
  KEY `IDX_527EDB256BF700BD` (`status_id`),
  KEY `IDX_527EDB25BE651DEC` (`solver_id`),
  CONSTRAINT `FK_527EDB25BE651DEC` FOREIGN KEY (`solver_id`) REFERENCES `user` (`id`),
  CONSTRAINT `FK_527EDB25166D1F9C` FOREIGN KEY (`project_id`) REFERENCES `project` (`id`),
  CONSTRAINT `FK_527EDB256BF700BD` FOREIGN KEY (`status_id`) REFERENCES `status` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


DROP TABLE IF EXISTS `task_tag`;
CREATE TABLE `task_tag` (
  `task_id` int(11) NOT NULL,
  `tag_id` int(11) NOT NULL,
  PRIMARY KEY (`task_id`,`tag_id`),
  KEY `IDX_6C0B4F048DB60186` (`task_id`),
  KEY `IDX_6C0B4F04BAD26311` (`tag_id`),
  CONSTRAINT `FK_6C0B4F04BAD26311` FOREIGN KEY (`tag_id`) REFERENCES `tag` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_6C0B4F048DB60186` FOREIGN KEY (`task_id`) REFERENCES `task` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


DROP TABLE IF EXISTS `time`;
CREATE TABLE `time` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `start_time` datetime NOT NULL,
  `end_time` datetime NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `task_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_6F949845A76ED395` (`user_id`),
  KEY `IDX_6F9498458DB60186` (`task_id`),
  CONSTRAINT `time_ibfk_4` FOREIGN KEY (`task_id`) REFERENCES `task` (`id`),
  CONSTRAINT `time_ibfk_3` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


DROP TABLE IF EXISTS `user`;
CREATE TABLE `user` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `password` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_8D93D649F85E0677` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

INSERT INTO `user` (`id`, `username`, `password`) VALUES
(1,	'pupe.dupe@gmail.com',	'$2y$10$ws51c6qPg4M3nbnca65GKO3LGxViV/6l4lkQ8fNb.rxbLdN/fU6aW'),
(3,	'kapicak@kapicak.com',	'$2y$10$YVimi23IMuGvpj.ZV4CXHO0D9KI3odaov.hxvnF9QzrcW6kYJkhem'),
(4,	'g.vvoody@gmail.com',	'$2y$10$gnhar4MXds4mDSHKP/7/Gu8KObGHropO1HkLhR0ndth5DLbJPxft.'),
(5,	'lubox11@gmail.com',	'$2y$10$5Q2etwA/JNJLZ7QujYkbUedBzIYM81z0oqPIzy9OJH2OyO7Iy7fVa'),
(6,	'g.farrell@source-code.ie',	'$2y$10$QlUAPaCkG9Bjhodzn3pha.k3RnuyUmaLYEC2kxS/bRLbtzS6c1wC2');

DROP TABLE IF EXISTS `user_role`;
CREATE TABLE `user_role` (
  `user_id` int(11) NOT NULL,
  `role_id` int(11) NOT NULL,
  PRIMARY KEY (`user_id`,`role_id`),
  KEY `IDX_2DE8C6A3A76ED395` (`user_id`),
  KEY `IDX_2DE8C6A3D60322AC` (`role_id`),
  CONSTRAINT `FK_2DE8C6A3A76ED395` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_2DE8C6A3D60322AC` FOREIGN KEY (`role_id`) REFERENCES `role` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

INSERT INTO `user_role` (`user_id`, `role_id`) VALUES
(1,	4),
(3,	2),
(4,	1),
(5,	3),
(6,	3);

-- 2014-06-25 19:21:51