<?php

namespace App\Model\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="role")
 * @method self setName(string $value)
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

	public function setRole()
	{
		
	}

}
