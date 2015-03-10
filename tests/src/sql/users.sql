-- Adminer 4.0.3 MySQL dump

SET NAMES utf8;
SET foreign_key_checks = 0;
SET time_zone = '+01:00';
SET sql_mode = 'NO_AUTO_VALUE_ON_ZERO';

INSERT INTO `role` (`id`, `name`) VALUES
(1,	'guest'),
(2,	'signed'),
(3,	'candidate'),
(4,	'company'),
(5,	'admin'),
(6,	'superadmin');

INSERT INTO `user` (`id`, `page_config_settings_id`, `page_design_settings_id`, `facebook_id`, `twitter_id`, `required_role_id`, `candidate_id`, `mail`, `hash`, `recovery_token`, `recovery_expiration`) VALUES
(1,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	'admin',	'$2y$10$wh7oLYFEYOuAe1HE82d7euQsKWQTtjY7z44kwMlu4XztrX.ckEJoe',	NULL,	NULL),
(2,	1,	NULL,	NULL,	NULL,	NULL,	NULL,	'superadmin',	'$2y$10$TP9nK63OFGgQTchheTe6x.V64FRpyhp2sEsSG0zvNRAz6Oh6dckN6',	NULL,	NULL);

INSERT INTO `user_role` (`user_id`, `role_id`) VALUES
(1,	5),
(2,	6);

-- 2015-03-10 16:02:38