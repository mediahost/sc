<?php

return array(
	'my site' => array(
		'remote' => 'ftp://' . $username . ':' . $password . '@' . $server,
		'passivemode' => TRUE,
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
			tests/
			bin/
			www/webtemp/*
			!www/webtemp/.htaccess
			*.local.neon
			*.server.neon
			*.server_dev.neon
			*.server_test.neon
			*.local.example.neon
			composer.lock
			composer.json
			*.md
			.bowerrc
			/app/config/deployment.*
			/vendor/dg/ftp-deployment
			*.rst
		',
		'allowdelete' => TRUE,
		'after' => array(
			$domain . '/install?printHtml=0'
		),
		'purge' => array(
			'temp/cache',
			'temp/install',
			'temp/deployment',
		),
		'preprocess' => FALSE,
		'tempdir' => 'temp/deployment',
		'colors' => TRUE,
	),
);
