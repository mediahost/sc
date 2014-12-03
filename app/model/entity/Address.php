<?php

namespace App\Model\Entity;

use Doctrine\ORM\Mapping as ORM;
use Kdyby\Doctrine\Entities\BaseEntity;

/**
 * @ORM\Entity
 *
 * @property string $name
 */
class Address extends BaseEntity
{

	use \Kdyby\Doctrine\Entities\Attributes\Identifier;

	/** @ORM\Column(type="string", length=512, nullable=false) */
	protected $name;

	/** @ORM\Column(type="string", length=512, nullable=false) */
	protected $street;

	/** @ORM\Column(type="string", length=512, nullable=false) */
	protected $city;

	/** @ORM\Column(type="string", length=512, nullable=false) */
	protected $zipcode;

	/** @ORM\Column(type="string", length=512, nullable=false) */
	protected $country;
	
	public function parseFromText($text)
	{
		
	}

	/** @return string */
	public function __toString()
	{
		return (string) $this->name;
	}

}
