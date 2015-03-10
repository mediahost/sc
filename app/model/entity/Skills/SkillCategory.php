<?php

namespace App\Model\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Kdyby\Doctrine\Entities\Attributes\Identifier;
use Kdyby\Doctrine\Entities\BaseEntity;

/**
 * @ORM\Entity
 *
 * @property string $name
 * @property SkillCategory $parent
 * @property Collection $childs
 * @property Collection $skills
 */
class SkillCategory extends BaseEntity
{

	use Identifier;

	/** @ORM\Column(type="string", nullable=false) */
	protected $name;

	/**
	 * @ORM\ManyToOne(targetEntity="SkillCategory", inversedBy="childs")
	 * @ORM\JoinColumn(name="parent_id", referencedColumnName="id")
	 */
	protected $parent;

	/** @ORM\OneToMany(targetEntity="SkillCategory", mappedBy="parent") */
	private $childs;

	/** @ORM\OneToMany(targetEntity="Skill", mappedBy="category") */
	private $skills;

	public function __construct($name = NULL)
	{
		if ($name) {
			$this->name = $name;
		}
		$this->childs = new ArrayCollection;
		$this->skills = new ArrayCollection;
		parent::__construct();
	}

	public function isNew()
	{
		return $this->id === NULL;
	}

	public function __toString()
	{
		return (string) $this->name;
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

}
