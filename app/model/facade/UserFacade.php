<?php

namespace App\Model\Facade;

use App\Model\Entity\Facebook;
use App\Model\Entity\Role;
use App\Model\Entity\SignUp;
use App\Model\Entity\Twitter;
use App\Model\Entity\User;
use App\Model\Entity\UserSettings;
use App\Model\Storage\SettingsStorage;
use DateTime;
use InvalidArgumentException;
use Kdyby\Doctrine\Entities\BaseEntity;
use Kdyby\Doctrine\EntityDao;
use Nette\Utils\Random;

/**
 * TODO: TEST IT!!!
 */
class UserFacade extends BaseFacade
{

	/** @var SettingsStorage @inject */
	public $settings;

	/** @var EntityDao */
	private $roleDao;

	/** @var EntityDao */
	private $signUpDao;

	/** @var EntityDao */
	private $userDao;

	protected function init()
	{
		$this->signUpDao = $this->em->getDao(SignUp::getClassName());
		$this->roleDao = $this->em->getDao(Role::getClassName());
		$this->userDao = $this->em->getDao(User::getClassName());
	}

	// <editor-fold defaultstate="collapsed" desc="create">

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

	/**
	 * Create user from registration and delete registration entity
	 * @param SignUp $registration
	 * @param Role $role
	 * @return User
	 */
	public function createFromRegistration(SignUp $registration, Role $role)
	{
		$user = new User;
		$user->setMail($registration->mail)
				->setHash($registration->hash)
				->setName($registration->mail)
				->addRole($role)
				->setRequiredRole($registration->role)
				->setSettings(new UserSettings());

		if ($registration->facebookId) {
			$user->facebook = (new Facebook)
					->setId($registration->facebookId)
					->setAccessToken($registration->facebookAccessToken);
		}
		if ($registration->twitterId) {
			$user->twitter = (new Twitter)
					->setId($registration->twitterId)
					->setAccessToken($registration->twitterAccessToken);
		}

		$this->signUpDao->delete($registration);

		return $this->userDao->save($user);
	}

	/**
	 * Create registration
	 * @param User $user
	 * @return SignUp
	 */
	public function createRegistration(User $user)
	{
		$this->clearRegistrations($user->mail);

		$signUp = new SignUp;
		$signUp->setMail($user->mail)
				->setHash($user->hash)
				->setName($user->name)
				->setRole($this->roleDao->find($user->requiredRole->id));

		if ($user->facebook) {
			$signUp->setFacebookId($user->facebook->id)
					->setFacebookAccessToken($user->facebook->accessToken);
		}

		if ($user->twitter) {
			$signUp->setTwitterId($user->twitter->id)
					->setTwitterAccessToken($user->twitter->accessToken);
		}

		$signUp->verificationToken = Random::generate(32);
		$signUp->verificationExpiration = new DateTime('now + ' . $this->settings->expiration->verification);
		
		$this->em->persist($signUp);
		$this->em->flush();

		return $signUp;
	}

	/**
	 * Clear registrations by mail
	 * @param string $mail
	 * @return mixed
	 */
	private function clearRegistrations($mail)
	{
		$qb = $this->em->createQueryBuilder();
		return $qb->delete(SignUp::getClassName(), 's')
						->where('s.mail = ?1')
						->setParameter(1, $mail)
						->getQuery()
						->execute();
	}

	// </editor-fold>
	// <editor-fold defaultstate="collapsed" desc="find methods">

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

	/**
	 * Find only valid entities
	 * Expired sign up request is deleted
	 * @param string $token
	 * @return SignUp
	 */
	public function findByVerificationToken($token)
	{
		$signUp = $this->signUpDao->findOneBy(['verificationToken' => $token]);

		if ($signUp) {
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

	// </editor-fold>
	// <editor-fold defaultstate="collapsed" desc="setters">

	/**
	 * Sets recovery token and expiration datetime to User.
	 * @param User $user
	 * @return User
	 */
	public function setRecovery(User $user)
	{
		$user->setRecovery(Random::generate(32), 'now + ' . $this->settings->expiration->recovery);
		return $this->userDao->save($user);
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
	 * @deprecated
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
	 * @deprecated
	 */
	public function signUp(User $user)
	{
		$user->settings = new UserSettings();

		$this->em->persist($user);
		$this->em->flush();

		return $user;
	}

}
