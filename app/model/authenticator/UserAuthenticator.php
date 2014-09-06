<?php

namespace App\Model\Authenticator;

use Nette;
use App\Model\Facade\AuthFacade;
use Kdyby\Doctrine\EntityManager;
use Kdyby\Doctrine\EntityDao;

/**
 * Users Authenticator.
 */
class UserAuthenticator extends Nette\Object implements Nette\Security\IAuthenticator
{

	/** @var AuthFacade */
	private $authFacade;
	
	/** @var EntityManager */
	private $em;
	
	/** @var EntityDao */
	private $userDao;


	public function __construct(AuthFacade $authFacade, EntityManager $em)
	{
		$this->authFacade = $authFacade;
		$this->em = $em;
		$this->userDao = $this->em->getDao(\App\Model\Entity\User::getClassName());
	}

	/**
	 * Performs an authentication.
	 * @return Nette\Security\Identity
	 * @throws Nette\Security\AuthenticationException
	 */
	public function authenticate(array $credentials)
	{
		list($mail, $password) = $credentials;

		$auth = $this->authFacade->findByMail($mail);
		
		if (!$auth) {
			throw new Nette\Security\AuthenticationException('The username is incorrect.', self::IDENTITY_NOT_FOUND);
		} elseif (!\Nette\Security\Passwords::verify($password, $auth->hash)) { // ToDo: Tohle by mělo být v uivateli, v auth nebo v Nette
			throw new Nette\Security\AuthenticationException('The password is incorrect.', self::INVALID_CREDENTIAL);
		} elseif (\Nette\Security\Passwords::needsRehash($auth->hash)) {
			$auth->hash = \Nette\Security\Passwords::hash($password);
			$this->authFacade->save($auth);
		}
		
		$user = $auth->user;
		
		// Remove recovery data if exists
		if ($user->recoveryToken !== NULL) {
			$user->unsetRecovery();
			$this->userDao->save($user);
		}

		$arr = $user->toArray();
		return new Nette\Security\Identity($user->getId(), $user->getRolesPairs(), $arr);
	}

}
