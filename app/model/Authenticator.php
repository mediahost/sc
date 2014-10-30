<?php

namespace App\Model;

use App\Model\Facade\UserFacade;
use Kdyby\Doctrine\EntityManager;
use Nette\Security\AuthenticationException;
use Nette\Security\IAuthenticator;
use Nette\Security\Identity;



class Authenticator implements IAuthenticator
{

	/** @var UserFacade @inject */
	public $userFacade;

	/** @var EntityManager @inject */
	public $em;

	/**
	 * @return Identity
	 * @throws AuthenticationException
	 */
	public function authenticate(array $credentials)
	{
		list($email, $password) = $credentials;

		$user = $this->userFacade->findByMail($email);

		if (!$user) {
			throw new AuthenticationException('Username is incorrect.', self::IDENTITY_NOT_FOUND);
		} elseif (!$user->verifyPassword($password)) {
			throw new AuthenticationException('Password is incorrect.', self::INVALID_CREDENTIAL);
		} elseif ($user->needsRehash()) {
			$this->em->persist($user);
		}

		// Remove recovery data if exists
		if ($user->recoveryToken !== NULL) {
			$user->removeRecovery();
			$this->em->persist($user);
		}

		$this->em->flush();
		return new Identity($user->id, $user->getRolesPairs(), $user->toArray());
	}

}
