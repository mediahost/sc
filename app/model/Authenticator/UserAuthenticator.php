<?php

namespace App\Model\Authenticator;

use Nette;

/**
 * Users Authenticator.
 */
class UserAuthenticator extends Nette\Object implements Nette\Security\IAuthenticator
{

	/** @var \App\Model\Facade\Auths */
	private $auths;

	public function __construct(\App\Model\Facade\Auths $auths)
	{
		$this->auths = $auths;
	}

	/**
	 * Performs an authentication.
	 * @return Nette\Security\Identity
	 * @throws Nette\Security\AuthenticationException
	 */
	public function authenticate(array $credentials)
	{
		list($email, $password) = $credentials;

		$auth = $this->auths->findByEmail($email);

		if (!$auth) {
			throw new Nette\Security\AuthenticationException('The username is incorrect.', self::IDENTITY_NOT_FOUND);
		} elseif (!\Nette\Security\Passwords::verify($password, $auth->hash)) { // ToDo: Tohle by mělo být v uivateli, v auth nebo v Nette
			throw new Nette\Security\AuthenticationException('The password is incorrect.', self::INVALID_CREDENTIAL);
		} elseif (\Nette\Security\Passwords::needsRehash($auth->hash)) {
			$auth->hash = \Nette\Security\Passwords::hash($password);
			$this->auths->save($auth);
		}

		$user = $auth->user;

		$arr = $user->toArray();
		return new Nette\Security\Identity($user->getId(), $user->getRolesPairs(), $arr);
	}

}
