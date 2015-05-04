<?php

namespace App\Extensions\CvToPdf\DI;

use Nette\Configurator;
use Nette\DI\Compiler;
use Nette\DI\CompilerExtension;

class CvToPdfExtension extends CompilerExtension
{

	/** @var array */
	public $defaults = [
		'folder' => '%wwwDir%/pdf',
	];

	public function loadConfiguration()
	{
		$builder = $this->getContainerBuilder();
		$config = $this->getConfig($this->defaults);

		$builder->addDefinition($this->prefix('foto'))
				->setClass('App\Extensions\CvToPdf')
				->addSetup('setFolder', [$config['folder']])
				->setInject(TRUE);
	}

	/** @param Configurator $configurator */
	public static function register(Configurator $configurator)
	{
		$configurator->onCompile[] = function ($config, Compiler $compiler) {
			$compiler->addExtension('cvToPdf', new CvToPdfExtension());
		};
	}

}
