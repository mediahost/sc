<?php

namespace App\Model\Entity;

use Doctrine\ORM\Mapping as ORM,
	\Doctrine\Common\Collections\ArrayCollection;

/**
 * Skill entity
 * @ORM\Entity
 * 
 * @property string $name
 * @property SkillCategory $category
 */
class Skill extends \Kdyby\Doctrine\Entities\BaseEntity
{
	
	use \Kdyby\Doctrine\Entities\Attributes\Identifier;
	
	// <editor-fold defaultstate="collapsed" desc="constants & variables">
	
	/**
	 * @ORM\Column(type="string", nullable=false)
	 */
	protected $name;
	
	/**
	 * @ORM\ManyToOne(targetEntity="SkillCategory")
	 * @ORM\JoinColumn(name="skill_category_id", referencedColumnName="id", nullable=false)
	 */
	protected $category;
	
	// </editor-fold>
	
}
