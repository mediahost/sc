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
 * @property SkillCategory[] $childs
 */
class SkillCategory extends \Kdyby\Doctrine\Entities\BaseEntity
{

	use \Kdyby\Doctrine\Entities\Attributes\Identifier;

	// <editor-fold defaultstate="expanded" desc="constants & variables">

	/**
	 * @ORM\Column(type="string", nullable=false)
	 */
	protected $name;

	/**
	 * @ORM\ManyToOne(targetEntity="SkillCategory", mappedBy="childs")
	 * @ORM\JoinColumn(name="parent_id", referencedColumnName="id")
	 */
	protected $parent;

	/**
	 * @ORM\OneToMany(targetEntity="SkillCategory", mappedBy="parent")
	 */
	protected $childs;

	// </editor-fold>
	// <editor-fold defaultstate="expanded" desc="functions">

	public function __construct()
	{
		parent::__construct();
		$this->childs = new ArrayCollection();
	}

	// </editor-fold>
}
