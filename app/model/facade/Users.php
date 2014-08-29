<?php

namespace App\Model\Facade;

use App\Model\Entity\User,
	App\Model\Entity,
	Kdyby\Doctrine\EntityDao,
	Tracy\Debugger as Debug;

class Users extends Base
{

	/** @var EntityDao */
	private $users;

	/** @var EntityDao */
	private $roles;
	
	/** @var EntityDao */
	private $auths;

	protected function init()
	{
		$this->users = $this->em->getDao(User::getClassName());
		$this->roles = $this->em->getDao(Entity\Role::getClassName());
		$this->auths = $this->em->getDao(Entity\Auth::getClassName());
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
	public function create($email, $password, Entity\Role $role)
	{
		if ($this->findByEmail($email) === NULL) { // check unique
			$user = new User;
			$user->email = $email;

			$auth = new Entity\Auth();
			$auth->key = $email;
			$auth->source = Entity\Auth::SOURCE_APP;
			$auth->hash = $password;

			$user->addRole($role);
			$user->addAuth($auth);

			return $this->users->save($user);
		}
		return NULL;
	}
	
	/**
	 * Hledá aplikační autorizaci odpovídající mailu.
	 * V případě její absence tuto autorizaci vytvoří se zadaným heslem
	 * @param User $user
	 * @param type $password
	 */
	public function setAppPassword(User $user, $password)
	{
		$auth = $this->auths->findOneBy([
					'source' => Entity\Auth::SOURCE_APP,
					'key' => $user->email,
					'user' => $user,
		]);
		if (!$auth) {
			$auth = new Entity\Auth;
			$auth->setUser($user);
			$auth->setSource(Entity\Auth::SOURCE_APP);
			$auth->setKey($user->email);
		}
		$auth->setPassword($password);
		$this->auths->save($auth);
	}

	/**
	 * Add role as Role entity, string or array of entites to user.
	 * @param User $user
	 * @param Entity\Role|string $role
	 */
	public function addRole(User $user, $role)
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
	
	public function delete(User $user)
	{
		$user->clearRoles();
		// TODO: delete auth
		$this->users->save($user);
		return $this->users->delete($user);
	}

}
