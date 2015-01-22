<?php

namespace App\Model\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Kdyby\Doctrine\Entities\BaseEntity;

/**
 * SkillCategory entity
 * @ORM\Entity
 * 
 * @property string $name
 * @property SkillCategory $parent
 * @property SkillCategory[] $childs
 */
class SkillCategory extends BaseEntity
{

	use \Kdyby\Doctrine\Entities\Attributes\Identifier;

	/**
	 * @ORM\Column(type="string", nullable=false)
	 */
	protected $name;

	/**
	 * @ORM\ManyToOne(targetEntity="SkillCategory", inversedBy="childs")
	 * @ORM\JoinColumn(name="parent_id", referencedColumnName="id")
	 */
	protected $parent;

	/**
	 * @ORM\OneToMany(targetEntity="SkillCategory", mappedBy="parent")
	 */
	protected $childs;

	/**
	 * @ORM\OneToMany(targetEntity="Skill", mappedBy="category")
	 */
	protected $skills;

	public function __construct($name = NULL)
	{
		if ($name) {
			$this->name = $name;
		}
		parent::__construct();
		$this->childs = new ArrayCollection();
		$this->skills = new ArrayCollection();
	}

	public function __toString()
	{
		return (string) $this->name;
	}

}
