<?php

namespace App\Model\Facade;

use Kdyby\Doctrine\EntityDao,
	App\Model\Entity\Auth,
	App\Model\Entity\User,
	App\Model\Entity,
	App\Model\Entity\Role;

/**
 * Description of Registration
 *
 * @author Martin Šifra <me@martinsifra.cz>
 */
class RegistrationFacade extends BaseFacade
{

	/** @var EntityDao */
	private $authDao;

	/** @var EntityDao */
	private $registrationDao;

	/** @var EntityDao */
	private $roleDao;

	/** @var EntityDao */
	private $userDao;
	
	/** @var UserFacade @inject */
	private $userFacade;
	
	/** @var RoleFacade @inject */
	private $roleFacade;
	
	
	protected function init()
	{
		$this->authDao = $this->em->getDao(Entity\Auth::getClassName());
		$this->registrationDao = $this->em->getDao(Entity\Registration::getClassName());
		$this->roleDao = $this->em->getDao(Entity\Role::getClassName());
		$this->userDao = $this->em->getDao(Entity\User::getClassName());
	}

	/**
	 * Find Uuth by key and source type.
	 * @param string $source Source name.
	 * @param string $key User's external identification key.
	 * @return Entity\Auth
	 */
	public function findByKey($source, $key)
	{
		return $this->authDao->findOneBy([
					'source' => $source,
					'key' => $key
		]);
	}

	/**
	 * Update OAuth access token.
	 * @param Auth $auth
	 * @param string $token
	 * @return Auth
	 */
	public function updateAccessToken(Auth $auth, $token)
	{
		$auth->token = $token;
		return $this->authDao->save($auth);
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
	 * Add new Auth to existing User and save.
	 * @param User $user
	 * @param Auth $auth
	 * @return User
	 */
	public function merge(User $user, Auth $auth)
	{
		$user->addAuth($auth);
		return $this->userDao->save($user);
	}

	/**
	 * Connect Auth with User and save new entities.
	 * @param User $user
	 * @param Auth $auth
	 * @return User
	 */
	public function register(User $user, Auth $auth)
	{
		$user->addAuth($auth);

		$role = $this->roleDao->findOneBy(['name' => Role::ROLE_CANDIDATE]);
		$user->addRole($role);

		return $this->userDao->save($user);
	}

	/**
	 * Create new temporarily registration and delete old one with same e-mail
	 * and source.
	 * @param RegistrationFacade $registration
	 * @return Entity\Registration
	 */
	public function registerTemporarily(Entity\Registration $registration)
	{
		$previous = $this->registrationDao->findBy([
				'mail' => $registration->mail,
				'source' => $registration->source
		]);

		foreach ($previous as $entity) {
			$this->em->remove($entity);
		}
		
		$registration->verificationToken = \Nette\Utils\Strings::random(32);
		$registration->verificationExpiration = new \DateTime('now + 1 day');
		$this->em->persist($registration);

		$this->em->flush();
		return $registration;
	}

	/**
	 * Find registration by valid verification tooken.
	 * @param string $token
	 * @return Entity\Registration
	 */
	public function findByVerificationToken($token)
	{
		$registration = $this->registrationDao->findOneBy([
			'verificationToken' => $token
		]);

		if ($registration) {
			// Expired registrations requests are deleted
			if ($registration->verificationExpiration > new \DateTime()) {
				return $registration;
			} else {
				$this->registrationDao->delete($registration);
			}
		}

		return NULL;
	}

	/**
	 * Creat new Auth and User (if doesn'e exist) by given code.
	 * @param string $token
	 * @return User
	 */
	public function verify($token) // ToDo: Předělat do transakcí, pokud je to možné.
	{
		$registration = $this->findByVerificationToken($token);

		if ($registration) {
			$auth = new Entity\Auth();
			$auth->key = $registration->key;
			$auth->source = $registration->source;
			$auth->token = $registration->token;
			$auth->hash = $registration->hash;

			if (!$user = $this->userDao->findOneBy(['mail' => $registration->mail])) {
				$user = new Entity\User();
				$user->mail = $registration->mail;
				$user->name = $registration->name;
				$return = $this->register($user, $auth); // ToDo: Tohle by nemělo volat save() ale pouze persist()
			} else {
				$return = $this->merge($user, $auth); // ToDo: Tohle by nemělo volat save() ale pouze persist()
			}

			$this->registrationDao->delete($registration);  // ToDo: Tohle by nemělo volat save() ale pouze persist()
//			$this->em->remove($registration); // Transakce
			
			return $return;
		}

		return NULL;
	}
	
	/**
	 * 
	 * @param type $user
	 * @param type $auth
	 * @return type
	 */
	public function mergeOrRegister($user, $auth)
	{
		if (!$user = $this->userFacade->findByMail($user->mail)) {
			// Registrace
			$role = $this->roleFacade->findByName(Role::ROLE_CANDIDATE);
			$user->addRole($role);
		}
		
		$user->addAuth($auth);
		$this->em->persist($user);
	}
}
