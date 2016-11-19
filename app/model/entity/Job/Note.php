<?php

namespace App\Model\Entity;

use Doctrine\ORM\Mapping as ORM;
use Kdyby\Doctrine\Entities\Attributes\Identifier;
use Kdyby\Doctrine\Entities\BaseEntity;
use Knp\DoctrineBehaviors\Model;

/**
 * @ORM\Entity
 *
 * @property Match $match
 * @property User $user
 * @property string $text
 * @property int $type
 */
class Note extends BaseEntity
{
	const TYPE_ADMIN = 1;
	const TYPE_COMPANY = 2;

	use Identifier;
	use Model\Timestampable\Timestampable;

	/** @ORM\ManyToOne(targetEntity="Match", inversedBy="notes") */
	protected $match;

	/** @ORM\Column(type="smallint") */
	protected $type;

	/** @ORM\ManyToOne(targetEntity="User") */
	protected $user;

	/** @ORM\Column(type="text") */
	protected $text;

	public function isAdmin()
	{
		return $this->type === self::TYPE_ADMIN;
	}

	public function isCompany()
	{
		return $this->type === self::TYPE_COMPANY;
	}

	public function __toString()
	{
		return $this->text;
	}

}
