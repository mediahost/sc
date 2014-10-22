<?php

namespace App\Model\Facade;

use Kdyby\Doctrine\EntityDao,
	App\Model\Entity;

class RoleFacade extends BaseFacade
{
	// <editor-fold defaultstate="collapsed" desc="constants & variables">

	/** @var EntityDao */
	private $roles;

	/** @var array */
	private $registratable = [Entity\Role::ROLE_CANDIDATE, Entity\Role::ROLE_COMPANY]; // ToDo: Move to configuration.

	// </editor-fold>

	protected function init()
	{
		$this->roles = $this->em->getDao(Entity\Role::getClassName());
	}

	// <editor-fold defaultstate="collapsed" desc="create">

	/**
	 * Create role if is not exists.
	 * @param type $name
	 * @return Entity\Role|null
	 */
	public function create($name)
	{
		if ($this->isUnique($name)) {
			$entity = new Entity\Role;
			$entity->setName($name);
			return $this->roles->save($entity);
		}
		return NULL;
	}

	// </editor-fold>
	// <editor-fold defaultstate="collapsed" desc="getters">

	/**
	 * Get all roles
	 * @return array
	 */
	public function getRoles()
	{
		return $this->roles->findPairs([], 'name', [], 'id');
	}

	// </editor-fold>
	// <editor-fold defaultstate="collapsed" desc="finders">

	/**
	 * Find role by name.
	 * @param type $name
	 * @return Entity\User
	 */
	public function findByName($name)
	{
		return $this->roles->findOneBy(['name' => $name]);
	}

	// </editor-fold>
	// <editor-fold defaultstate="collapsed" desc="checkers">

	/**
	 * Check if name is unique.
	 * @param type $name
	 * @return bool
	 */
	public function isUnique($name)
	{
		return $this->findByName($name) === NULL;
	}

	/**
	 * Check if role is allowed to register.
	 * @param string $roleName
	 */
	public function isRegistratable($roleName)
	{
		$role = $this->findByName($roleName);

		if ($role !== NULL && in_array($role->name, $this->registratable)) {
			return $role;
		}

		return FALSE;
	}

	// </editor-fold>
}
