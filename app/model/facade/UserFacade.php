<?php

namespace App\Model\Facade;

use App\Model\Entity;
use App\Model\Entity\User;
use Kdyby\Doctrine\EntityDao;

class UserFacade extends BaseFacade
{

	/** @var EntityDao */
	private $userDao;

	/** @var EntityDao */
	private $roleDao;

	/** @var EntityDao */
	private $authDao;

	/** @var EntityDao */
	private $signUpDao;

	/** @var \App\Model\Storage\UserSettingsStorage @inject */
	protected function init()
	{
		$this->userDao = $this->em->getDao(User::getClassName());
		$this->roleDao = $this->em->getDao(Entity\Role::getClassName());
		$this->authDao = $this->em->getDao(Entity\Auth::getClassName());
		$this->signUpDao = $this->em->getDao(Entity\SignUp::getClassName());
	}

	// <editor-fold defaultstate="collapsed" desc="create">

	/**
	 * Create User if isn't exists.
	 * @param string $mail
	 * @param string $password
	 * @return User
	 */
	public function create($mail, $password, Entity\Role $role)
	{
		if ($this->isUnique($mail)) {
			$user = new User;
			$user->mail = $mail;

			$auth = new Entity\Auth();
			$auth->key = $mail;
			$auth->source = Entity\Auth::SOURCE_APP;
			$auth->password = $password;

			$user->addRole($role);
			$user->addAuth($auth);
			$user->settings = new Entity\UserSettings();

			return $this->userDao->save($user);
		}

		return NULL;
	}

	// </editor-fold>
	// <editor-fold defaultstate="collapsed" desc="Find methods">

	/**
	 * @param string $mail
	 * @return User
	 */
	public function findByMail($mail)
	{
		return $this->userDao->findOneBy(['mail' => $mail]);
	}

	/**
	 * @param string $id
	 * @return User
	 */
	public function findByFacebookId($id)
	{
		return $this->userDao->findOneBy(['facebook.id' => $id]);
	}

	/**
	 * @param string $id
	 * @return User
	 */
	public function findByTwitterId($id)
	{
		return $this->userDao->findOneBy(['twitter.id' => $id]);
	}

	// </editor-fold>
	// <editor-fold defaultstate="collapsed" desc="setters">

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
	 * Sets recovery token and expiration datetime to User.
	 * @param User $user
	 * @return User
	 */
	public function setRecovery(User $user)
	{
		$user->setRecovery(\Nette\Utils\Strings::random(32), 'now + 1 hour');
		return $this->userDao->save($user);
	}

	// </editor-fold>
	// <editor-fold defaultstate="collapsed" desc="delete">

	public function delete(\Kdyby\Doctrine\Entities\BaseEntity $user)
	{
		$user->clearRoles();
		$this->userDao->save($user); // ToDo: Do all in one row.
		return $this->userDao->delete($user);
	}

	/**
	 * Delete all user data (Auth, User)
	 * @param int $id User ID.
	 * @return User
	 */
	public function hardDelete($id)
	{
		$user = $this->userDao->find($id);

		if ($user !== NULL) {
			$this->em->remove($user);
			$this->em->flush();
		}

		return $user;
	}

	// </editor-fold>

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

	/**
	 * @param string $mail
	 * @return bool
	 */
	public function isUnique($mail)
	{
		return $this->findByMail($mail) === NULL;
	}

	/**
	 * @param User $user
	 * @return User
	 */
	public function signUp(User $user)
	{
		$user->settings = new Entity\UserSettings();

		$this->em->persist($user);
		$this->em->flush();

		return $user;
	}

	/**
	 * @param Entity\SignUp $signUp
	 * @return Entity\SignUp
	 */
	public function signUpTemporarily(Entity\SignUp $signUp)
	{
		$qb = $this->em->createQueryBuilder();
		$qb->delete(Entity\SignUp::getClassName(), 's')
				->where('s.mail = ?1')
				->setParameter(1, $signUp->mail)
				->getQuery()
				->execute();

		$signUp->verificationToken = \Nette\Utils\Strings::random(32);
		$signUp->verificationExpiration = new \DateTime('now + 1 day');

		$this->em->persist($signUp);
		$this->em->flush();

		return $signUp;
	}

	/**
	 * @param string $token
	 * @return Entity\SignUp
	 */
	public function findByVerificationToken($token)
	{
		$signUp = $this->signUpDao->findOneBy(['verificationToken' => $token]);

		if ($signUp) {
			// Expired sign up request is deleted
			if ($signUp->verificationExpiration > new \DateTime()) {
				return $signUp;
			} else {
				$this->signUpDao->delete($signUp);
			}
		}

		return NULL;
	}

}
