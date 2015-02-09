<?php

namespace App\Model\Entity;

use Doctrine\ORM\Mapping as ORM;
use Kdyby\Doctrine\Entities\BaseEntity;

/**
 * @ORM\Entity
 *
 * @property Company $company
 * @property string $name
 * @property string $description
 */
class Job extends BaseEntity
{

	use \Kdyby\Doctrine\Entities\Attributes\Identifier;
	
    /**
     * @ORM\ManyToOne(targetEntity="Company", inversedBy="jobs")
     **/
    protected $company;

	/**
	 * @ORM\Column(type="string", length=512, nullable=false)
	 */
	protected $name;

	/**
	 * @ORM\Column(type="text", nullable=true)
	 */
	protected $description;

	public function __construct($name = NULL)
	{
		parent::__construct();
		if ($name) {
			$this->name = $name;
		}
	}
	
	public function isNew()
	{
		return $this->id === NULL;
	}

	/** @return string */
	public function __toString()
	{
		return (string) $this->name;
	}

}
