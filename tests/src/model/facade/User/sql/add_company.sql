-- Adminer 4.0.3 MySQL dump

INSERT INTO `company` (`id`, `name`, `company_id`, `address`) VALUES
  (1,	'first company',	'companyID123',	'company address');

INSERT INTO `company_role` (`id`, `name`) VALUES
  (1,	'editor'),
  (2,	'manager'),
  (3,	'admin');

INSERT INTO `user` (`id`, `page_config_settings_id`, `page_design_settings_id`, `required_role_id`, `candidate_id`, `facebook_id`, `twitter_id`, `mail`, `hash`, `recovery_token`, `recovery_expiration`) VALUES
  (4,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	'company@domain.com',	'$2y$10$ZSpsPe2ZKbANuf7mSf0YzOINnhnlKtveOl48jj3GhqxtCx/qSqtKu',	NULL,	NULL);

INSERT INTO `company_permission` (`id`, `user_id`, `company_id`) VALUES
  (1,	4,	1);

INSERT INTO `company_permission_company_role` (`company_permission_id`, `company_role_id`) VALUES
  (1,	3);

INSERT INTO `user_role` (`user_id`, `role_id`) VALUES
  (4,	4);