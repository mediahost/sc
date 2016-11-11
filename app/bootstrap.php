<?php

require __DIR__ . '/../vendor/autoload.php';

$configurator = new Nette\Configurator;

// Enable Nette Debugger for error visualisation & logging
$configurator->setDebugMode([
    '94.112.129.121', // Petr - Brno
    '149.62.146.153', // Petr - Brno2
    '89.176.41.80', // Petr - Brno3
    '89.102.3.159', // Petr - Brno4
	'37.221.251.252', // Petr - Svetla n.S.
	'213.81.220.69', // Kapco
]);

$configurator->enableDebugger(__DIR__ . '/../log');

$configurator->setTempDirectory(__DIR__ . '/../temp');

$configurator->createRobotLoader()
		->addDirectory(__DIR__)
		->addDirectory(__DIR__ . '/../vendor/others')
		->register();

$configurator->addConfig(__DIR__ . '/config/config.neon');
$configurator->addConfig(__DIR__ . '/config/config.local.neon');

$container = $configurator->createContainer();

return $container;
