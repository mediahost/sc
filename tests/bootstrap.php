<?php

require __DIR__ . '/../vendor/autoload.php';

if (!class_exists('Tester\Assert')) {
	echo "Install Nette Tester using `composer update --dev`\n";
	exit(1);
}

Tester\Environment::setup();

$configurator = new Nette\Configurator;
$configurator->setDebugMode(TRUE);
//$configurator->enableDebugger(__DIR__ . '/../log');
$configurator->setTempDirectory(__DIR__ . '/temp');
$configurator->createRobotLoader()
		->addDirectory(__DIR__ . '/../app')
		->addDirectory(__DIR__ . '/../vendor/others')
		->register();

$configurator->addConfig(__DIR__ . '/../app/config/tests/config.neon');
$configurator->addConfig(__DIR__ . '/../app/config/tests/config.local.neon');
return $configurator->createContainer();
