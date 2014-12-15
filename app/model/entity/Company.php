<?php

namespace App\Model\Entity;

use Doctrine\ORM\Mapping as ORM;
use Kdyby\Doctrine\Entities\BaseEntity;

/**
 * @ORM\Entity
 *
 * @property string $name
 * @property string $companyId
 * @property string $address
 */
class Company extends BaseEntity
{

	use \Kdyby\Doctrine\Entities\Attributes\Identifier;

	/**
	 * @ORM\Column(type="string", length=512, nullable=false)
	 */
	protected $name;

	/**
	 * @ORM\Column(type="string", length=100, nullable=true)
	 */
	protected $companyId;

	/**
	 * @ORM\Column(type="text", nullable=true)
	 */
	protected $address;
	
	/** @ORM\OneToMany(targetEntity="CompanyPriviledge", mappedBy="company", fetch="LAZY", cascade={"persist"}) */
	protected $acceses;

	/** @return string */
	public function __toString()
	{
		return (string) $this->name;
	}

}
