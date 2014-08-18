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
	
	/**
	 * An array with requred items for registration
	 * @var array
	 */
	private $required = ['email'];
	
	/**
	 * IN => OUT
	 * @var array
	 */
	private $facebookMap = [
		'email' => 'email',
		'birthday' => 'birthdate',
		'name' => 'name'
	];
	
	
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

		$this->data = $this->mapFromOAuth($this->facebookMap, $data);
		
		$this->defaults = [
			'name' => $this->data->name,
			'email' => $this->data->email,
			'birthday' => $this->data->birthdate
		];

		$this->auth = new Entity\Auth();
		$this->auth->key = $id;
		$this->auth->source = 'facebook';
		$this->auth->token = $token;

		$this->user = new Entity\User();
		$this->user->email = $data->email;

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
	
	/**
	 * 
	 * @param array $map
	 * @param \Nette\Utils\ArrayHash|array $data
	 * @return array
	 */
	public function mapFromOAuth($map, $data)
	{
		$array = [];
		
		foreach ($map as $in => $out) {
			$array[$out] = isset($data[$in]) ? $data[$in] : NULL;
		}
		
		return \Nette\Utils\ArrayHash::from($array);
	}
	
	public function checkRequired()
	{
		foreach ($this->required as $value) {
			if ($this->data[$value] === NULL) {
				return FALSE;
			}
		}
		
		return TRUE;
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
