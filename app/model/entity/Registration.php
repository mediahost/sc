<?php

namespace App\Model\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Registration entity
 * @ORM\Entity
 *
 * @property string $mail
 * @property string $key
 * @property string $source
 * @property string $token
 * @property string $hash
 * @property string $name
 * @property string $verificationToken
 * @property \DateTime $verificationExpiration
 * @property-write string $password
 */
class Registration extends \Kdyby\Doctrine\Entities\BaseEntity
{

	use \Kdyby\Doctrine\Entities\Attributes\Identifier;

	/**
	 * @ORM\Column(type="string", nullable=false)
	 */
	protected $mail;

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
     * @ORM\ManyToOne(targetEntity="Role", fetch="EAGER")
     * @ORM\JoinColumn(name="role_id", referencedColumnName="id", nullable=false)
     **/
	protected $role;

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
		$this->hash = \Nette\Security\Passwords::hash($password, $options);
		return $this;
	}

}
