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

	public function __construct($tempDir = NULL, $wwwDir = NULL, $users = NULL)
	{
		parent::__construct();
		$this->setPathes($tempDir, $wwwDir);
		$this->setUsers($users);
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
		$services = [
			"roles" => ["Roles" => [$this->permissions->getRoles(), $this->roleFacade]],
			"users" => ["Users" => [$this->initUsers, $this->roleFacade, $this->userFacade]],
//			"adminer" => ["Adminer" => [$this->wwwDir]],
		];
		foreach ($services as $service => $serviceArr) {
			$this->callService($service, $serviceArr);
		}
		
		$this->terminate();
	}

	public function setPathes($tempDir, $wwwDir)
	{
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

	private function callService($service, array $data)
	{
		list($name, $params) = each($data);
		$lockFile = $this->installDir . "/" . $service;
		if (!file_exists($lockFile)) {
			$method = "install" . ucfirst($service);
			$obj = new \App\Model\Installer\Installer;
			if (\call_user_func_array([$obj, $method], $params)) {
				$this->lockFile($lockFile);
				$this->message($name . " - INSTALLED");
			} else {
				$this->message($name . " - NOT INSTALLED");
			}
		} else {
			$this->message($name . " - ALREADY INSTALLED");
		}
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
