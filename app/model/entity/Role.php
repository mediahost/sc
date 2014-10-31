<?php

namespace App\Model\Entity;

use Doctrine\ORM\Mapping as ORM;
use Kdyby\Doctrine\Entities\BaseEntity;

/**
 * @ORM\Entity
 *
 * @property string $name
 */
class Role extends BaseEntity
{

	use \Kdyby\Doctrine\Entities\Attributes\Identifier;

	const ROLE_GUEST = 'guest';
	const ROLE_SIGNED = 'signed';
	const ROLE_CANDIDATE = 'candidate';
	const ROLE_COMPANY = 'company';

	/** @ORM\Column(type="string", length=128) */
	protected $name;

}
