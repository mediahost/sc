<?php

namespace App\Model\Entity;

use Doctrine\ORM\Mapping as ORM;
use Kdyby\Doctrine\Entities\BaseEntity;

/**
 * @ORM\Entity
 *
 * @property string $name
 */
class Company extends BaseEntity
{

	use \Kdyby\Doctrine\Entities\Attributes\Identifier;

	/**
	 * @ORM\Column(type="string", length=512, nullable=false)
	 */
	protected $name;

	public function __construct()
	{

	}

	/** @return string */
	public function __toString()
	{
		return (string) $this->name;
	}

}
