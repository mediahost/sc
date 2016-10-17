<?php

namespace App\Extensions\Document\DI;

use Nette\DI\CompilerExtension;

class UploadExtension extends CompilerExtension
{
	public $defaults = [
		'root_folder' => '%wwwDir%',
		'cvs_folder' => 'files/cvs',
		'documents_folder' => 'documents',
		'root_url' => '/'
	];

	public function loadConfiguration()
	{
		$builder = $this->getContainerBuilder();
		$config = $this->getConfig($this->defaults);
		$builder->addDefinition($this->prefix('upload'))
			->setClass('App\Extensions\UploadService')
			->addSetup('setFolders', [$config])
			->addSetup('setUrl', [$config['root_url']])
			->setInject(TRUE);
	}
}