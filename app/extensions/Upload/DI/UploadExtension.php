<?php

namespace App\Extensions\Document\DI;

use Nette\DI\CompilerExtension;

class UploadExtension extends CompilerExtension
{
	public $defaults = [
		'folder' => '%wwwDir%',
		'url' => '/'
	];

	public function loadConfiguration()
	{
		$builder = $this->getContainerBuilder();
		$config = $this->getConfig($this->defaults);
		$builder->addDefinition($this->prefix('upload'))
			->setClass('App\Extensions\UploadService')
			->addSetup('setFolder', [$config['folder']])
			->addSetup('setUrl', [$config['url']])
			->setInject(TRUE);
	}
}