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
	 * Find Auth of application account by e-mail.
	 * @param string $mail E-mail address in Auth (not in User).
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
	 * Find Auth by key and source type.
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
	 * Find application Auth by valid token.
	 * @param string $token
	 * @return Entity\Auth
	 */
	public function findByRecoveryToken($token)
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
				$user->unsetRecovery();
				$this->userDao->save($user);
			}
		}
		
		return NULL;
	}
	
	/**
	 * Find Auth by User.
	 * 
	 * @param Entity\User $user
	 * @return Entity\Auth
	 * @deprecated
	 */
	public function findByUser(Entity\User $user)
	{
		return $this->authDao->findBy([
					'user' => $user,
		]);
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
		$user->unsetRecovery();
		$this->em->persist($user);
		
		$this->em->flush();
		return $auth->user;
	}
	
	/**
	 * Update OAuth access token.
	 * @param Auth $auth
	 * @param string $token
	 * @return Entity\Auth
	 */
	public function updateAccessToken(Auth $auth, $token)
	{
		$auth->token = $token;
		return $this->authDao->save($auth);
	}
}

class AuthFacadeException extends \Exception
{}
