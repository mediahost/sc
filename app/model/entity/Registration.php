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
	 * @ORM\Column(type="string", length=256, nullable=false)
	 */
	protected $verification_code;

}
