<?php

namespace Test\Model\Storage\SettingsStorage;

use App\Model\Storage\SettingsStorage;
use Test\ParentTestCase;
use Tester\Assert;

$container = require __DIR__ . '/../../../bootstrap.php';

/**
 * TEST: SettingsStorage
 *
 * @testCase
 * @phpVersion 5.4
 */
class SettingsStorageTest extends ParentTestCase
{
	
	/** @var SettingsStorage @inject */
    public $settings;

	// <editor-fold defaultstate="expanded" desc="tests">
	
	public function testExpiration()
	{
		$settingsExtension = new \App\Extensions\Settings\DI\SettingsExtension;
		$defaultExtensionExpiration = $settingsExtension->defaults['web']['controls']['expiration'];
		
		$defaultExpiration = $this->settings->expiration;
		Assert::same($defaultExpiration->recovery, $defaultExtensionExpiration['recovery']);
		Assert::same($defaultExpiration->verification, $defaultExtensionExpiration['verification']);
		Assert::same($defaultExpiration->registration, $defaultExtensionExpiration['registration']);
		Assert::same($defaultExpiration->remember, $defaultExtensionExpiration['remember']);
		Assert::same($defaultExpiration->notRemember, $defaultExtensionExpiration['notRemember']);
		
		$defaultExtensionExpiration['recovery'] = '365 days';
		$this->settings->setExpiration($defaultExtensionExpiration);
		
		$myExpiration = $this->settings->expiration;
		Assert::same($myExpiration->recovery, $defaultExtensionExpiration['recovery']);
	}
	
	public function testPasswordPolicy()
	{
		$settings = [
			'length' => 8,
		];
		$this->settings->setPasswordsPolicy($settings);
		
		$passwords = $this->settings->passwordsPolicy;
		Assert::same($passwords->length, $settings['length']);
	}
	
	public function testPageControls()
	{
		$settings = [
			'itemsPerPage' => 20,
			'itemsPerRow' => 3,
			'rowsPerPage' => 4,
		];
		$this->settings->setPageControls($settings);
		
		$pageControls = $this->settings->pageControls;
		Assert::same($pageControls->itemsPerPage, $settings['itemsPerPage']);
		Assert::same($pageControls->itemsPerRow, $settings['itemsPerRow']);
		Assert::same($pageControls->rowsPerPage, $settings['rowsPerPage']);
	}
	
	public function testPageInfo()
	{
		$settings = [
			'author' => 'me',
			'description' => 'description',
		];
		$this->settings->setPageInfo($settings);
		
		$pageInfo = $this->settings->pageInfo;
		Assert::same($pageInfo->author, $settings['author']);
		Assert::same($pageInfo->description, $settings['description']);
	}

	public function testModules()
	{
		$ip = '127.0.0.1';
		$modules = [
			'users' => TRUE,
		];
		$modulesSettings = [
			'users' => [
				'onlyForIp' => $ip,
			],
		];
		$this->settings->setModules($modules, $modulesSettings);
		
		Assert::true($this->settings->isAllowedModule('users'));
		Assert::false($this->settings->isAllowedModule('pohoda'));
		
		$usersSettings = $this->settings->getModuleSettings('users');
		Assert::same($ip, $usersSettings->onlyForIp);
		Assert::null($usersSettings->nonInicialized);
		
		Assert::null($this->settings->getModuleSettings('pohoda'));
	}

	// </editor-fold>
}

$test = new SettingsStorageTest($container);
$test->run();
