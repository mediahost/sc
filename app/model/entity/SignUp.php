<?php

namespace App\Model\Entity;

use DateTime;
use Doctrine\ORM\Mapping as ORM;
use Kdyby\Doctrine\Entities\BaseEntity;
use Nette\Security\Passwords;

/**
 * @ORM\Entity
 *
 * @property string $mail
 * @property string $name
 * @property string $hash
 * @property Role $role
 * @property string $facebookId
 * @property string $facebookAccessToken
 * @property string $twitterId
 * @property string $twitterAccessToken
 * @property string $verificationToken
 * @property DateTime $verificationExpiration
 * @property-write string $password
 */
class SignUp extends BaseEntity
{

	use \Kdyby\Doctrine\Entities\Attributes\Identifier;

	/** @ORM\Column(type="string") */
	protected $mail;

	/** @ORM\Column(type="string", length=512, nullable=true) */
	protected $name;

	/** @ORM\Column(type="string", length=256, nullable=true) */
	protected $hash;

	/**
	 * @ORM\ManyToOne(targetEntity="Role", fetch="EAGER")
	 * @ORM\JoinColumn(name="role_id", referencedColumnName="id", nullable=true)
	 * */
	protected $role;
	
	/** @ORM\Column(type="string", length=512, nullable=true) */
	protected $facebookId;
	
	/** @ORM\Column(type="string", length=512, nullable=true) */
	protected $facebookAccessToken;
	
	/** @ORM\Column(type="string", length=512, nullable=true) */
	protected $twitterId;
	
	/** @ORM\Column(type="string", length=512, nullable=true) */
	protected $twitterAccessToken;

	/** @ORM\Column(type="string", length=256) */
	protected $verificationToken;

	/** @ORM\Column(type="datetime") */
	protected $verificationExpiration;

	/**
	 * Computes salted password hash.
	 * @param string Password to be hashed.
	 * @param array with cost (4-31), salt (22 chars)
	 * @return SignUp
	 */
	public function setPassword($password, array $options = NULL)
	{
		$this->hash = Passwords::hash($password, $options);
		return $this;
	}

}
