<?php

namespace App\Model\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Registration entity
 * @author Martin Šifra <me@martinsifra.cz>
 * 
 * @ORM\Entity
 * 
 * @property $email
 * @property $key
 * @property $source
 * @property $token
 * @property $hash
 * @property $name
 * @property $verificationToken
 * @property $verificationExpiration
 * 
 * @method App\Model\Entity\Registration setKey(string $key)
 * @method App\Model\Entity\Registration setSource(string $key)
 */
class Registration extends \Kdyby\Doctrine\Entities\BaseEntity
{

	use \Kdyby\Doctrine\Entities\Attributes\Identifier;

	/**
	 * @ORM\Column(type="string", nullable=false)
	 */
	protected $email;

	/**
	 * @ORM\Column(name="`key`", type="string", length=256)
	 */
	protected $key;

	/**
	 * @ORM\Column(type="string", length=256)
	 */
	protected $source;

	/**
	 * @ORM\Column(type="string", length=256, nullable=true)
	 */
	protected $token;

	/**
	 * @ORM\Column(type="string", length=256, nullable=true)
	 */
	protected $hash;
	
    /**
     * @ORM\Column(type="string", length=512, nullable=true)
     */
    protected $name;

	/**
	 * @ORM\Column(type="string", length=256, nullable=false)
	 */
	protected $verificationToken;
	
    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
	protected $verificationExpiration;
	

	/**
	 * Computes salted password hash.
	 * @param string Password to be hashed.
	 * @param array with cost (4-31), salt (22 chars)
	 * @return Registration
	 */
	public function setPassword($password, array $options = NULL)
	{
		if ($password !== '' && $password !== NULL) {
			$this->hash = \Nette\Security\Passwords::hash($password, $options);
		}
		return $this;
	}
}
