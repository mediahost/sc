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
		$services = [ // FUNCTION => [NAME => PARAMS]
			'roles' => ['Roles' => [$this->permissions->getRoles(), $this->roleFacade]],
			'users' => ['Users' => [$this->initUsers, $this->roleFacade, $this->userFacade]],
			'doctrine' => ['Doctrine' => []],
//			'adminer' => ['Adminer' => [$this->wwwDir]],
		];
		foreach ($services as $function => $serviceArr) {
			list($name, $params) = each($serviceArr);
			$this->callService($function, $name, $params);
		}

		$this->terminate();
	}

	public function setPathes($tempDir, $wwwDir)
	{
		$this->tempDir = $tempDir;
		$this->wwwDir = $wwwDir;
		$this->installDir = $this->tempDir . '/install';
		return $this;
	}

	/**
	 * Set users to init
	 * @param type $users
	 * @return \App\FrontModule\Presenters\InstallPresenter
	 */
	public function setUsers($users)
	{
		if (is_array($users)) {
			$this->initUsers = $users;
		}
		return $this;
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
