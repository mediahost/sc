<?php

namespace App\Extensions\Settings\DI;

use Nette\Configurator;
use Nette\DI\Compiler;
use Nette\DI\CompilerExtension;

/**
 * @author Martin Šifra <me@martinsifra.cz>
 */
class SettingsExtension extends CompilerExtension
{

	/** @var array */
	public $defaults = [
		'expiration' => [
			'recovery' => '30 minutes',
			'verification' =>  '1 hour',
			'registration' => '1 hour',
			'remember' => '14 days',
			'notRemember' => '30 minutes'
		]
	];

	public function loadConfiguration()
	{
		$builder = $this->getContainerBuilder();
		$config = $this->getConfig($this->defaults);

		$builder->addDefinition($this->prefix('settings'))
				->setClass('App\Model\Storage\SettingsStorage')
				->addSetup('setExpiration', [$config['expiration']]);
	}
	
	/** @param Configurator $configurator */
	public static function register(Configurator $configurator)
	{
		$configurator->onCompile[] = function ($config, Compiler $compiler) {
			$compiler->addExtension('settings', new SettingsExtension());
		};
	}

}
