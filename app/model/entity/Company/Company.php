<?php

namespace App\Model\Entity;

use App\Model\Entity\Traits\CompanyAccess;
use App\Model\Entity\Traits\CompanyJobs;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Kdyby\Doctrine\Entities\Attributes\Identifier;
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

	use Identifier;
	use CompanyAccess;
	use CompanyJobs;

	/** @ORM\Column(type="string", length=512, nullable=false) */
	protected $name;

	/** @ORM\Column(type="string", length=100, nullable=true) */
	protected $companyId;

	/** @ORM\Column(type="text", nullable=true) */
	protected $address;

	public function __construct($name = NULL)
	{
		if ($name) {
			$this->name = $name;
		}
		$this->accesses = new ArrayCollection;
		$this->jobs = new ArrayCollection;
		parent::__construct();
	}

	public function __toString()
	{
		return (string) $this->name;
	}

	public function isNew()
	{
		return $this->id === NULL;
	}

}
