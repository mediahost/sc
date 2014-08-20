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
	
	/** @var EntityDao */
	private $registrations;
		
	/** @var EntityDao */
	private $roles;
	
	
	protected function init()
	{
		$this->users = $this->em->getDao(Entity\User::getClassName());
		$this->auths = $this->em->getDao(Entity\Auth::getClassName());
		$this->registrations = $this->em->getDao(Entity\Registration::getClassName());
		$this->roles = $this->em->getDao(Entity\Role::getClassName());
	}

	public function findByFacebookId($id)
	{
		return $this->users->findOneBy([
					'auths.key' => $id,
					'auths.source' => 'facebook'
		]);
	}
	

	public function findByTwitterId($id)
	{
		return $this->users->findOneBy([
					'auths.key' => $id,
					'auths.source' => 'twitter'
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

	public function merge($user, $auth)
	{
		$user->addAuth($auth);
		return $this->users->save($user);
	}

	public function register($user, $auth)
	{
		
	}

	public function verify($code)
	{
		$registration = $this->registrations->findOneBy(['verification_code' => $code]);
		
		if ($registration) {
			$user = $this->users->findOneBy(['email' => $registration->email]);
			
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
			
			$user->addRole($this->roles->findBy(['name' => 'signed']));
			
			$this->em->remove($registration);
			$this->em->persist($user);
			
			$this->em->flush();
			
			return TRUE;
		}
		
		return FALSE;
	}

}
