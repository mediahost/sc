<?php

namespace App\Model\Entity;

use Doctrine\ORM\Mapping as ORM;
use Kdyby\Doctrine\Entities\BaseEntity;

/**
 * Skill entity
 * @ORM\Entity
 * 
 * @property string $name
 * @property SkillCategory $category
 */
class Skill extends BaseEntity
{

	use \Kdyby\Doctrine\Entities\Attributes\Identifier;

	/**
	 * @ORM\Column(type="string", nullable=false)
	 */
	protected $name;

	/**
	 * @ORM\ManyToOne(targetEntity="SkillCategory")
	 * @ORM\JoinColumn(name="skill_category_id", referencedColumnName="id", nullable=false)
	 */
	protected $category;

	public function __construct($name = NULL)
	{
		if ($name) {
			$this->name = $name;
		}
		parent::__construct();
	}

	public function __toString()
	{
		return (string) $this->name;
	}
	
	public function isNew()
	{
		return (bool) ($this->id === NULL);
	}
	
	public function isEqual(Skill $skill)
	{
		return (bool) ($this->id === $skill->id);
	}

}
