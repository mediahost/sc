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
		'modules' => [], // auto generated default FALSE
		'modulesSettings' => [], // auto generated default NULL
		'pageInfo' => [],
		'pageConfig' => [
			'itemsPerPage' => 20,
			'itemsPerRow' => 3,
			'rowsPerPage' => 4,
		],
		'expiration' => [
			'recovery' => '30 minutes',
			'verification' => '1 hour',
			'registration' => '1 hour',
			'remember' => '14 days',
			'notRemember' => '30 minutes',
		],
		'languages' => [
			'default' => 'en',
			'allowed' => ['en' => 'English', 'cs' => 'Czech'],
			'recognize' => ['en' => 'en', 'cs_CZ' => 'cs'], // https://docs.moodle.org/dev/Table_of_locales
		],
		'passwords' => [
			'length' => 8,
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
	];

	public function loadConfiguration()
	{
		$builder = $this->getContainerBuilder();
		$config = $this->getConfig($this->defaults);

		$builder->addDefinition($this->prefix('defaults'))
				->setClass('App\Extensions\Settings\Model\Storage\DefaultSettingsStorage')
				->addSetup('setModules', [$config['modules'], $config['modulesSettings']])
				->addSetup('setPageInfo', [$config['pageInfo']])
				->addSetup('setPageConfig', [$config['pageConfig']])
				->addSetup('setExpiration', [$config['expiration']])
				->addSetup('setLanguages', [$config['languages']])
				->addSetup('setPasswords', [$config['passwords']])
				->addSetup('setDesign', [$config['design']])
				->setInject(TRUE);

		$builder->addDefinition($this->prefix('design'))
				->setClass('App\Extensions\Settings\Model\Service\DesignService')
				->setInject(TRUE);

		$builder->addDefinition($this->prefix('language'))
				->setClass('App\Extensions\Settings\Model\Service\LanguageService')
				->setInject(TRUE);

		$builder->addDefinition($this->prefix('password'))
				->setClass('App\Extensions\Settings\Model\Service\PasswordService')
				->setInject(TRUE);

		$builder->addDefinition($this->prefix('expiration'))
				->setClass('App\Extensions\Settings\Model\Service\ExpirationService')
				->setInject(TRUE);

		$builder->addDefinition($this->prefix('pageInfo'))
				->setClass('App\Extensions\Settings\Model\Service\PageInfoService')
				->setInject(TRUE);

		$builder->addDefinition($this->prefix('pageConfig'))
				->setClass('App\Extensions\Settings\Model\Service\PageConfigService')
				->setInject(TRUE);

		$builder->addDefinition($this->prefix('module'))
				->setClass('App\Extensions\Settings\Model\Service\ModuleService')
				->setInject(TRUE);
	}

	/** @param Configurator $configurator */
	public static function register(Configurator $configurator)
	{
		$configurator->onCompile[] = function ($config, Compiler $compiler) {
			$compiler->addExtension('settings', new SettingsExtension());
		};
	}

}
