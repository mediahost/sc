<?php

namespace App\Model\Facade;

use App\Model\Entity\User,
	App\Model\Entity,
	Kdyby\Doctrine\EntityDao,
	Tracy\Debugger as Debug;

class UserFacade extends BaseFacade
{

	/** @var EntityDao */
	private $userDao;

	/** @var EntityDao */
	private $roleDao;
	
	/** @var EntityDao */
	private $authDao;

	protected function init()
	{
		$this->userDao = $this->em->getDao(User::getClassName());
		$this->roleDao = $this->em->getDao(Entity\Role::getClassName());
		$this->authDao = $this->em->getDao(Entity\Auth::getClassName());
	}

	/**
	 * 
	 * @param type $mail
	 * @return User
	 */
	public function findByMail($mail)
	{
		return $this->userDao->findOneBy(['mail' => $mail]);
	}

	/**
	 * Check if mail is unique
	 * @param type $mail
	 * @return bool
	 */
	public function isUnique($mail)
	{
		return $this->findByMail($mail) === NULL;
	}

	/**
	 * Create user if isnt exists
	 * @param type $mail
	 * @param type $password
	 * @return \App\Model\Entity\User|null
	 */
	public function create($mail, $password, Entity\Role $role)
	{
		if ($this->findByMail($mail) === NULL) { // check unique
			$user = new User;
			$user->mail = $mail;

			$auth = new Entity\Auth();
			$auth->key = $mail;
			$auth->source = Entity\Auth::SOURCE_APP;
			$auth->password = $password;

			$user->addRole($role);
			$user->addAuth($auth);

			return $this->userDao->save($user);
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
		$auth = $this->authDao->findOneBy([
					'source' => Entity\Auth::SOURCE_APP,
					'key' => $user->mail,
					'user' => $user,
		]);
		if (!$auth) {
			$auth = new Entity\Auth;
			$auth->setUser($user);
			$auth->setSource(Entity\Auth::SOURCE_APP);
			$auth->setKey($user->mail);
		}
		$auth->setPassword($password);
		$this->authDao->save($auth);
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
				$role = $this->roleDao->findOneBy(['name' => $role]);
			} elseif (is_array($role)) {
				$role = $this->roleDao->findBy(['name' => $role]);
			} else {
				throw new \InvalidArgumentException;
			}
		}

		return $user->addRole($role);
	}
	
	public function delete(\Kdyby\Doctrine\Entities\BaseEntity $user)
	{
		$user->clearRoles();
		$this->userDao->save($user); // ToDo: Do all in one row.
		return $this->userDao->delete($user);
	}
	
	/**
	 * 
	 * @param User $user
	 * @return User
	 */
	public function forgotten(User $user)
	{
		$user->setRecovery(\Nette\Utils\Strings::random(32), '1 hour');
		return $this->userDao->save($user);
	}
}
