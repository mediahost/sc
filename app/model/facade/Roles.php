<?php

namespace App\Model\Facade;

use Kdyby\Doctrine\EntityDao,
	App\Model\Entity;

class Roles extends Base
{

	/** @var EntityDao */
	private $roles;

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
		return $this->roles->fetchPairs();
	}

	/**
	 * 
	 * @param type $name
	 * @return \App\Model\Entity\User
	 */
	public function findByName($name)
	{
		return $this->roles->findOneBy(['name' => $name]);
	}

	/**
	 * Check if name is unique
	 * @param type $name
	 * @return bool
	 */
	public function isUnique($name)
	{
		return $this->findByName($name) === NULL;
	}

	/**
	 * Create role if isnt exists
	 * @param type $name
	 * @return Entity\Role|null
	 */
	public function create($name)
	{
		if ($this->isUnique($name)) { // check unique
			$entity = new Entity\Role;
			$entity->setName($name);
			return $this->roles->save($entity);
		}
		return NULL;
	}

}
