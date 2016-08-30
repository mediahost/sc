<?php

namespace App\Extensions\Installer\Model;

use App\Model\Entity\SkillLevel;
use App\Model\Facade\CompanyFacade;
use App\Model\Facade\JobFacade;
use App\Model\Facade\RoleFacade;
use App\Model\Facade\UserFacade;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Tools\SchemaTool;
use Nette\InvalidArgumentException;
use Nette\Object;

class InstallerModel extends Object
{

	const ADMINER_FILENAME = '/adminer/database-log.sql';

	// <editor-fold desc="injects">

	/** @var EntityManager @inject */
	public $em;

	/** @var RoleFacade @inject */
	public $roleFacade;

	/** @var UserFacade @inject */
	public $userFacade;

	/** @var CompanyFacade @inject */
	public $companyFacade;
	
	/** @var JobFacade @inject */
	public $jobFacade;

	// </editor-fold>
	// <editor-fold desc="installers">

	/**
	 * Create all nested roles
	 * @return boolean
	 */
	public function installRoles(array $roles)
	{
		foreach ($roles as $roleName) {
			$this->roleFacade->create($roleName);
		}
		return TRUE;
	}

	/**
	 * Create all nested roles
	 * @return boolean
	 */
	public function installCompany(array $roles)
	{
		foreach ($roles as $roleName) {
			$this->companyFacade->createRole($roleName);
		}
		return TRUE;
	}

	/**
	 * Create all nested skill levels
	 * @return boolean
	 */
	public function installSkillLevels(array $levels)
	{
		$skillLevelRepo = $this->em->getRepository(SkillLevel::getClassName());
		foreach ($levels as $levelId => $levelName) {
			$skillLevel = $skillLevelRepo->find($levelId);
			if (!$skillLevel) {
				$skillLevel = new SkillLevel;
			}
			$skillLevel->name = $levelName;
			$skillLevel->priority = $levelId;
			$skillLevelRepo->save($skillLevel);
		}
		return TRUE;
	}

	/**
	 * Create default users
	 * @return boolean
	 * @throws InvalidArgumentException
	 */
	public function installUsers(array $users)
	{
		foreach ($users as $initUserMail => $initUserData) {
			if (!is_array($initUserData) || !array_key_exists(0, $initUserData) || !array_key_exists(1, $initUserData)) {
				throw new InvalidArgumentException('Invalid users array. Must be [user_mail => [password, role]].');
			}
			$pass = $initUserData[0];
			$role = $initUserData[1];
			$roleEntity = $this->roleFacade->findByName($role);
			if (!$roleEntity) {
				throw new InvalidArgumentException('Invalid name of role. Check if exists role with name \'' . $role . '\'.');
			}
			$this->userFacade->create($initUserMail, $pass, $roleEntity);
		}
		return TRUE;
	}

	/**
	 * Update database
	 * @return boolean
	 */
	public function installDoctrine()
	{
		$tool = new SchemaTool($this->em);
		$classes = $this->em->getMetadataFactory()->getAllMetadata();
		$tool->updateSchema($classes); // php index.php orm:schema-tool:update --force
		return TRUE;
	}

	/**
	 * Set database as writable
	 * @param type $wwwDir
	 * @param string $file
	 * @return boolean
	 * @deprecated It FAILS on server (chmod has insufficient permissions), its required special settings for FTP deployment
	 */
	public function installAdminer($wwwDir, $file = NULL)
	{
		if (!$file) {
			$file = $wwwDir . self::ADMINER_FILENAME;
		}
		if (is_dir($wwwDir)) {
			if (!file_exists($file)) {
				$handle = fopen($file, "w");
				fclose($handle);
			}
			@chmod($file, 0777);
		}
		return TRUE;
	}

	/**
	 * Install or update composer
	 * NON TESTED - only for localhost use
	 * @param string $appDir
	 * @param string $print
	 * @return boolean
	 * @deprecated no function - https://github.com/composer/composer/issues/1906
	 */
	public function installComposer($appDir, &$print = NULL)
	{
		@system('composer install -n -q --dev -d ./..');
		return TRUE;
	}

	// </editor-fold>
}
