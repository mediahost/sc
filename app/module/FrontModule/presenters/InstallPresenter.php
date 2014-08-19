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

	public function __construct($tempDir = NULL, $wwwDir = NULL)
	{
		parent::__construct();
		$this->setPathes($tempDir, $wwwDir);
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

	protected function startup()
	{
		parent::startup();
		if (!is_dir($this->installDir)) {
			mkdir($this->installDir);
		}
	}

	public function actionDefault()
	{
		$this->install();
		$this->terminate();
	}

	private function install()
	{
		$lockFile = $this->installDir . "/adminer";
		if (!file_exists($lockFile)) {
//			$this->initAdminer();
			$this->initUsers();
			$this->lockFile($lockFile);
		}
	}

	private function initAdminer()
	{
		chmod($this->wwwDir . "/adminer/database.sql", 0777);
	}

	private function initUsers()
	{
		foreach ($this->initUsers as $initUserMail => $initUserData) {
			$pass = $initUserData[0];
			$role = $initUserData[1];
			$roleEntity = $this->roleFacade->findByName($role);
			$this->userFacade->create($initUserMail, $pass, $roleEntity);
		}
	}

	private function lockFile($lockFile)
	{
		file_put_contents($lockFile, "1");
	}

}
