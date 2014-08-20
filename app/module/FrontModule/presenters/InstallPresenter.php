<?php

namespace App\FrontModule\Presenters;

use Nette;
use Tracy\Debugger as Debug;

/**
 * Install presenter.
 */
class InstallPresenter extends BasePresenter
{

	/** @var string */
	private $tempDir;

	/** @var string */
	private $wwwDir;

	/** @var string */
	private $installDir;

	/** @var array */
	private $initUsers = [];

	/** @var \App\Model\Facade\Users @inject */
	public $userFacade;

	/** @var \App\Model\Facade\Roles @inject */
	public $roleFacade;

	/** @var Nette\Security\IAuthorizator @inject */
	public $permissions;

	public function __construct($tempDir = NULL, $wwwDir = NULL)
	{
		parent::__construct();
		$this->setPathes($tempDir, $wwwDir);
	}

	public function setPathes($tempDir, $wwwDir)
	{
		\Tracy\Debugger::barDump($tempDir);
		\Tracy\Debugger::barDump($wwwDir);
		$this->tempDir = $tempDir;
		$this->wwwDir = $wwwDir;
		$this->installDir = $this->tempDir . "/install";
		return $this;
	}

	public function setUsers($users)
	{
		if (is_array($users)) {
			$this->initUsers = $users;
		}
		return $this;
	}

	protected function startup()
	{
		parent::startup();
		if (!is_dir($this->installDir)) {
			mkdir($this->installDir);
		}
	}

	public function actionDefault()
	{
		$this->installAll();
		$this->terminate();
	}

	private function installAll()
	{
		$services = [
			"roles" => "Roles",
			"users" => "Users",
//			"adminer" => "Adminer",
		];
		foreach ($services as $service => $serviceName) {
			$this->callService($service, $serviceName);
		}
	}

	private function callService($service, $name)
	{
		$lockFile = $this->installDir . "/" . $service;
		if (!file_exists($lockFile)) {
			$method = "init" . ucfirst($service);
			if (\call_user_func([$this, $method])) {
				$this->lockFile($lockFile);
				$this->message($name . " - INSTALLED");
			} else {
				$this->message($name . " - NOT INSTALLED");
			}
		} else {
			$this->message($name . " - ALREADY INSTALLED");
		}
	}

	/**
	 * Set database as writable
	 * @return boolean
	 */
	private function initAdminer()
	{
		chmod($this->wwwDir . "/adminer/database.sql", 0777);
		return TRUE;
	}

	/**
	 * Create all nested roles
	 * @return boolean
	 */
	private function initRoles()
	{
		foreach ($this->permissions->getRoles() as $roleName) {
			$this->roleFacade->create($roleName);
		}
		return TRUE;
	}

	/**
	 * Create default users
	 * @return boolean
	 */
	private function initUsers()
	{
		foreach ($this->initUsers as $initUserMail => $initUserData) {
			$pass = $initUserData[0];
			$role = $initUserData[1];
			$roleEntity = $this->roleFacade->findByName($role);
			$this->userFacade->create($initUserMail, $pass, $roleEntity);
		}
		return TRUE;
	}

	private function lockFile($lockFile)
	{
		file_put_contents($lockFile, "1");
	}

	private function message($message)
	{
		echo $message . "<br/>";
	}

}
