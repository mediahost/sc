<?php

$purge = [
	'temp/deployment',
];

$before = [];

$after = [];
if (!isset($allowInstall) || $allowInstall) {
	$after[] = $domain . '/install?printHtml=0';
}

if (!isset($allowDeleteCache) || $allowDeleteCache) {
	$purge[] = 'temp/cache';
	$purge[] = 'temp/install';
}
if (!isset($allowDeleteTmp) || $allowDeleteTmp) {
	$purge[] = 'tmp/';
}

return array(
	'my site' => array(
		'remote' => 'ftp://' . $username . ':' . $password . '@' . $server,
		'passivemode' => TRUE,
		'local' => '..',
		'test' => FALSE,
		'ignore' => '
			.git*
			!.gitignore
			!.gitattributes
			!.gitkeep
			.composer*
			composer.lock
			project.pp[jx]
			/.idea
			/nbproject
			/deployment
			log/*
			!log/.htaccess
			temp/*
			!temp/.htaccess
			tests/
			bin/
			www/webtemp/*
			!www/webtemp/.htaccess
			www/foto/*
			!www/foto/.htaccess
			www/wp/*
			!www/wp/.htaccess
			www/pdf/*
			!www/pdf/.htaccess
			*.local.neon
			*.server.neon
			*.server_dev.neon
			*.server_test.neon
			*.server_ver21.neon
			*.server_ver22.neon
			*.local.example.neon
			/app/config/deployment.*
			/vendor/dg/ftp-deployment
			/vendor/nette/tester
			/vendor/kdyby/tester-extras
			/vendor/mockery/mockery
		',
		'allowdelete' => TRUE,
		'before' => $before,
		'after' => $after,
		'purge' => $purge,
		'preprocess' => FALSE,
	),
	
	'tempdir' => __DIR__ . '/temp',
	'colors' => TRUE,
);
