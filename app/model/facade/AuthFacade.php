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
	 * Find Auth of application registration by e-mail.
	 * @param string $mail
	 * @return Entity\Auth
	 */
	public function findByMail($mail)
	{
		return $this->authDao->findOneBy([
					'source' => Entity\Auth::SOURCE_APP,
					'key' => $mail,
		]);
	}

	/**
	 * Find Auth by User.
	 * @param Entity\User $user
	 * @return Entity\Auth
	 */
	public function findByUser(Entity\User $user)
	{
		return $this->authDao->findBy([
					'user' => $user,
		]);
	}
	
	/**
	 * Set new password to User by Auth.
	 * @param Entity\Auth $auth
	 * @param string $password
	 * @return Entity\User
	 */
	public function recovery(Entity\Auth $auth, $password)
	{
		$auth->password = $password;
		$this->em->persist($auth);
		
		$user = $auth->user;
		$user->setRecovery();
		$this->em->persist($user);
		
		$this->em->flush();
		return $auth->user;
	}
	
	/**
	 * Find application Auth by valid token.
	 * @param string $token
	 * @return Entity\Auth
	 */
	public function findByValidToken($token)
	{
		$auth = $this->authDao->findOneBy([
			'user.recoveryToken' => $token,
			'source' => Entity\Auth::SOURCE_APP
		]);
		
		if ($auth) {
			$user = $auth->user;
			
			if ($user->recoveryExpiration > new \DateTime) {
				return $auth;
			} else {
				$user->setRecovery();
				$this->userDao->save($user);
			}
		}
		
		return NULL;
	}
	
	/**
	 * Is Auth unique?
	 * @param string $key
	 * @param string $source
	 * @return bool
	 */
	public function isUnique($key, $source)
	{
		return $this->authDao->findOneBy([
					'source' => $source,
					'key' => $key,
		]) === NULL;
	}
}

class AuthFacadeException extends \Exception
{}
