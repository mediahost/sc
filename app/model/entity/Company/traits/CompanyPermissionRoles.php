<?php

namespace App\Model\Entity\Traits;

use App\Model\Entity\CompanyRole;
use App\Security\CompanyPermission as Authorizator;
use Doctrine\Common\Collections\Collection;
use Nette\Security\IAuthorizator;

/**
 * @property Collection $roles
 * @property-read array $rolesKeys
 */
trait CompanyPermissionRoles
{

	/** @ORM\ManyToMany(targetEntity="CompanyRole") */
	private $roles;

	/** @var IAuthorizator */
	private $authorizator;

	public function addRole(CompanyRole $role)
	{
		if (!$this->roles->contains($role)) {
			$this->roles->add($role);
		}
		return $this;
	}

	public function addRoles(array $roles)
	{
		foreach ($roles as $role) {
			$this->addRole($role);
		}
		return $this;
	}

	public function clearRoles()
	{
		$this->roles->clear();
		return $this;
	}

	public function containRoleName($name)
	{
		$hasRoleName = function ($key, CompanyRole $role) use ($name) {
			return $role->name === $name;
		};
		return $this->roles->exists($hasRoleName);
	}

	/** @return Collection */
	public function getRoles()
	{
		return $this->roles;
	}

	public function getRolesKeys()
	{
		$array = [];
		foreach ($this->roles as $role) {
			$array[] = $role->id;
		}
		return $array;
	}

	/**
	 * Has a user effective access to the company Resource?
	 * @param  string  resource
	 * @param  string  privilege
	 * @return bool
	 */
	public function isAllowed($resource = IAuthorizator::ALL, $privilege = IAuthorizator::ALL)
	{
		$authorizator = $this->getAuthorizator();
		foreach ($this->roles as $role) {
			if ($authorizator->isAllowed((string) $role, $resource, $privilege)) {
				return TRUE;
			}
		}
		return FALSE;
	}

	public function setAuthorizator(IAuthorizator $authorizator)
	{
		$this->authorizator = $authorizator;
		return $this;
	}

	private function getAuthorizator()
	{
		if (!$this->authorizator) {
			$this->authorizator = new Authorizator;
		}
		return $this->authorizator;
	}

}