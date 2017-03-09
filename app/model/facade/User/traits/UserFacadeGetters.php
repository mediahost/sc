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
		return $this->userRepo->findPairs('mail');
	}

	/**
	 * Get all users in inserted role
	 * @param Role $role
	 * @return array
	 */
	public function getUserMailsInRole(Role $role)
	{
		return $this->userRepo->findPairsByRoleId($role->id, 'mail');
	}

	public function getDealers()
	{
		$admin = $this->roleDao->findOneByName(Role::ADMIN);
		$dealers = $this->userRepo->findBy([
			'isDealer' => TRUE,
			'roles.id' => $admin,
		]);
		return $dealers;
	}

	public function getDealersPairs()
	{
		$pairs = [];
		$dealers = $this->getDealers();
		foreach ($dealers as $dealer) {
			$pairs[$dealer->id] = (string)$dealer;
		}
		return $pairs;
	}

}
