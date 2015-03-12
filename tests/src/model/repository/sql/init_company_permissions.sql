-- Adminer 4.0.3 MySQL dump

INSERT INTO `role` (`id`, `name`) VALUES
  (1,	'guest'),
  (2,	'signed'),
  (3,	'candidate'),
  (4,	'company'),
  (5,	'admin'),
  (6,	'superadmin');

INSERT INTO `user` (`id`, `page_config_settings_id`, `page_design_settings_id`, `required_role_id`, `candidate_id`, `facebook_id`, `twitter_id`, `mail`, `hash`, `recovery_token`, `recovery_expiration`) VALUES
  (1,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	'admin',	'$2y$10$C1xzeFi9oPSPuZtRmOnBjuIHSvu69rwcad11J6TJN9ow.0dDXCCtS',	NULL,	NULL),
  (2,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	'superadmin',	'$2y$10$vgANJpiP5Oe8lLrY9zmEaeEMY03lNmdCohPNO276d6yWXFCEtUNPe',	NULL,	NULL),
  (3,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	'company1@domain.com',	'$2y$10$WYw8ehlcdbK2EKaJfjL5zeev0aW4FMQNfMjotfNT0avTd/i0kMQLi',	NULL,	NULL),
  (4,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	'company2@domain.com',	'$2y$10$efESXLih8FybQK4Um9FSLOFjsNavwXdz7Qax0Hy1LyaGpH8M4f0vu',	NULL,	NULL),
  (5,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	'company3@domain.com',	'$2y$10$Xityh59l9CGCGs4LdrllJeQUY6y7AbiaOvQMWJuoY0BJj0oo/.Sey',	NULL,	NULL),
  (6,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	'company4@domain.com',	'$2y$10$6CKN6K/a50OGBoVG96wLzuPAkopyDv6cuHk3PKAto2lAIYG7skame',	NULL,	NULL),
  (7,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	'company5@domain.com',	'$2y$10$AWvmu44Uugp7QX3mCMYtROmtviQOB092/g0yKIxujiq0MktK/8rS6',	NULL,	NULL);

INSERT INTO `company` (`id`, `name`, `company_id`, `address`) VALUES
  (1,	'company1',	NULL,	NULL),
  (2,	'company2',	NULL,	NULL),
  (3,	'company3',	NULL,	NULL);

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
  (4,	4,	1),
  (5,	5,	1),
  (6,	5,	2),
  (7,	6,	2),
  (8,	3,	3),
  (9,	4,	3),
  (10,	6,	3),
  (11,	7,	3);

INSERT INTO `company_permission_company_role` (`company_permission_id`, `company_role_id`) VALUES
  (1,	3),
  (2,	3),
  (3,	3),
  (4,	2),
  (5,	1),
  (5,	2),
  (6,	2),
  (7,	1),
  (7,	2),
  (8,	1),
  (8,	3),
  (9,	1),
  (9,	2),
  (10,	1),
  (10,	2),
  (11,	1),
  (11,	2);

