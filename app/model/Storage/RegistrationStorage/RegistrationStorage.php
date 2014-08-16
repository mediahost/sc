<?php

namespace App\Model\Storage;

use App\Model\Entity;

/**
 * Description of RegistrationStorage
 *
 * @author Martin Šifra <me@martinsifra.cz>
 * 
 * @property Entity\Auth $auth
 * @property Entity\User $user
 */
class RegistrationStorage extends \Nette\Object
{
	
    /** @var \Nette\Http\SessionSection */
    public $section;
	
	/** @var \Kdyby\Doctrine\EntityManager */
	private $em;
	
	
	public $session;
	
	
	public function __construct(\Nette\Http\Session $session, \Kdyby\Doctrine\EntityManager $em)
	{
		$this->section = $session->getSection('registration');
		$this->session = $session;
		$this->em = $em;
	}
	
	/**
	 * Create an instance of User in session for future usage in registration
	 * form from Facebook's data
	 */
	public function storeFromFacebook($id, $data, $token)
	{
		$this->auth = new Entity\Auth();
		$this->auth->key = $id;
		$this->auth->source = 'facebook';
		$this->auth->token = $token;
		
		
		$this->data = $data;
		
		$this->defaults = NULL;
		$this->defaults = [
			'name' => $data->name,
			'email' => $data->email
		];
		
		

		$this->setUser(new Entity\User());
//		$this->auth = $auth;
		$this->user->addAuth($this->auth);
		
		return $this->auth;
	}
	
	/**
	 * 
	 */
	public function registerFromTwitter($id)
	{
		$this->auth = new Entity\Auth();
		$this->auth->key = $id;
		$this->auth->source = 'twitter';		
	}
	
	/**
	 * 
	 */
	public function registerFromGoogle($id)
	{
		$this->auth = new Entity\Auth();
		$this->auth->key = $id;
		$this->auth->source = 'google';
	}
	
	public function isOauth()
	{
		return $this->session->hasSection('registration');
	}
	
	public function wipe()
	{
		$this->section->remove();
	}	
	
	public function setAuth($auth)
	{
		$this->section->auth = $auth;
	}
	
	/** @return Entity\Auth */
	public function getAuth()
	{
		return $this->section->auth;
	}
	
	public function setUser($user)
	{
		$this->section->user = $user;
	}
	
	/** @return Entity\User */
	public function getUser()
	{
		return $this->section->user;
	}
	
	public function setData($data)
	{
		$this->section->data = $data;
	}
	
	public function getData()
	{
		return $this->section->data;
	}
	
	public function setDefaults($defaults)
	{
		$this->section->defaults = $defaults;
	}
	
	public function getDefaults() {
		return $this->section->defaults;
	}
	

}
