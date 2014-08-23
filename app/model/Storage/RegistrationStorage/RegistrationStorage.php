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

	/** @var \App\Model\Facade\Users */
	private $userFacade;

	/**
	 * An array with requred items for registration
	 * @var array
	 */
	private $required = ['email', 'birthdate'];

	/**
	 * IN => OUT
	 * @var array
	 */
	private $facebookMap = [
		'email' => 'email',
		'birthday' => 'birthdate',
		'name' => 'name'
	];

	private $fromFacebook = ['id', 'first_name', 'last_name', 'email'];

	private $fromTwitter = ['id'];




	public function __construct(\Nette\Http\Session $session, \Kdyby\Doctrine\EntityManager $em, \App\Model\Facade\Users $userFacade)
	{
		$this->section = $session->getSection('registration');
		$this->session = $session;
		$this->em = $em;
		$this->userFacade = $userFacade;

		$this->section->warnOnUndefined = TRUE;

		// Initialization
		$this->section->oauth = FALSE;
		$this->auth = new Entity\Auth();
		$this->user = new Entity\User();
	}

	/**
	 *
	 */
	public function store($source, $data)
	{
		switch ($source) {
			case 'facebook':
				$this->storeFromFacebook($data, $token);
				break;

			case 'twitter':
				$this->storeFromTwitter($data);
				break;

			default :
				throw new Exception('Wrong source of OAuth data specified.');
		}
	}

	/**
	 * Create an instance of User in session for future usage in registration
	 * form from Facebook's data
	 */
	public function storeFromFacebook($data, $token) // ToDo: $data je matoucí s $this->data, přejmenovat
	{
		$this->section->oauth = TRUE;

//		$facebookMap = [
//			'email' => $this->user->email,
//			'birthday' => NULL,
//			'name' => [
//				$this->user->firstname
//			]
//		];

//		foreach ($facebookMap as $in => $out) {
//				$array[$out] = isset($data[$in]) ? $data[$in] : NULL;
//		}


		$this->data = $this->mapFromOAuth($this->facebookMap, $data);


		$this->auth->setKey($data->id)
				->setSource('facebook')
				->setToken($token);

		$user = new Entity\User();
		$user->email = $data->

		$this->defaults = [
			'name' => $this->data->name,
			'email' => $this->data->email,
			'birthday' => $this->data->birthdate
		];


		$this->user = new Entity\User();
		$this->user->email = $this->data->email;
		$this->user = $this->userFacade->addRole($this->user, 'signed');



		return $this->auth;
	}

	/**
	 *
	 */
	public function storeFromTwitter($data)
	{
		$this->section->oauth = TRUE;

		$this->auth = new Entity\Auth();
		$this->auth->key = $data->id;
		$this->auth->source = 'twitter';

		$this->user = new Entity\User();
		$this->user->email = NULL;
		$this->user = $this->userFacade->addRole($this->user, 'signed');

		return $this->auth;
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

	public function isComplete()
	{
		foreach ($this->required as $value) {
			if ($this->data[$value] === NULL) {
				return FALSE;
			}
		}

		return TRUE;
	}

	/**
	 *
	 * @return boolean
	 */
	public function isOAuth()
	{
		return (bool) $this->section->oauth;
	}

	/**
	 *
	 * @return boolean
	 */
	public function isVerified()
	{
		if ($this->isOAuth() && isset($this->user->email)) {
			return TRUE;
		}

		return FALSE;
	}

	public function isRequired($value)
	{
		if ($this->isOAuth() && isset($this->data->$value)) {
			return FALSE;
		}

		return TRUE;
	}

	public function wipe()
	{
		$this->section->remove();
	}

	public function setAuth($auth)
	{
		$this->section->auth = $auth;
//		$this->user->addAuth($this->section->auth);
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

	public function getDefaults()
	{
		return $this->section->defaults ? $this->section->defaults : [];
	}

	public function toRegistration()
	{
		$registration = new Entity\Registration();
		$registration->email = $this->user->email;
		$registration->key = $this->auth->key;
		$registration->source = $this->auth->source;
		$registration->hash = $this->auth->hash;

		return $registration;
	}

}
