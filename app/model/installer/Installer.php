<?php

namespace App\Model\Installer;

use Tracy\Debugger as Debug;

/**
 * Installer
 *
 * @author Petr Poupě
 */
class Installer
{

	public function __call($name, $arguments)
	{
		return FALSE;
	}

	/**
	 * Create all nested roles
	 * @return boolean
	 */
	public function installRoles(array $roles, \App\Model\Facade\RoleFacade $roleFacade)
	{
		foreach ($roles as $roleName) {
			$roleFacade->create($roleName);
		}
		return TRUE;
	}

	/**
	 * Create default users
	 * @return boolean
	 * @throws \Nette\InvalidArgumentException
	 */
	public function installUsers(array $users, \App\Model\Facade\RoleFacade $roleFacade, \App\Model\Facade\UserFacade $userFacade)
	{
		foreach ($users as $initUserMail => $initUserData) {
			if (!is_array($initUserData) || !array_key_exists(0, $initUserData) || !array_key_exists(1, $initUserData)) {
				throw new \Nette\InvalidArgumentException('Invalid users array. Must be [user_mail => [password, role]].');
			}
			$pass = $initUserData[0];
			$role = $initUserData[1];
			$roleEntity = $roleFacade->findByName($role);
			if (!$roleEntity) {
				throw new \Nette\InvalidArgumentException('Invalid name of role. Check if exists role with name \'' . $role . '\'.');
			}
			$userFacade->create($initUserMail, $pass, $roleEntity);
		}
		return TRUE;
	}

	/**
	 * Update database
	 * @return boolean
	 */
	public function installDoctrine(\Doctrine\ORM\EntityManager $em)
	{
		$tool = new \Doctrine\ORM\Tools\SchemaTool($em);
		$classes = [
			$em->getClassMetadata('App\Model\Entity\User'),
			$em->getClassMetadata('App\Model\Entity\UserSettings'),
			$em->getClassMetadata('App\Model\Entity\Role'),
			$em->getClassMetadata('App\Model\Entity\SignUp'),
			$em->getClassMetadata('App\Model\Entity\Facebook'),
			$em->getClassMetadata('App\Model\Entity\Twitter'),
			$em->getClassMetadata('App\Model\Entity\Company')
		];
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
			$file = $wwwDir . '/adminer/database.sql';
		}
		if (file_exists($file)) {
			@chmod($file, 0777);
		}
		return TRUE;
	}

	/**
	 * Install or update composer
	 * NON TESTED - only for localhost use
	 * @return boolean
	 * @deprecated
	 */
	public function installComposer($appDir)
	{
		$oldcwd = getcwd();
		chdir($oldcwd . "/..");
		if (is_file($appDir . "/../composer.lock")) {
			$print = @shell_exec('composer update');
		} else {
			$print = @shell_exec('composer instal');
		}
		chdir($oldcwd);
		echo '-----------<br />';
		echo '<pre>' . $print . '</pre>';
		echo '-----------<br />';
		return TRUE;
	}

}
