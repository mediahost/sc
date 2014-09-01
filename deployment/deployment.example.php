<?php

$username = "name";
$password = "password";
$server = "server";
$domain = "http://example.com";

return array(
	'my site' => array(
		'remote' => 'ftp://' . $username . ':' . $password . '@' . $server,
		'local' => '..',
		'test' => FALSE,
		'ignore' => '
			.git*
			.composer*
			project.pp[jx]
			/nbproject
			/deployment
			log/*
			!log/.htaccess
			temp/*
			!temp/.htaccess
			/tests
			www/webtemp/*
			!www/webtemp/.htaccess
			*.local.neon
			*.server.neon
			*.local.example.neon
		',
		'allowdelete' => TRUE,
		'after' => array(
			$domain . 'install?printHtml=0'
		),
		'purge' => array(
			'temp/cache',
			'temp/install',
		),
		'preprocess' => FALSE,
		'tempdir' => __DIR__ . '/temp',
		'colors' => TRUE,
	),
);
