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
	 * Find user by e-mail.
	 * @param string $mail
	 * @return User
	 */
	public function findByMail($mail)
	{
		return $this->userDao->findOneBy(['mail' => $mail]);
	}

	/**
	 * Is User unique by e-mail?
	 * @param string $mail
	 * @return bool
	 */
	public function isUnique($mail)
	{
		return $this->findByMail($mail) === NULL;
	}

	/**
	 * Create User if isn't exists.
	 * @param string $mail
	 * @param string $password
	 * @return User
	 */
	public function create($mail, $password, Entity\Role $role)
	{
		if ($this->isUnique($mail)) { // check unique
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
	 * Finds application Auth corresponding to e-mail.
	 * If missing creates new Auth with set password.
	 * @param User $user
	 * @param string $password
	 * @return Entity\Auth
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
	 * Sets recovery token and expiration datetime to User.
	 * @param User $user
	 * @return User
	 */
	public function setRecovery(User $user)
	{
		$user->setRecovery(\Nette\Utils\Strings::random(32), 'now + 1 hour');
		return $this->userDao->save($user);
	}
}
