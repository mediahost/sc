<?php

namespace App\Model\Facade\Traits;

use Nette\Security\User as IdentityUser;
use App\Model\Entity\User;
use App\Model\Entity\Role;

trait UserFacadeAccess {
    
    
    /**
	 * Decides if identity user can access user
	 * @param IdentityUser $identityUser
	 * @param User $user
	 * @return boolean
	 */
	public function canAccess(IdentityUser $identityUser, User $user)
	{
		return $this->canEdit($identityUser, $user);
	}
    
    /**
	 * Decides if identity user can delete user
	 * @param IdentityUser $identityUser
	 * @param User $user
	 * @return boolean
	 */
	public function canDelete(IdentityUser $identityUser, User $user)
	{
		$isDeletable = $this->isDeletable($user);
		return $this->canEdit($identityUser, $user) && $isDeletable;
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
			return FALSE;
		} else {
			// pokud je nejvyšší uživatelova role v nižších rolích přihlášeného uživatele
			// tedy může editovat pouze uživatele s nižšími rolemi
			$identityLowerRoles = $this->findLowerRoles($identityUser->roles);
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
