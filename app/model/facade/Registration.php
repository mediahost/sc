<?php

namespace App\Model\Facade;

use Kdyby\Doctrine\EntityDao,
	App\Model\Entity;

/**
 * Description of Registration
 *
 * @author Martin Šifra <me@martinsifra.cz>
 */
class Registration extends Base
{
	/** @var EntityDao */
	private $users;
	
	/** @var EntityDao */
	private $auths;

	
	protected function init()
	{
		$this->users = $this->em->getDao(Entity\User::getClassName());
		$this->auths = $this->em->getDao(Entity\Auth::getClassName());
	}
	
	
	public function findByFacebookId($id)
	{
		return $this->users->findOneBy([
			'auths.key' => $id,
			'auths.source' => 'facebook'
		]);
	}
	
	public function updateFacebookAccessToken($id, $token)
	{
		$auth = $this->auths->findOneBy(['key' => $id]);
		$auth->token = $token;
		
		return $this->auths->save($auth);
	}
	
	public function findByEmail($email)
	{
		return $this->users->findOneBy(['email' => $email]);
	}
	
	public function merge($user,$auth)
	{	
		$user->addAuth($auth);	
		return $this->users->save($user);
	}
	
	public function register($user, $auth)
	{
		
	}

}
