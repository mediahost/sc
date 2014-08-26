<?php

namespace App\Model\Entity;

use Doctrine\ORM\Mapping as ORM,
	\Nette\Security\Passwords;

/**
 * Auth entity
 * @author Martin Šifra <me@martinsifra.cz>
 * 
 * @ORM\Entity
 * 
 * @property User $user
 * @property $key
 * @property $source
 * @property $token
 * @property $hash
 */
class Auth extends \Kdyby\Doctrine\Entities\BaseEntity
{

	use \Kdyby\Doctrine\Entities\Attributes\Identifier;

	/**
	 * @ORM\ManyToOne(targetEntity="User", inversedBy="auths", cascade={"persist"})
	 * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
	 */
	protected $user;

	/**
	 * @ORM\Column(name="`key`", type="string", length=256)
	 */
	protected $key;

	/**
	 * @ORM\Column(type="string", length=32)
	 */
	protected $source;

	/**
	 * @ORM\Column(type="string", length=1024, nullable=true)
	 */
	protected $token;

	/**
	 * @ORM\Column(type="string", length=256, nullable=true)
	 */
	protected $hash;

	/**
	 * Computes salted password hash.
	 * @param string Password to be hashed.
	 * @param array with cost (4-31), salt (22 chars)
	 * @return Auth
	 */
	public function setHash($password, array $options = NULL)
	{
		if ($password !== "" && $password !== NULL) {
			$this->hash = Passwords::hash($password, $options);
		}
		return $this;
	}

	/**
	 * Verifies that a password matches a hash.
	 * @param string $password Password in plain text
	 * @return bool
	 */
	public function verifyPassword($password)
	{
		return Passwords::verify($password, $this->hash);
	}

	/**
	 * Checks if the given hash matches the options.
	 * @param  array with cost (4-31)
	 * @return bool
	 */
	public function needsRehash(array $options = NULL)
	{
		return Passwords::needsRehash($this->hash, $options);
	}

	/**
	 * Verify that are hashes equal.
	 * @param string $hash Hashed password
	 * @return bool
	 */
	public function verifyHash($hash)
	{
		return $this->hash === $hash;
	}

}
