<?php

namespace App\Model\Entity;

use Doctrine\ORM\Mapping as ORM;
use Kdyby\Doctrine\Entities\BaseEntity;

/**
 * Skill know request entity
 * @ORM\Entity
 * 
 * @property Skill $skill
 * @property SkillLevel $levelFrom
 * @property SkillLevel $levelTo
 * @property int $yearsFrom
 * @property int $yearsTo
 */
class SkillKnowRequest extends BaseEntity
{

	use \Kdyby\Doctrine\Entities\Attributes\Identifier;

	/** 
	 * @ORM\ManyToOne(targetEntity="Skill")
     * @ORM\JoinColumn(onDelete="CASCADE") 
	 */
	protected $skill;

	/** @ORM\ManyToOne(targetEntity="SkillLevel") */
	protected $levelFrom;

	/** @ORM\ManyToOne(targetEntity="SkillLevel") */
	protected $levelTo;

	/** @ORM\Column(type="integer", nullable=false) */
	protected $yearsFrom;

	/** @ORM\Column(type="integer", nullable=false) */
	protected $yearsTo;

	public function __construct()
	{
		parent::__construct();
	}

	public function __toString()
	{
		return $this->skill . ':' .
				$this->levelFrom . '-' . $this->levelTo . ':' .
				$this->yearsFrom . '-' . $this->yearsTo . ':';
	}

}
