<?php

namespace App\Model\Facade\Traits;

use App\Model\Entity\Role;

trait UserFacadeGetters
{

	/**
	 * Get all users
	 * @return array
	 */
	public function getUsers()
	{
		return $this->userDao->findPairs('mail');
	}

	/**
	 * Get all users in inserted role
	 * @param Role $role
	 * @return array
	 */
	public function getUserMailsInRole(Role $role)
	{
		return $this->userDao->findPairsByRoleId($role->id, 'mail');
	}

}
