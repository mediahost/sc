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
	public function installDoctrine()
	{
		$print = @shell_exec('php index.php orm:schema-tool:update --force');
		echo '-----------<br />';
		echo '<pre>' . $print . '</pre>';
		echo '-----------<br />';
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

}
