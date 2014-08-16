<?php

namespace App\Model\Facade;

use Kdyby\Doctrine\EntityDao;

/**
 * Description of Registration
 *
 * @author Martin Šifra <me@martinsifra.cz>
 */
class Registration extends Base
{
	/** @var EntityDao @inject */
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
		return $this->dao->findOneBy(['email' => $email]);
	}
	
	public function registerFromFacebook($id, $me)
	{
		$auth = new Entity\Auth();
		$auth->key = $id;
		$auth->source = 'facebook';
		
		$role = $this->em->getDao(Entity\Role::getClassName())->findOneBy(['name' => 'guest']);
		
		$user = new Entity\User();
		$user->addAuth($auth);
		$user->addRole($role);
		
		return $this->users->save($user);
	}
	
	public function mergeFromFacebook($id, $me, $user, $token)
	{
		$auth = new Entity\Auth();
		$auth->key = $id;
		$auth->source = 'facebook';
		$auth->token = $token;
		
		$user->addAuth($auth);
		
		return $this->users->save($user);
	}

}
