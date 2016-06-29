<?php

namespace App\Model\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Kdyby\Doctrine\Entities\Attributes\Identifier;
use Kdyby\Doctrine\Entities\BaseEntity;

/**
 * @ORM\Entity
 * 
 * @property string $name
 * @property JobCategory $parent
 * @property Collection $childs
 * @property Collection $skills
 */
class JobCategory extends BaseEntity
{
	use Identifier;
	
	/** @ORM\Column(type="string", length=64, nullable=false) */
	protected $name;
    
    /**
	 * @ORM\ManyToOne(targetEntity="JobCategory", inversedBy="childs")
	 * @ORM\JoinColumn(name="parent_id", referencedColumnName="id")
	 */
	protected $parent;

	/** @ORM\OneToMany(targetEntity="JobCategory", mappedBy="parent") */
	private $childs;

	/** @ORM\OneToMany(targetEntity="Job", mappedBy="category") */
	private $skills;
	
	
	public function __construct($name = NULL) {
		if ($name) {
			$this->name = $name;
		}
        $this->childs = new ArrayCollection;
		$this->skills = new ArrayCollection;
		parent::__construct();
	}
    
    /** @return Collection */
	public function getChilds()
	{
		return $this->childs;
	}

	/** @return Collection */
	public function getSkills()
	{
		return $this->skills;
	}
    
    /** @return bool */
    public function isNew()
	{
		return $this->id === NULL;
	}
    
    public function __toString()
	{
		return (string) $this->name;
	}
}
