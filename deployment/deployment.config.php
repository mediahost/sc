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
			deployment
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
			composer.lock
			composer.json
			*.md
			.bowerrc
			/app/config/deployment.*
			/bin
			/vendor/dg/ftp-deployment
			*.rst
			tests/
			bin/
		',
		'allowdelete' => TRUE,
		'after' => array(
			
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
