<?php

namespace App\Model\Facade\Traits;

use Nette\Security\User as IdentityUser;
use App\Model\Entity\User;
use App\Model\Entity\Role;

trait UserFacadeAccess
{

	/**
	 * Decides if identity user can access user
	 * @param IdentityUser $identityUser
	 * @param User $user
	 * @return boolean
	 */
	public function canAccess(IdentityUser $identityUser, User $user)
	{
		if ($identityUser->id === $user->id) {
			return FALSE; // cant acces to myself
		} else if ($identityUser->isInRole(Role::COMPANY)) {
			return FALSE;
		} else if ($user->isUnregistered()) {
			return FALSE;
		} else {
			$identityLowerRoles = $this->findLowerRoles($identityUser->roles); // can acces to only lower roles
			return in_array($user->maxRole->name, $identityLowerRoles);
		}
	}

	/**
	 * Decides if identity user can delete user
	 * @param IdentityUser $identityUser
	 * @param User $user
	 * @return boolean
	 */
	public function canDelete(IdentityUser $identityUser, User $user)
	{
		if ($identityUser->id === $user->id) {
			return FALSE; // cant delete myself
		} else if ($identityUser->isInRole(Role::COMPANY)) {
			return FALSE;
		} else {
			$isDeletable = $this->isDeletable($user);
			return $this->canEdit($identityUser, $user) && $isDeletable;
		}
	}

	/**
	 * Decides if identity user can edit user
	 * @param IdentityUser $identityUser
	 * @param User $user
	 * @return boolean
	 */
	public function canEdit(IdentityUser $identityUser, User $user)
	{
		if ($identityUser->id === $user->id) {
			return TRUE; // can edit myself
		} else if ($identityUser->isInRole(Role::COMPANY)) {
			return $user->isCompany();
		} else {
			$identityLowerRoles = $this->findLowerRoles($identityUser->roles, TRUE); // can edit lower or same roles
			return in_array($user->maxRole->name, $identityLowerRoles);
		}
	}

	public function findLowerRoles(array $roles, $includeMax = FALSE)
	{
		$allRoles = $this->roleDao->findPairs('name'); // expect roles by priority (first is the lowest)
		$lowerRoles = [];
		$maxRole = Role::getMaxRole($roles);
		if (in_array($maxRole->name, $allRoles)) {
			foreach ($allRoles as $id => $dbRole) {
				if ($maxRole->name === $dbRole) {
					if ($includeMax) {
						$lowerRoles[$id] = $dbRole;
					}
					break;
				}
				$lowerRoles[$id] = $dbRole;
			}
		}
		return $lowerRoles;
	}
}
