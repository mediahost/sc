<?php

// Increase default timeot limit because of locks on DB.
set_time_limit(180);

require __DIR__ . '/../vendor/autoload.php';

if (!class_exists('Tester\Assert')) {
	echo "Install Nette Tester using `composer update --dev`\n";
	exit(1);
}

// Using app temporary directory
define('TEMP_DIR', __DIR__ . '/../temp/');

// Directory for lock files
define('LOCK_DIR', TEMP_DIR);

// Global from is %wwwDir% taken and is not set when php-cgi (run from cmd)
$_SERVER['SCRIPT_FILENAME'] = __DIR__ . '/../www/index.php';

Tester\Environment::setup();

$configurator = new Nette\Configurator;
//$configurator->setDebugMode(TRUE);
//$configurator->enableDebugger(__DIR__ . '/../log');
$configurator->setTempDirectory(TEMP_DIR);
$configurator->createRobotLoader()
		->addDirectory(__DIR__ . '/../app')
		->addDirectory(__DIR__ . '/../tests')
		->addDirectory(__DIR__ . '/../vendor/others')
		->register();

$configurator->addConfig(__DIR__ . '/../app/config/config.neon');
$configurator->addConfig(__DIR__ . '/../app/config/test/config.test.local.neon');

return $configurator->createContainer();
