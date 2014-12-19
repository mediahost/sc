<?php

namespace App\Model\Entity;

use App\Security\CompanyPermission as Authorizator;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Kdyby\Doctrine\Entities\BaseEntity;
use Nette\Security\IAuthorizator;

/**
 * @ORM\Entity
 *
 * @property User $user
 * @property Company $company
 * @property ArrayCollection $roles
 * @property-read array $rolesKeys
 */
class CompanyPermission extends BaseEntity
{

	use \Kdyby\Doctrine\Entities\Attributes\Identifier;

	/**
	 * @ORM\ManyToOne(targetEntity="User", inversedBy="allowedCompanies")
	 * */
	protected $user;

	/**
	 * @ORM\ManyToOne(targetEntity="Company", inversedBy="acceses")
	 * */
	protected $company;

	/**
	 * @ORM\ManyToMany(targetEntity="CompanyRole")
	 */
	protected $roles;

	public function __construct()
	{
		parent::__construct();
		$this->roles = new ArrayCollection;
	}

	// <editor-fold defaultstate="collapsed" desc="roles">

	/**
	 * @param CompanyRole|array $role
	 * @param bool $clear Clear all previous roles.
	 * @return self
	 */
	public function addRole($role, $clear = FALSE)
	{
		if ($clear) {
			$this->clearRoles();
		}

		if (is_array($role)) {
			foreach ($role as $entity) {
				if (!$this->roles->contains($entity)) {
					$this->roles->add($entity);
				}
			}
		} else {
			if (!$this->roles->contains($role)) {
				$this->roles->add($role);
			}
		}

		return $this;
	}

	/** @return self */
	public function clearRoles()
	{
		$this->roles->clear();
		return $this;
	}

	/**
	 * Check if any roles has roleName
	 * @param type $roleName
	 * @return type
	 */
	public function containRoleName($roleName)
	{
		return $this->roles->exists(function ($key, CompanyRole $role) use ($roleName) {
					return $role->name === $roleName;
				});
	}

	/**
	 * Return array of roles ids
	 * @return array 
	 */
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
	 * If $resource is NULL, then the query applies to all resources.
	 * @param  string  resource
	 * @param  string  privilege
	 * @return bool
	 */
	public function isAllowed($resource = IAuthorizator::ALL, $privilege = IAuthorizator::ALL)
	{
		$authorizator = new Authorizator;
		foreach ($this->roles as $role) {
			if ($authorizator->isAllowed((string) $role, $resource, $privilege)) {
				return TRUE;
			}
		}

		return FALSE;
	}

	// </editor-fold>	
}
