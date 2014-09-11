<?php

namespace App\Model\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Role entity
 * @ORM\Entity
 * 
 * @property string $name
 */
class Role extends \Kdyby\Doctrine\Entities\BaseEntity
{

	use \Kdyby\Doctrine\Entities\Attributes\Identifier;

	const ROLE_GUEST = "guest";
	const ROLE_SIGNED = "signed";
	const ROLE_CANDIDATE = "candidate";

	/**
	 * @ORM\Column(type="string", length=128)
	 */
	protected $name;

}
