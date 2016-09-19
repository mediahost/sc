<?php

namespace App\Extensions\Document\DI;

use Nette\DI\CompilerExtension;

class DocumentExtension extends CompilerExtension
{
	public $defaults = [
		'folder' => '%wwwDir%/documents',
		'webPath' => '/documents'
	];

	public function loadConfiguration() {
		$builder = $this->getContainerBuilder();
		$config = $this->getConfig($this->defaults);
		$builder->addDefinition($this->prefix('document'))
			->setClass('App\Extensions\DocumentService')
			->addSetup('setFolders', [$config['folder']])
			->addSetup('setWebPath', [$config['webPath']])
			->setInject(TRUE);
	}

	public static function register(Configurator $configurator) {
		$configurator->onCompile[] = function ($config, Compiler $compiler) {
			$compiler->addExtension('document', new DocumentExtension());
		};
	}
}