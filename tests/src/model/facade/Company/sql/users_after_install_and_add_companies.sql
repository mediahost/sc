-- Adminer 4.0.3 MySQL dump

INSERT INTO `role` (`id`, `name`) VALUES
  (1,	'guest'),
  (2,	'signed'),
  (3,	'candidate'),
  (4,	'company'),
  (5,	'admin'),
  (6,	'superadmin');

INSERT INTO `user` (`id`, `page_config_settings_id`, `page_design_settings_id`, `required_role_id`, `candidate_id`, `facebook_id`, `twitter_id`, `mail`, `hash`, `recovery_token`, `recovery_expiration`) VALUES
  (1,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	'admin',	'$2y$10$jOdbclfR38vpkvZIxiZdr.bvf/.ezWzEHVFD88P/SA0zIYQpHL6ES',	NULL,	NULL),
  (2,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	'superadmin',	'$2y$10$8yUBkOMjEqqQ/uSH08n7Nued4f03wixtUnZNxDeQc50dG1SPJ1RLK',	NULL,	NULL),
  (3,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	'company1@domain.com',	'$2y$10$I4qmS370rSUIY.IBbKmNS.RkOuTP4OkNu3omKHqO1WyuFp7vqd6mm',	NULL,	NULL),
  (4,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	'company2@domain.com',	'$2y$10$YYd1Gd.3VCkGLed8zjDqYO/tHnU/y14OONfOxBSLMsWb1m6bzQ7Tu',	NULL,	NULL),
  (5,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	'company3@domain.com',	'$2y$10$mos2Zy1ZVi2i4XGFOX5rWeNQR0C.H.RfmS/NJyEAYOXEZ1/Hl84ny',	NULL,	NULL),
  (6,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	'company4@domain.com',	'$2y$10$wJa.OdBvURO1zakAP64Lk.rJh6tTqMwmGcTW0FHyBhwBjxFSAy916',	NULL,	NULL),
  (7,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	'company5@domain.com',	'$2y$10$kh0imVWVbPOc64zo9NHI4.L3JAsL4dhZ3Zyvi2mWu./392doUSv22',	NULL,	NULL);

INSERT INTO `company` (`id`, `name`, `company_id`, `address`) VALUES
  (1,	'company1',	'id1',	NULL),
  (2,	'company2',	'id2',	NULL),
  (3,	'company3',	'id3',	NULL),
  (4,	'company4',	'id4',	NULL),
  (5,	'company5',	'id5',	NULL);

INSERT INTO `company_role` (`id`, `name`) VALUES
  (1,	'editor'),
  (2,	'manager'),
  (3,	'admin');

INSERT INTO `user_role` (`user_id`, `role_id`) VALUES
  (1,	5),
  (2,	6),
  (3,	4),
  (4,	4),
  (5,	4),
  (6,	4),
  (7,	4);

INSERT INTO `company_permission` (`id`, `user_id`, `company_id`) VALUES
  (1,	3,	1),
  (2,	4,	2),
  (3,	5,	3),
  (4,	6,	4),
  (5,	7,	5);

INSERT INTO `company_permission_company_role` (`company_permission_id`, `company_role_id`) VALUES
  (1,	3),
  (2,	3),
  (3,	3),
  (4,	3),
  (5,	3);