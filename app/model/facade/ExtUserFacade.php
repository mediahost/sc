<?php

namespace App\Model\Facade;

use App\Model\Entity;

class ExtUserFacade extends \Nette\Object
{
	/** @var \Kdyby\Doctrine\EntityManager */
	private $em;
	
	private $dao;


	public function __construct(\Kdyby\Doctrine\EntityManager $em)
	{
		$this->em = $em;
		$this->dao = $this->em->getDao(Entity\User::getClassName());
	}

	public function findByFacebookId($id)
	{
		return $this->dao->findOneBy([
			'auths.key' => $id,
			'auths.source' => 'facebook'
		]);
	}
	
	public function findByEmail($email)
	{
		return $this->dao->findOneBy(['email' => $email]);
	}
	
	/**
	 * Variable $me contains all the public information about the user
	 * including facebook id, name and email, if he allowed you to see it.
	 */
	public function registerFromFacebook($id, $me)
	{
		$auth = new Entity\Auth();
		$auth->key = $id;
		$auth->source = 'facebook';
		
		$role = $this->em->getDao(Entity\Role::getClassName())->findOneBy(['name' => 'guest']);
		
		$user = new Entity\User();
		$user->addAuth($auth);
		$user->addRole($role);
		
		$this->dao->save($user);
		return $user;
	}
	
	public function mergeFromFacebook($id, $me, $user, $token)
	{
		$auth = new Entity\Auth();
		$auth->key = $id;
		$auth->source = 'facebook';
		$auth->token = $token;
		
		$user->addAuth($auth);
		
		$this->dao->save($user);
		return $user;
	}

	/**
	 * You should save the access token to database for later usage.
	 *
	 * You will need it when you'll want to call Facebook API,
	 * when the user is not logged in to your website,
	 * with the access token in his session.
	 */
	public function updateFacebookAccessToken($id, $token)
	{
		$authDao = $this->em->getDao(Entity\Auth::getClassName());
		$auth = $authDao->findOneBy(['key' => $id]);
		
		$auth->token = $token;
		return $authDao->save($auth);
	}
}
