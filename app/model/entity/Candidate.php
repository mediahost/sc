<?php

namespace App\Model\Entity;

use Doctrine\ORM\Mapping as ORM;
use Kdyby\Doctrine\Entities\BaseEntity;

/**
 * @ORM\Entity
 *
 * @property string $name
 * @property string $address
 */
class Candidate extends BaseEntity
{

	use \Kdyby\Doctrine\Entities\Attributes\Identifier;

	/**
	 * @ORM\Column(type="string", length=512, nullable=false)
	 */
	protected $name;

	/**
	 * @ORM\Column(type="text", nullable=true)
	 */
	protected $address;

	/** @return string */
	public function __toString()
	{
		return (string) $this->name;
	}

}
