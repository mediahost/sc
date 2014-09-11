<?php

namespace App\Model\Entity;

use Doctrine\ORM\Mapping as ORM,
	Nette\Security\Passwords;

/**
 * Auth entity
 * @ORM\Entity
 *
 * @property User $user
 * @property string $key
 * @property string $source
 * @property string $token
 * @property string $hash
 * @property-write string $password
 */
class Auth extends \Kdyby\Doctrine\Entities\BaseEntity
{

	use \Kdyby\Doctrine\Entities\Attributes\Identifier;

	const SOURCE_APP = "app";
	const SOURCE_FACEBOOK = "facebook";
	const SOURCE_TWITTER = "twitter";

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
	public function setPassword($password, array $options = NULL)
	{
		$this->hash = Passwords::hash($password, $options);
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

}
