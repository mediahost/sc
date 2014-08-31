<?php

require __DIR__ . '/../vendor/autoload.php';

$configurator = new Nette\Configurator;

// Enable Nette Debugger for error visualisation & logging
$configurator->setDebugMode(array(
	'37.221.251.250', // pto - Svetla0
	'37.221.251.251', // pto - Svetla1
	'37.221.251.255', // pto - Svetla5
	'89.176.186.77', // pto - Brno
	'147.229.204.31', // Kapco
));

//$configurator->setDebugMode(TRUE);  // debug mode MUST NOT be enabled on production server
$configurator->enableDebugger(__DIR__ . '/../log');

$configurator->setTempDirectory(__DIR__ . '/../temp');

$configurator->createRobotLoader()
		->addDirectory(__DIR__)
		->addDirectory(__DIR__ . '/../vendor/others')
		->register();

$configurator->addConfig(__DIR__ . '/config/config.neon');
$configurator->addConfig(__DIR__ . '/config/config.local.neon');

$container = $configurator->createContainer();

//ob_start(function($s, $flag) {
//    if ($flag & PHP_OUTPUT_HANDLER_START) {
//    	$e = new \Exception;
//		$s = nl2br("Output started here:\n{$e->getTraceAsString()}\n\n") . $s;
//	}
//	return $s;
//}, 2); 

return $container;
