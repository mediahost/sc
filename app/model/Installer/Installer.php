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
	public function installRoles(array $roles, $roleFacade)
	{
		foreach ($roles as $roleName) {
			$roleFacade->create($roleName);
		}
		return TRUE;
	}

	/**
	 * Create default users
	 * @return boolean
	 */
	public function installUsers(array $users, \App\Model\Facade\RoleFacade $roleFacade, \App\Model\Facade\UserFacade $userFacade)
	{
		foreach ($users as $initUserMail => $initUserData) {
			$pass = $initUserData[0];
			$role = $initUserData[1];
			$roleEntity = $roleFacade->findByName($role);
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
			$em->getClassMetadata('App\Model\Entity\Role'),
			$em->getClassMetadata('App\Model\Entity\Auth'),
			$em->getClassMetadata('App\Model\Entity\Registration'),
		];
		$tool->updateSchema($classes); // php index.php orm:schema-tool:update --force
		return TRUE;
	}

	/**
	 * Set database as writable
	 * @return boolean
	 */
	public function installAdminer($wwwDir)
	{
		@chmod($wwwDir . '/adminer/database.sql', 0777);
		return TRUE;
	}

	/**
	 * Install or update composer
	 * @return boolean
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
