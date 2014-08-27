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
class Registration extends Base
{

	/** @var EntityDao */
	private $authDao;

	/** @var EntityDao */
	private $registrationDao;

	/** @var EntityDao */
	private $roleDao;

	/** @var EntityDao */
	private $userDao;


	protected function init()
	{
		$this->authDao = $this->em->getDao(Entity\Auth::getClassName());
		$this->registrationDao = $this->em->getDao(Entity\Registration::getClassName());
		$this->roleDao = $this->em->getDao(Entity\Role::getClassName());
		$this->userDao = $this->em->getDao(Entity\User::getClassName());
	}

	/**
	 * Find user by Auth key and source type.
	 * @param string $source Source name
	 * @param string $key Users's external identification
	 * @return Entity\User
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
	 * @param string $email
	 * @return User
	 */
	public function findByEmail($email)
	{
		return $this->userDao->findOneBy(['email' => $email]);
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

		$role = $this->roleDao->findOneBy(['name' => 'candidate']);
		$user->addRole($role);

		return $this->userDao->save($user);
	}

	/**
	 *
	 * @param Registration $registration
	 * @return Registration
	 */
	public function registerTemporarily(Entity\Registration $registration)
	{
		$registration->verification_code = \Nette\Utils\Strings::random(32);
		return $this->registrationDao->save($registration);
	}

	/**
	 * Creat new Auth and User (if doesn'e exist) by given code.
	 * @param string $code
	 * @return boolean
	 */
	public function verify($code)
	{
		$registration = $this->registrationDao->findOneBy(['verification_code' => $code]);

		if ($registration) {
			$auth = new Entity\Auth();
			$auth->key = $registration->key;
			$auth->source = $registration->source;
			$auth->token = $registration->token;
			$auth->hash = $registration->hash;
			
			if (!$user = $this->userDao->findOneBy(['email' => $registration->email])) {
				$user = new Entity\User();
				$user->email = $registration->email;
				$user->name = $registration->name;
				$return = $this->register($user, $auth);
			} else {
				$return = $this->merge($user, $auth);
			}
			
			$this->registrationDao->delete($registration);
			return $return;
		}

		return NULL;
	}

}
