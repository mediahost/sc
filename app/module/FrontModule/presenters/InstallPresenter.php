<?php

namespace App\FrontModule\Presenters;

use Nette;
use Tracy\Debugger as Debug;

/**
 * Install presenter.
 */
class InstallPresenter extends BasePresenter
{
	
	const PARAM_USERS = "startUsers";
	const PARAM_DOCTRINE = "doctrine";
	const PARAM_ADMINER = "adminer";
	const PARAM_COMPOSER = "composer";

	/** @var string */
	private $tempDir;

	/** @var string */
	private $wwwDir;

	/** @var string */
	private $appDir;

	/** @var string */
	private $installDir;

	/** @var array */
	private $initUsers = [];

	/** @var \App\Model\Facade\UserFacade @inject */
	public $userFacade;

	/** @var \App\Model\Facade\RoleFacade @inject */
	public $roleFacade;

	/** @var Nette\Security\IAuthorizator @inject */
	public $permissions;

	/** @var array */
	private $toInstall = [];

	/** @var array */
	private $installParams = [];

	public function __construct($tempDir = NULL, $wwwDir = NULL, $appDir = NULL, $params = [])
	{
		parent::__construct();
		$this->setPathes($tempDir, $wwwDir, $appDir);
		$allowedParams = [
			self::PARAM_USERS,
			self::PARAM_DOCTRINE,
			self::PARAM_ADMINER,
			self::PARAM_COMPOSER,
		];
		foreach ($allowedParams as $param) {
			$value = NULL;
			if (array_key_exists($param, $params)) {
				$value = $params[$param];
			}
			$this->installParams[$param] = $value;
		}
	}

	protected function startup()
	{
		parent::startup();
		if (!is_dir($this->installDir)) {
			mkdir($this->installDir);
		}
		$this->setUsers($this->installParams[self::PARAM_USERS]);
		$this->installDb($this->installParams[self::PARAM_DOCTRINE]);
		$this->installAdminer($this->installParams[self::PARAM_ADMINER]);
		$this->installComposer($this->installParams[self::PARAM_COMPOSER]);
	}

	public function actionDefault()
	{
		foreach ($this->toInstall as $function => $serviceArr) {
			list($name, $params) = each($serviceArr);
			$this->callService($function, $name, $params);
		}

		$this->terminate();
	}

	private function setPathes($tempDir, $wwwDir, $appDir)
	{
		$this->tempDir = $tempDir;
		$this->wwwDir = $wwwDir;
		$this->appDir = $appDir;
		$this->installDir = $this->tempDir . '/install';
		return $this;
	}

	/**
	 * Set users to init
	 * @param type $users
	 * @return \App\FrontModule\Presenters\InstallPresenter
	 */
	private function setUsers($users)
	{
		if (is_array($users)) {
			$this->initUsers = $users;
		}
		return $this;
	}
	
	private function installDb($doctrine = FALSE)
	{
		if ($doctrine) {
			$this->toInstall['doctrine'] = ['Doctrine' => []];
		}
		$this->toInstall['roles'] = ['Roles' => [$this->permissions->getRoles(), $this->roleFacade]];
		$this->toInstall['users'] = ['Users' => [$this->initUsers, $this->roleFacade, $this->userFacade]];
	}
	
	private function installAdminer($adminer = FALSE)
	{
		if ($adminer) {
			$this->toInstall['adminer'] = ['Adminer' => [$this->wwwDir]];
		}
	}
	
	private function installComposer($composer = FALSE)
	{
		if ($composer) {
			$this->toInstall['composer'] = ['Composer' => [$this->appDir]];
		}
	}

	/**
	 * Call functions from \App\Model\Installer\Installer
	 * @param type $function
	 * @param type $name
	 * @param array $params
	 */
	private function callService($function, $name, array $params)
	{
		$lockFile = $this->installDir . '/' . $function;
		if (!file_exists($lockFile)) {
			$installer = new \App\Model\Installer\Installer;
			$method = 'install' . ucfirst($function);
			if (\call_user_func_array([$installer, $method], $params)) {
				$this->lockFile($lockFile);
				$this->message($name . ' - INSTALLED');
			} else {
				$this->message($name . ' - NOT INSTALLED');
			}
		} else {
			$this->message($name . ' - ALREADY INSTALLED');
		}
	}

	/**
	 * Lock instalation file
	 * @param type $lockFile
	 */
	private function lockFile($lockFile)
	{
		file_put_contents($lockFile, '1');
	}

	/**
	 * Print control message
	 * @param type $message
	 */
	private function message($message)
	{
		echo $message . '<br/>';
	}

}
