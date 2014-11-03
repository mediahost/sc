<?php

namespace App\Model\Facade;

use App\Model\Entity\Role;
use App\Model\Entity\SignUp;
use App\Model\Entity\User;
use App\Model\Entity\UserSettings;
use App\Model\Storage\UserSettingsStorage;
use DateTime;
use InvalidArgumentException;
use Kdyby\Doctrine\Entities\BaseEntity;
use Kdyby\Doctrine\EntityDao;
use Nette\Utils\Random;
use Nette\Utils\Strings;

class UserFacade extends BaseFacade
{
	/** @var EntityDao */
	private $roleDao;
	
	/** @var EntityDao */
	private $signUpDao;
	
	/** @var EntityDao */
	private $userDao;

	/** @var UserSettingsStorage @inject */
	protected function init()
	{
		$this->signUpDao = $this->em->getDao(SignUp::getClassName());
		$this->roleDao = $this->em->getDao(Role::getClassName());
		$this->userDao = $this->em->getDao(User::getClassName());
		
	}

	/**
	 * @param string $mail
	 * @param string $password
	 * @param Role $role
	 * @return User
	 */
	public function create($mail, $password, Role $role)
	{
		if ($this->isUnique($mail)) {
			$user = new User;
			$user->setMail($mail)
					->setPassword($password)
					->addRole($role)
					->setSettings(new UserSettings());

			return $this->userDao->save($user);
		}

		return NULL;
	}

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
	 * Sets recovery token and expiration datetime to User.
	 * @param User $user
	 * @return User
	 */
	public function setRecovery(User $user)
	{
		$user->setRecovery(Random::generate(32), 'now + 1 hour');
		return $this->userDao->save($user);
	}

	// </editor-fold>
	// <editor-fold defaultstate="collapsed" desc="delete">

	public function delete(BaseEntity $user)
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
	 * @param Role|string $role
	 */
	public function addRole(User $user, $role)
	{
		if (!($user instanceof Role)) {
			if (is_string($role)) {
				$role = $this->roleDao->findOneBy(['name' => $role]);
			} elseif (is_array($role)) {
				$role = $this->roleDao->findBy(['name' => $role]);
			} else {
				throw new InvalidArgumentException;
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
		$user->settings = new UserSettings();

		$this->em->persist($user);
		$this->em->flush();

		return $user;
	}

	/**
	 * @param SignUp $signUp
	 * @return SignUp
	 */
	public function signUpTemporarily(SignUp $signUp)
	{
		$qb = $this->em->createQueryBuilder();
		$qb->delete(SignUp::getClassName(), 's')
				->where('s.mail = ?1')
				->setParameter(1, $signUp->mail)
				->getQuery()
				->execute();

		$signUp->verificationToken = Strings::random(32);
		$signUp->verificationExpiration = new DateTime('now + 1 day');

		$this->em->persist($signUp);
		$this->em->flush();

		return $signUp;
	}

	/**
	 * @param string $token
	 * @return SignUp
	 */
	public function findByVerificationToken($token)
	{
		$signUp = $this->signUpDao->findOneBy(['verificationToken' => $token]);

		if ($signUp) {
			// Expired sign up request is deleted
			if ($signUp->verificationExpiration > new DateTime()) {
				return $signUp;
			} else {
				$this->signUpDao->delete($signUp);
			}
		}

		return NULL;
	}

	/**
	 * @param string $token
	 * @return User
	 */
	public function findByRecoveryToken($token)
	{
		if (!empty($token)) {
			$user = $this->userDao->findOneBy([
				'recoveryToken' => $token
			]);

			if ($user) {
				if ($user->recoveryExpiration > new DateTime) {
					return $user;
				} else {
					$user->removeRecovery();
					$this->userDao->save($user);
				}
			}
		}
			
		return NULL;
	}
	
	/**
	 * @param User $user
	 * @param string $password
	 * @return User
	 */
	public function recoveryPassword(User $user, $password)
	{
		$user->password = $password;
		$user->removeRecovery();
		return $this->userDao->save($user);
	}

	/**
	 * @param User $user
	 * @param string $token
	 * @return User
	 */
	public function verify($user, $token)
	{
		$qb = $this->em->createQueryBuilder();
		$qb->delete(SignUp::getClassName(), 's')
				->where('s.verificationToken = ?1')
				->setParameter(1, $token)
				->getQuery()
				->execute();
		
		return $this->userDao->save($user);
	}

}
