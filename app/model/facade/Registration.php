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
		return $this->userDao->findOneBy([
					'auths.source' => $source,
					'auths.key' => $key
		]);
	}

	/**
	 * Update OAuth access token.
	 * @param string $key
	 * @param string $token
	 * @return Auth
	 */
	public function updateAccessToken($key, $token)
	{
		$auth = $this->authDao->findOneBy(['key' => $key]);
		$auth->token = $token;

		return $this->authDao->save($auth);
	}

	public function findByEmail($email)
	{
		return $this->userDao->findOneBy(['email' => $email]);
	}

	public function merge($user, $auth)
	{
		$user->addAuth($auth);
		return $this->userDao->save($user);
	}

	/**
	 *
	 * @param User $user
	 * @param Auth $auth
	 * @return User
	 */
	public function register(User $user, Auth $auth)
	{
		$user->addAuth($auth);

		$role = $this->roleDao->findOneBy(['name' => 'signed']);
		$user->addRole($role);

		return $user;
	}

	/**
	 *
	 * @param Registration $registration
	 * @return Registration
	 */
	public function temporarilyRegister(Entity\Registration $registration)
	{
		$registration->verification_code = Nette\Utils\Strings::random(32);
		return $this->registrationDao->save($registration);
	}

	/**
	 *
	 * @param type $code
	 * @return boolean
	 */
	public function verify($code)
	{
		$registration = $this->registrationDao->findOneBy(['verification_code' => $code]);

		if ($registration) {
			$user = $this->userDao->findOneBy(['email' => $registration->email]);

			if (!$user) {
				$user = new Entity\User();
				$user->email = $registration->email;
			}

			$auth = new Entity\Auth();
			$auth->key = $registration->key;
			$auth->source = $registration->source;
			$auth->token = $registration->token;
			$auth->hash = $registration->hash;
			$user->addAuth($auth);

			$user->addRole($this->roleDao->findBy(['name' => 'signed'])); // Vyměnit za register (role se musí zadávat na jednom místě)

			$this->em->remove($registration);
			$this->em->persist($user);

			$this->em->flush();

			return TRUE;
		}

		return FALSE;
	}

}
