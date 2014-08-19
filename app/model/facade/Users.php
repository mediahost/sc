<?php

namespace App\Model\Facade;

use App\Model\Entity\User,
	App\Model\Entity,
	Kdyby\Doctrine\EntityDao;

class Users extends Base
{

	/** @var EntityDao */
	private $users;
	
	/** @var EntityDao */
	private $roles;

		
	protected function init()
	{
		$this->users = $this->em->getDao(User::getClassName());
		$this->roles = $this->em->getDao(Entity\Role::getClassName());
	}

	/**
	 * 
	 * @param type $email
	 * @return User
	 */
	public function findByEmail($email)
	{
		return $this->users->findOneBy(['email' => $email]);
	}

	/**
	 * Check if email is unique
	 * @param type $email
	 * @return bool
	 */
	public function isUnique($email)
	{
		return $this->findByEmail($email) === NULL;
	}

	/**
	 * Create user if isnt exists
	 * @param type $email
	 * @param type $password
	 * @return \App\Model\Entity\User|null
	 */
	public function create($email, $password, Role $role)
	{
		if ($this->findByEmail($email) === NULL) { // check unique
			$user = new User;
			$user->email = $email;
			
			$auth = new Entity\Auth();
			$auth->key = $email;
			$auth->source = 'app';
			$auth->hash = $password;
			
			$user->addRole($role);
			
			return $this->users->save($user);
		}
		return NULL;
	}

	
	/**
	 * 
	 * @param Entity\User $user
	 * @param Entity\Role|string $role
	 */
	public function addRole(Entity\User $user, $role)
	{
		if (!($user instanceof Entity\Role)) {
			if (is_string($role)) {
				$role = $this->roles->findOneBy(['name' => $role]);
			} elseif (is_array($role)) {
				$role = $this->roles->findBy(['name' => $role]);
			} else {
				throw new \InvalidArgumentException;
			}
		}
		
		return $user->addRole($role);
	}
}
