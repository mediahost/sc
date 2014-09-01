<?php

namespace App\Model\Facade;

use Kdyby\Doctrine\EntityDao,
	App\Model\Entity;

class AuthFacade extends BaseFacade
{

	/** @var EntityDao */
	private $auths;

	protected function init()
	{
		$this->auths = $this->em->getDao(Entity\Auth::getClassName());
	}

	/**
	 * Find Auth by e-mail of application registration
	 * @param type $email
	 * @return type
	 */
	public function findByEmail($email)
	{
		return $this->auths->findOneBy([
					'source' => Entity\Auth::SOURCE_APP,
					'key' => $email,
		]);
	}

	public function findByUser(Entity\User $user)
	{
		return $this->auths->findBy([
					'user' => $user,
		]);
	}
	
	public function recovery(Entity\Auth $auth, $password)
	{
		$auth->hash = \Nette\Security\Passwords::hash($password);
		$user = $auth->user;
		$user->recovery = NULL;
		$this->em->persist($auth);
		$this->em->persist($user);
		$this->em->flush();
		return $auth->user;
	}
	
	public function findByRecovery($token)
	{
		return $this->auths->findOneBy([
			'user.recovery' => $token
		]);
	}

}
