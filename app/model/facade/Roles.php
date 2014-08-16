<?php

namespace App\Model\Facade;

use Kdyby\Doctrine\EntityDao,
	App\Model\Entity;

class Roles extends Base
{
	
	/** @var EntityDao */
	private $roles;

	private function init()
	{
		$this->roles = $this->em->getDao(Entity\Role::getClassName());
	}

	
	/**
     * 
     * @param type $name
     * @return \App\Model\Entity\User
     */
    public function findByName($name)
    {
        return $this->roles->findOneBy(array('name' => $name));
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
     * @return \App\Model\Entity\User\Role|null
     */
    public function create($name)
    {
        if ($this->isUnique($name)) { // check unique
            $entity = new \App\Model\Entity\User\Role;
            $entity->setName($name);
            return $this->roles->save($entity);
        }
        return NULL;
    }

}
