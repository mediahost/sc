<?php

namespace App\Model\Storage;

use Nette\Http\Session,
	Nette\Http\SessionSection,
	Nette\Utils\ArrayHash;
use App\Model\Entity;


/**
 * Description of RegistrationStorage
 *
 * @author Martin Šifra <me@martinsifra.cz>
 *
 * @property Entity\Auth $auth
 * @property Entity\User $user
 * @property array $defaults
 */
class RegistrationStorage extends \Nette\Object
{
	
	const SOURCE_APP = 'app';
	const SOURCE_FACEBOOK = 'facebook';
	const SOURCE_TWITTER = 'twitter';
	
	/** @var Session */
	public $session;
	
	/** @var SessionSection */
	public $section;

	/**
	 * List of valid sources names.
	 * @var array
	 */
	private $sources = [self::SOURCE_APP, self::SOURCE_FACEBOOK, self::SOURCE_TWITTER];

	/**
	 * An array with required items in User entity for complete registration.
	 * @var array
	 */
	private $required = ['mail'];

	/**
	 * List of expecting values from FB, which we can and want process.
	 * @var array
	 */
	private $facebookKeys = ['id', 'first_name', 'last_name', 'name', 'email', 'birthday'];

	/**
	 * List of expecting values from Twitter, which we can and want process.
	 * @var array
	 */
	private $twitterKeys = ['id', 'name'];


	public function __construct(Session $session)
	{
		$this->section = $session->getSection('registration');
		$this->session = $session;

		$this->section->warnOnUndefined = TRUE;

		// Initialization
		$this->initSession();
	}

	/**
	 * Saving process from given OAuth to User and Auth entities in session.
	 * @param type $source
	 * @param type $data
	 * @param type $token
	 * @throws RegistrationStorageException
	 */
	public function store($source, $data, $token = NULL)
	{

		// Convert OAuth $data to ArrayHash object
		if (!($data instanceof ArrayHash)) {
			$data = ArrayHash::from($data, TRUE);
		}

		// Recognize source type
		switch ($source) {
			case self::SOURCE_FACEBOOK:
				$this->storeFromFacebook($data, $token);
				break;

			case self::SOURCE_TWITTER:
				$this->storeFromTwitter($data, $token);
				break;

			default :
				throw new RegistrationStorageException('Unrecognized source of OAuth data.');
		}

		// If is set e-mail from OAuth, user is verified.
		if ($this->user->mail !== NULL) {
			$this->section->verified = TRUE;
		}

		// Default values for registration form
		$this->defaults = [
			'reg_name' => $this->user->name,
			'reg_mail' => $this->user->mail
		];		
	}

	/**
	 * Fill User and Auth entities with data from Facebook OAuth.
	 * @param ArrayHash $data
	 * @param string $token
	 * @return void
	 */
	public function storeFromFacebook(ArrayHash $data, $token) // ToDo: $data je matoucí s $this->data, přejmenovat
	{
		$this->section->oauth = TRUE;
		$data = $this->checkKeys($this->facebookKeys, $data);

		$this->auth->setKey($data->id)
				->setSource(self::SOURCE_FACEBOOK)
				->setToken($token);

		$this->user->setMail($data->email)
				->setName($data->first_name . ' ' . $data->last_name);
	}

	/**
	 * Fill User and Auth entities with data from Titter OAuth.
	 * @param ArrayHash $data
	 * @param string $token
	 * @return void
	 */
	public function storeFromTwitter(ArrayHash $data, $token)
	{
		$this->section->oauth = TRUE;
		$data = $this->checkKeys($this->twitterKeys, $data);

		$this->auth->setKey($data->id)
				->setSource(self::SOURCE_TWITTER)
				->setToken($token);

		$this->user->setMail(NULL)
				->setName($data->name);
	}

	/**
	 * Check if all requested indexes aren't undefined.
	 * @param array $keys
	 * @param ArrayHash $data
	 * @return ArrayHash
	 */
	public function checkKeys($keys, ArrayHash $data)
	{
		foreach ($keys as $key) {
			if (!isset($data[$key])) {
				$data->$key = NULL;
			}
		}

		return $data;
	}

	/**
	 * Return whether registration or login process via OAuth have begun.
	 * @return boolean
	 */
	public function isOAuth()
	{
		return (bool) $this->section->oauth;
	}

	/**
	 * Return whether the user is verified.
	 * @return boolean
	 */
	public function isVerified()
	{
		return $this->section->verified;
	}

	/**
	 * Returns whether is the requested value required, but empty from AOuth.
	 * Without argument checks whether is any required property NULL.
	 * @param NULL|string $key Uset entity attribute name.
	 * @return boolean
	 */
	public function isRequired($key = NULL)
	{
		if ($key === NULL) {
			foreach ($this->required as $property) {
				if ($this->user->$property === NULL) {
					return TRUE;
				}
			}
		} else {
			if ($this->isOAuth()) {
				if (in_array($key, $this->required) && $this->user->$key === NULL) {
					return TRUE;
				}
			} else {
				return TRUE;
			}
		}
		
		return FALSE;
	}

	/**
	 * 
	 */
	public function wipe()
	{
		$this->initSession(TRUE);
	}

	/** @param Entity\Auth $auth */
	public function setAuth(Entity\Auth $auth)
	{
		$this->section->auth = $auth;
	}

	/** @return Entity\Auth */
	public function getAuth()
	{
		return $this->section->auth;
	}

	/** @param Entity\User $user */
	public function setUser(Entity\User $user)
	{
		$this->section->user = $user;
	}

	/** @return Entity\User */
	public function getUser()
	{
		return $this->section->user;
	}

	/** @param array $defaults */
	public function setDefaults($defaults)
	{
		$this->section->defaults = (array) $defaults;
	}

	/** @return array */
	public function getDefaults()
	{
		return $this->section->defaults;
	}

	/**
	 * Map data from session's Auth and User to Registration.
	 * @return \App\Model\Entity\Registration
	 */
	public function toRegistration()
	{
		$registration = new Entity\Registration();
		$registration->setMail($this->user->mail)
				->setName($this->user->name)
				->setKey($this->auth->key)
				->setSource($this->auth->source)
				->setHash($this->auth->hash);

		return $registration;
	}

	/**
	 * Set up all session properties to their default values.
	 * @param bool $force
	 * @return void
	 */
	private function initSession($force = FALSE) {
		$defaults = [
			'oauth' => FALSE,
			'verified' => FALSE,
			'auth' => new Entity\Auth(),
			'user' => new Entity\User(),
			'defaults' => []
		];
		
		if ($force === FALSE) {
			foreach ($defaults as $property => $value) {
				if (!isset($this->section->{$property})) {
					$this->section->{$property} = $value;
				}
			}
		} else {
			foreach ($defaults as $property => $value) {
				$this->section->{$property} = $value;
			}			
		}
	}

	/**
	 * Check if is the source name valid.
	 * @param string $source
	 */
	public function isSource($source)
	{
		return in_array($source, $this->sources);
	}

	/**
	 * @param array
	 */
	public function getSources()
	{
		return $this->sources;
	}
	
}

class RegistrationStorageException extends \Exception
{}
