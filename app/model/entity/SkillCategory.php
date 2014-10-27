<?php

namespace App\Model\Entity;

use Doctrine\ORM\Mapping as ORM,
	\Doctrine\Common\Collections\ArrayCollection;

/**
 * SkillCategory entity
 * @ORM\Entity
 * 
 * @property string $name
 * @property SkillCategory $parent
 */
class SkillCategory extends \Kdyby\Doctrine\Entities\BaseEntity
{
	
	use \Kdyby\Doctrine\Entities\Attributes\Identifier;
	
	// <editor-fold defaultstate="collapsed" desc="constants & variables">
	
	/**
	 * @ORM\Column(type="string", nullable=false)
	 */
	protected $name;
	
	/**
	 * @ORM\ManyToOne(targetEntity="SkillCategory")
	 */
	protected $parent;
	
	// </editor-fold>
	
}
