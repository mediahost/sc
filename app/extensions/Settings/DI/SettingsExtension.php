<?php

namespace App\Extensions\Settings\DI;

use Nette\DI\CompilerExtension;

class SettingsExtension extends CompilerExtension
{

	/** @var array */
	public $defaults = [
		'modules' => [
			'api' => [
				'enabled' => FALSE,
				'allowedIps' => ['127.0.0.1'],
			],
			'registrableRoles' => ['company', 'candidate'],
			'notifications' => [
				'enabled' => TRUE,
				'from' => 'info@source-code.com',
				'newMessage' => TRUE,
			],
			'jobs' => [
				'defaultAccountManagerId' => 1,
			],
		],
		'pageInfo' => [
			'projectName' => 'SourceCode',
			'author' => 'Mediahost.sk',
			'authorUrl' => 'http://www.mediahost.sk/',
			'keywords' => 'keywords',
			'description' => 'description',
		],
		'pageConfig' => [
			'itemsPerRow' => 4,
			'rowsPerPage' => 4,
		],
		'expiration' => [
			'recovery' => '30 minutes',
			'verification' => '1 hour',
			'registration' => '1 hour',
			'linkAccess' => '24 hour',
			'remember' => '14 days',
			'notRemember' => '30 minutes',
		],
		'passwords' => [
			'length' => 8,
		],
		'mails' => [
			'automatFrom' => 'system@source-code.com',
		],
	];

	public function loadConfiguration()
	{
		$builder = $this->getContainerBuilder();
		$config = $this->getConfig($this->defaults);

		$builder->addDefinition($this->prefix('settings'))
			->setClass('App\Extensions\Settings\SettingsStorage')
			->addSetup('setPageInfo', [$config['pageInfo']])
			->addSetup('setPageConfig', [$config['pageConfig']])
			->addSetup('setExpiration', [$config['expiration']])
			->addSetup('setPasswords', [$config['passwords']])
			->addSetup('setMails', [$config['mails']])
			->addSetup('setModules', [$config['modules']])
			->setInject(TRUE);
	}

}
