<?php

namespace App\Model\Facade;

use Kdyby\Doctrine\EntityDao,
	App\Model\Entity;

class AuthFacade extends BaseFacade
{

	/** @var EntityDao */
	private $authDao;
	
	/** @var EntityDao */
	private $userDao;

	protected function init()
	{
		$this->authDao = $this->em->getDao(Entity\Auth::getClassName());
		$this->userDao = $this->em->getDao(Entity\User::getClassName());
	}

	/**
	 * Find Auth by e-mail of application registration
	 * @param type $email
	 * @return type
	 */
	public function findByEmail($email)
	{
		return $this->authDao->findOneBy([
					'source' => Entity\Auth::SOURCE_APP,
					'key' => $email,
		]);
	}

	public function findByUser(Entity\User $user)
	{
		return $this->authDao->findBy([
					'user' => $user,
		]);
	}
	
	public function recovery(Entity\Auth $auth, $password)
	{
		$auth->hash = \Nette\Security\Passwords::hash($password);
		$user = $auth->user;
		$user->recovery = NULL;
		$user->recovery_expiration = NULL;
		$this->em->persist($auth);
		$this->em->persist($user);
		$this->em->flush();
		return $auth->user;
	}
	
	public function findByValidToken($token)
	{
		$auth = $this->authDao->findOneBy([
			'user.recovery' => $token
		]);
		
		if ($auth) {
			$user = $auth->user;
			
			if ($user->recovery_expiration > new \DateTime) {
				return $auth;
			} else {
				$user->recovery = NULL;
				$user->recovery_expiration = NULL;
				$this->userDao->save($user);
			}
		}
		
		return NULL;
	}

}


class AuthFacadeException extends \Exception
{}
