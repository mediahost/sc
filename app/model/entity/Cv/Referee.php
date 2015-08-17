<?php

namespace App\Model\Entity;

use Doctrine\ORM\Mapping as ORM;
use Kdyby\Doctrine\Entities\Attributes\Identifier;
use Kdyby\Doctrine\Entities\BaseEntity;

/**
 * @ORM\Entity
 *
 * @property string $name
 * @property string $position
 * @property string $phone
 * @property string $mail
 */
class Referee extends BaseEntity
{

	use Identifier;

	/** @ORM\Column(type="string", length=100, nullable=false) */
	protected $name;

	/** @ORM\Column(type="string", length=100, nullable=false) */
	protected $position;

	/** @ORM\Column(type="string", length=50, nullable=false) */
	protected $phone;

	/** @ORM\Column(type="string", length=256, nullable=false) */
	protected $mail;

	public function __construct()
	{
		parent::__construct();
	}

	public function __toString()
	{
		return (string) $this->name;
	}

}
