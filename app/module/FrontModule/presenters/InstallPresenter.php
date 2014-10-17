<?php

namespace App\FrontModule\Presenters;

use Nette;
use Tracy\Debugger as Debug;

/**
 * Install presenter.
 */
class InstallPresenter extends BasePresenter
{

	// <editor-fold defaultstate="collapsed" desc="constants & variables">

	const PARAM_USERS = "startUsers";
	const PARAM_DOCTRINE = "doctrine";
	const PARAM_ADMINER = "adminer";
	const PARAM_COMPOSER = "composer";
	const PARAM_LOCK = "lockInstaller";

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

	/** @var \Doctrine\ORM\EntityManager @inject */
	public $em;

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

	/** @var bool */
	private $printHtml;

	// </editor-fold>

	public function __construct($tempDir = NULL, $wwwDir = NULL, $appDir = NULL, $params = [])
	{
		parent::__construct();
		$this->setPathes($tempDir, $wwwDir, $appDir);
		
		// default values
		$this->installParams[self::PARAM_USERS] = NULL;
		$this->installParams[self::PARAM_DOCTRINE] = FALSE;
		$this->installParams[self::PARAM_ADMINER] = FALSE;
		$this->installParams[self::PARAM_COMPOSER] = FALSE;
		$this->installParams[self::PARAM_LOCK] = TRUE;
		
		// for each default value load value from config
		foreach ($this->installParams as $param => $value) {
			if (array_key_exists($param, $params)) {
				$this->installParams[$param] = $params[$param];
			}
		}
	}

	protected function startup()
	{
		parent::startup();
		if (!is_dir($this->installDir)) {
			mkdir($this->installDir);
		}
		$this->setUsers($this->installParams[self::PARAM_USERS]);

		// Pouze přidají instalace do fronty - neprovádějí je!!!
		$this->installDb($this->installParams[self::PARAM_DOCTRINE]);
		$this->installAdminer($this->installParams[self::PARAM_ADMINER]);
		$this->installComposer($this->installParams[self::PARAM_COMPOSER]);
	}

	public function actionDefault($printHtml = TRUE)
	{
		$this->printHtml = $printHtml;

		foreach ($this->toInstall as $function => $serviceArr) {
			list($name, $params) = each($serviceArr);
			$this->callService($function, $name, $params);
		}
		$this->afterInstall();

		$this->terminate();
	}

	// <editor-fold defaultstate="collapsed" desc="setters">

	/**
	 * Set nested pathes
	 * @param type $tempDir
	 * @param type $wwwDir
	 * @param type $appDir
	 * @return \App\FrontModule\Presenters\InstallPresenter
	 */
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

	// </editor-fold>
	// <editor-fold defaultstate="collapsed" desc="instalations">

	/**
	 * Add install DB to queue 'toInstall'
	 * can create/update DB tables
	 * set all nested thing (users, roles) to DB
	 * @param bool $doctrine if create/update DB
	 */
	private function installDb($doctrine = FALSE)
	{
		if ($doctrine) {
			$this->toInstall['doctrine'] = ['Doctrine' => [$this->em]];
		}
		$this->toInstall['roles'] = ['Roles' => [$this->permissions->getRoles(), $this->roleFacade]];
		$this->toInstall['users'] = ['Users' => [$this->initUsers, $this->roleFacade, $this->userFacade]];
	}

	/**
	 * Add install adminer to queue 'toInstall'
	 * @param bool $adminer if install adminer
	 */
	private function installAdminer($adminer = FALSE)
	{
		if ($adminer) {
			$this->toInstall['adminer'] = ['Adminer' => [$this->wwwDir]];
		}
	}

	/**
	 * Add install composer to queue 'toInstall'
	 * @param bool $composer if install composer
	 */
	private function installComposer($composer = FALSE)
	{
		if ($composer) {
			$this->toInstall['composer'] = ['Composer' => [$this->appDir]];
		}
	}

	// </editor-fold>
	// <editor-fold defaultstate="collapsed" desc="after jobs">

	/**
	 * Set clear user settings to all users without settings
	 */
	private function setUserSettings()
	{
		$userDao = $this->em->getDao(\App\Model\Entity\User::getClassName());
		$users = $userDao->findAll();

		/* @var $user \App\Model\Entity\User */
		foreach ($users as $user) {
			if ($user->settings === NULL) {
				$user->settings = new \App\Model\Entity\UserSettings();
				$this->em->persist($user);
			}
		}

		$this->em->flush();
	}

	// </editor-fold>
	// <editor-fold defaultstate="collapsed" desc="private functions">

	/**
	 * Jobs after install is complete
	 */
	private function afterInstall()
	{
		$this->setUserSettings();
	}

	/**
	 * Call functions from \App\Model\Installer\Installer
	 * @param type $function
	 * @param type $name
	 * @param array $params
	 */
	private function callService($function, $name, array $params)
	{
		$lockFile = $this->installDir . '/' . $function . '.lock';
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
		if ($this->installParams[self::PARAM_LOCK]) {
			file_put_contents($lockFile, '1');
		}
	}

	/**
	 * Print control message
	 * @param type $message
	 */
	private function message($message)
	{
		echo $message . ($this->printHtml ? '<br/>' : ' | ');
	}

	// </editor-fold>
}
