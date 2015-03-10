<?php

namespace App\Model\Entity\Traits;

use DateTime;
use Nette\Security\Passwords;

/**
 * @property-write $password
 * @property-read string $recoveryToken
 * @property-read DateTime $recoveryExpiration
 */
trait UserPassword
{

	/** @ORM\Column(type="string", length=256, nullable=true) */
	private $hash;

	/** @ORM\Column(type="string", length=256, nullable=true) */
	private $recoveryToken;

	/** @ORM\Column(type="datetime", nullable=true) */
	private $recoveryExpiration;

	public function setPassword($password, array $options = NULL)
	{
		$this->hash = Passwords::hash($password, $options);
		return $this;
	}

	public function clearPassword()
	{
		$this->hash = NULL;
		return $this;
	}

	public function verifyPassword($password)
	{
		return Passwords::verify($password, $this->hash);
	}

	public function needsRehash(array $options = NULL)
	{
		return Passwords::needsRehash($this->hash, $options);
	}

	public function setRecovery($token, $expirationTime)
	{
		if (!($expirationTime instanceof DateTime)) {
			$expirationTime = new DateTime($expirationTime);
		}

		$this->recoveryToken = $token;
		$this->recoveryExpiration = $expirationTime;

		return $this;
	}

	public function removeRecovery()
	{
		$this->recoveryToken = NULL;
		$this->recoveryExpiration = NULL;
		return $this;
	}

	/** @return string */
	public function getRecoveryToken()
	{
		return $this->recoveryToken;
	}

	/** @return DateTime */
	public function getRecoveryExpiration()
	{
		return $this->recoveryExpiration;
	}

}