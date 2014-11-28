<?php

namespace App\Extensions\Settings\DI;

use Nette\Configurator;
use Nette\DI\Compiler;
use Nette\DI\CompilerExtension;

/**
 * @author Martin Šifra <me@martinsifra.cz>
 * @author Petr Poupě <petr.poupe@gmail.com>
 */
class SettingsExtension extends CompilerExtension
{

	/** @var array */
	public $defaults = [
		'web' => [
			'modules' => [], // auto generated default FALSE
			'modulesSettings' => [], // auto generated default NULL
			'page' => [], // auto generated default NULL
			'controls' => [
				'expiration' => [
					'recovery' => '30 minutes',
					'verification' => '1 hour',
					'registration' => '1 hour',
					'remember' => '14 days',
					'notRemember' => '30 minutes',
				],
				'passwords' => [
					'length' => 8,
				],
				'languages' => [
					'default' => 'en',
					'allowed' => ['en' => 'English'],
				],
				'page' => [
					'itemsPerPage' => 20,
					'itemsPerRow' => 3,
					'rowsPerPage' => 4,
				],
			],
		],
		'user' => [
			'preferences' => [
				'page' => [
					'language' => 'cs',
				],
				'design' => [
					'color' => 'default',
					'pageHeaderFixed' => FALSE,
					'pageSidebarClosed' => FALSE,
					'pageSidebarFixed' => FALSE,
					'pageFooterFixed' => FALSE,
					'pageSidebarReversed' => FALSE,
					'pageFullWidth' => FALSE,
					'pageContainerBgSolid' => FALSE,
				],
			],
		],
	];

	public function loadConfiguration()
	{
		$builder = $this->getContainerBuilder();
		$config = $this->getConfig($this->defaults);

		$builder->addDefinition($this->prefix('settings'))
				->setClass('App\Model\Storage\SettingsStorage')
				->setInject(TRUE)
				->addSetup('setModules', [$config['web']['modules'], $config['web']['modulesSettings']])
				->addSetup('setPageInfo', [$config['web']['page']])
				->addSetup('setPageControls', [$config['web']['controls']['page']])
				->addSetup('setExpiration', [$config['web']['controls']['expiration']])
				->addSetup('setLanguages', [$config['web']['controls']['languages']])
				->addSetup('setPasswordsPolicy', [$config['web']['controls']['passwords']])
				->addSetup('setUserPreferences', [$config['user']['preferences']['page'], $config['user']['preferences']['design']]);
	}

	/** @param Configurator $configurator */
	public static function register(Configurator $configurator)
	{
		$configurator->onCompile[] = function ($config, Compiler $compiler) {
			$compiler->addExtension('settings', new SettingsExtension());
		};
	}

}
