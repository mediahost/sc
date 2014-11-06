<?php

namespace App\Model\Storage;

use App\Model\Entity\Company;
use App\Model\Entity\User;
use Exception;
use Nette\Http\Session;
use Nette\Http\SessionSection;
use Nette\Object;

/**
 * Description of RegistrationStorage
 *
 * @property Auth $auth
 * @property User $user
 * @property array $defaults
 * @property bool $verification
 */
class SignUpStorage extends Object
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

	/** @param User $user */
	public function setUser(User $user)
	{
		$this->section->user = $user;
	}

	/** @param string $role */
	public function setRole($role)
	{
		$this->section->role = $role;
	}

	/** @param Company $company */
	public function setCompany($company)
	{
		$this->section->company = $company;
	}

	/** @param array $defaults */
	public function setDefaults($defaults)
	{
		$this->section->defaults = (array) $defaults;
	}

	/** @return User */
	public function getUser()
	{
		return $this->section->user;
	}

	/** @return string */
	public function getRole()
	{
		return $this->section->role;
	}

	/** @return Company */
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
	 * Set up all session properties to their default values.
	 * @param bool $force
	 * @return void
	 */
	private function initSession($force = FALSE)
	{
		$defaults = [
			'oauth' => FALSE,
			'verification' => FALSE,
			'user' => new User(),
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

class SignUpStorageException extends Exception
{
	
}
