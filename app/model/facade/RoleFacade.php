<?php

namespace App\Model\Facade;

use Kdyby\Doctrine\EntityDao,
	App\Model\Entity;

class RoleFacade extends BaseFacade
{

	/** @var EntityDao */
	private $roles;

	/** @var array */
	private $registratable = [Entity\Role::ROLE_CANDIDATE, Entity\Role::ROLE_COMPANY_ADMIN]; // ToDo: Move to configuration.

	protected function init()
	{
		$this->roles = $this->em->getDao(Entity\Role::getClassName());
	}

	/**
	 * Get all roles
	 * @return array
	 */
	public function getRoles()
	{
		return $this->roles->findPairs([], 'name', [], 'id');
	}

	/**
	 * Find role by name.
	 * @param type $name
	 * @return Entity\User
	 */
	public function findByName($name)
	{
		return $this->roles->findOneBy(['name' => $name]);
	}

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

}
