<?php

namespace App\Model\Storage;

use Nette\Http\Session,
	Nette\Http\SessionSection,
	Nette\Utils\ArrayHash;
use App\Model\Entity;

/**
 * Description of RegistrationStorage
 *
 * @property Entity\Auth $auth
 * @property Entity\User $user
 * @property array $defaults
 */
class SignUpStorage extends \Nette\Object
{

	/** @var Session OK */
	public $session;

	/** @var SessionSection OK */
	public $section;

	public function __construct(Session $session)
	{
		$this->section = $session->getSection('registration');
		$this->session = $session;

		$this->section->warnOnUndefined = TRUE;

		// Initialization
		$this->initSession();
	}

	/** @param Entity\User $user */
	public function setUser(Entity\User $user)
	{
		$this->section->user = $user;
	}

	/** @param string $role */
	public function setRole($role)
	{
		$this->section->role = $role;
	}

	/** @param Entity\Company $comapny */
	public function setCompany($company)
	{
		$this->section->company = $company;
	}

	/** @param array $defaults */
	public function setDefaults($defaults)
	{
		$this->section->defaults = (array) $defaults;
	}

	/** @return Entity\User */
	public function getUser()
	{
		return $this->section->user;
	}

	/** @return string */
	public function getRole()
	{
		return $this->section->role;
	}

	/** @return Entity\Company */
	public function getCompany()
	{
		return $this->section->company;
	}

	/** @return array */
	public function getDefaults()
	{
		return $this->section->defaults;
	}

	/**
	 * OK
	 * @return boolean
	 */
	public function isVerified()
	{
		return $this->section->verification;
	}

	/**
	 * OK
	 * @param bool $bool
	 * @return bool
	 */
	public function setVerification($bool)
	{
		$this->section->verification = $bool;
	}

	public function wipe()
	{
		$this->initSession(TRUE);
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
	private function initSession($force = FALSE)
	{
		$defaults = [
			'oauth' => FALSE,
			'verification' => FALSE,
			'user' => new Entity\User(),
			'defaults' => [],
			'role' => NULL,
			'company' => NULL
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

}

class SignUpStorageException extends \Exception
{
	
}
