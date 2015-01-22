<?php

namespace App\Model\Facade;

use App\Extensions\Settings\Model\Service\ExpirationService;
use App\Model\Entity\CompanyPermission;
use App\Model\Entity\Facebook;
use App\Model\Entity\PageConfigSettings;
use App\Model\Entity\PageDesignSettings;
use App\Model\Entity\Registration;
use App\Model\Entity\Role;
use App\Model\Entity\Twitter;
use App\Model\Entity\User;
use DateTime;
use InvalidArgumentException;
use Kdyby\Doctrine\EntityDao;
use Kdyby\Doctrine\EntityManager;
use Nette\Object;
use Nette\Utils\Random;

/**
 * UserFacade
 */
class UserFacade extends Object
{

	/** @var EntityManager @inject */
	public $em;

	/** @var ExpirationService @inject */
	public $expirationService;

	/** @var CompanyFacade @inject */
	public $companyFacade;

	/** @var EntityDao */
	private $userDao;

	/** @var EntityDao */
	private $roleDao;

	/** @var EntityDao */
	private $registrationDao;

	/** @var EntityDao */
	private $configSettingsDao;

	/** @var EntityDao */
	private $designSettingsDao;

	public function __construct(EntityManager $em, ExpirationService $expiration)
	{
		$this->expirationService = $expiration;
		$this->em = $em;
		$this->userDao = $this->em->getDao(User::getClassName());
		$this->roleDao = $this->em->getDao(Role::getClassName());
		$this->registrationDao = $this->em->getDao(Registration::getClassName());
		$this->configSettingsDao = $this->em->getDao(PageConfigSettings::getClassName());
		$this->designSettingsDao = $this->em->getDao(PageDesignSettings::getClassName());
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
					->addRole($role);

			return $this->userDao->save($user);
		}
		return NULL;
	}

	/**
	 * Create user from registration and delete registration entity
	 * @param Registration $registration
	 * @param Role $role
	 * @return User
	 */
	public function createFromRegistration(Registration $registration, Role $role)
	{
		$user = new User;
		$user->setMail($registration->mail)
				->setHash($registration->hash)
				->addRole($role)
				->setRequiredRole($registration->role);

		if ($registration->facebookId) {
			$user->facebook = new Facebook($registration->facebookId);
			$user->facebook->setAccessToken($registration->facebookAccessToken);
		}
		if ($registration->twitterId) {
			$user->twitter = new Twitter($registration->twitterId);
			$user->twitter->setAccessToken($registration->twitterAccessToken);
		}

		$this->registrationDao->delete($registration);

		return $this->userDao->save($user);
	}

	/**
	 * Create registration
	 * @param User $user
	 * @return Registration
	 */
	public function createRegistration(User $user)
	{
		$this->clearRegistrations($user->mail);

		$registration = new Registration;
		$registration->setMail($user->mail)
				->setHash($user->hash)
				->setRole($this->roleDao->find($user->requiredRole->id));

		if ($user->facebook) {
			$registration->setFacebookId($user->facebook->id)
					->setFacebookAccessToken($user->facebook->accessToken);
		}

		if ($user->twitter) {
			$registration->setTwitterId($user->twitter->id)
					->setTwitterAccessToken($user->twitter->accessToken);
		}

		$registration->verificationToken = Random::generate(32);
		$registration->verificationExpiration = new DateTime('now + ' . $this->expirationService->verification);

		$this->registrationDao->save($registration);

		return $registration;
	}

	/**
	 * Clear registrations by mail
	 * @param string $mail
	 * @return mixed
	 */
	private function clearRegistrations($mail)
	{
		$qb = $this->em->createQueryBuilder();
		return $qb->delete(Registration::getClassName(), 'r')
						->where('r.mail = ?1')
						->setParameter(1, $mail)
						->getQuery()
						->execute();
	}

	// </editor-fold>
	// <editor-fold defaultstate="expanded" desc="getters">

	/**
	 * Get all users
	 * @return array
	 */
	public function getUsers()
	{
		return $this->userDao->findPairs([], 'mail', [], 'id');
	}

	/**
	 * Get all users in inserted role
	 * @return array
	 */
	public function getUsersInRole(Role $role)
	{
		$qb = $this->em->createQueryBuilder();
		$query = $qb->select('u.mail', 'u.id')
				->from(User::getClassName(), 'u', 'u.id')
				->innerJoin('u.roles', 'r')
				->where('r.id = :roleid')
				->setParameter('roleid', $role->id)
				->getQuery();

		return array_map(function ($row) {
			return reset($row);
		}, $query->getArrayResult());
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
	 * @return Registration
	 */
	public function findByVerificationToken($token)
	{
		$registration = $this->registrationDao->findOneBy(['verificationToken' => $token]);

		if ($registration) {
			if ($registration->verificationExpiration > new DateTime()) {
				return $registration;
			} else {
				$this->registrationDao->delete($registration);
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
	 */
	public function setRecovery(User &$user)
	{
		$user->setRecovery(Random::generate(32), 'now + ' . $this->expirationService->recovery);
	}

	/**
	 * @param User $user
	 * @param string $password
	 */
	public function recoveryPassword(User &$user, $password)
	{
		$user->password = $password;
		$user->removeRecovery();
	}

	/**
	 * Add role as Role entity, string or array of entites to user.
	 * @param User $user
	 * @param Role|string $role
	 * @throws InvalidArgumentException
	 */
	public function addRole(User $user, $role)
	{
		if (is_string($role)) {
			return $user->addRole($this->roleDao->findOneBy(['name' => $role]));
		} elseif (is_array($role)) {
			return $user->addRoles($this->roleDao->findBy(['name' => $role]));
		} else {
			throw new InvalidArgumentException;
		}
	}

	/**
	 * Append settings to user
	 * @param type $userId
	 * @param PageConfigSettings $configSettings
	 * @param PageDesignSettings $designSettings
	 */
	public function appendSettings($userId, PageConfigSettings $configSettings = NULL, PageDesignSettings $designSettings = NULL)
	{
		$user = $this->userDao->find($userId);
		if ($user && $configSettings) {
			if (!$user->pageConfigSettings instanceof PageConfigSettings) {
				$user->pageConfigSettings = new PageConfigSettings;
			}
			$user->pageConfigSettings->append($configSettings);
			$this->configSettingsDao->save($user->pageConfigSettings);
		}
		if ($user && $designSettings) {
			if (!$user->pageDesignSettings instanceof PageDesignSettings) {
				$user->pageDesignSettings = new PageDesignSettings;
			}
			$user->pageDesignSettings->append($designSettings);
			$this->designSettingsDao->save($user->pageDesignSettings);
		}
	}

	// </editor-fold>
	// <editor-fold defaultstate="collapsed" desc="delete">

	/**
	 * Delete user by id
	 * @param int $id User ID
	 * @return bool
	 */
	public function deleteById($id)
	{
		$user = $this->userDao->find($id);
		return $this->delete($user);
	}

	/**
	 * TODO: TEST IT! / all delete
	 * Delete user or throw exception
	 * @param User $user
	 * @return type
	 */
	public function delete(User $user)
	{
		if ($this->isLastAdminForSomeCompany($user)) {
			throw new CantDeleteUserException('You\'re only one admin');
		}
		return $this->userDao->delete($user);
	}

	/**
	 * If some user is last admin for some company then return TRUE
	 * @param User $user
	 * @return boolean
	 */
	private function isLastAdminForSomeCompany(User $user)
	{
		$permissions = $this->companyFacade->findPermissions(NULL, $user);
		foreach ($permissions as $permission) {
			if (count($permission->company->adminAccesses) === 1 &&
					$user->id === $permission->company->adminAccesses->first()->user->id) {
				return TRUE;
			}
		}
		return FALSE;
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

}

class CantDeleteUserException extends \Exception
{
	
}
