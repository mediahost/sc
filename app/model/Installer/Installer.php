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
	public function installUsers($users, $roleFacade, $userFacade)
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
	 * Set database as writable
	 * @return boolean
	 */
	public function installAdminer($wwwDir)
	{
		chmod($wwwDir . "/adminer/database.sql", 0777);
		return TRUE;
	}

}
