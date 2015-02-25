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

	/** @ORM\ManyToOne(targetEntity="Job", inversedBy="skillRequests") */
	protected $job;

	/** 
	 * @ORM\ManyToOne(targetEntity="Skill")
     * @ORM\JoinColumn(onDelete="CASCADE") 
	 */
	protected $skill;

	/** @ORM\ManyToOne(targetEntity="SkillLevel") */
	protected $levelFrom;

	/** @ORM\ManyToOne(targetEntity="SkillLevel") */
	protected $levelTo;

	/** @ORM\Column(type="integer", nullable=true) */
	protected $yearsFrom;

	/** @ORM\Column(type="integer", nullable=true) */
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
	
	/**
	 * Import all data (except id) from inserted item
	 * @param SkillKnowRequest $imported
	 * @return self
	 */
	public function import(SkillKnowRequest $imported)
	{
		$this->levelFrom = $imported->levelFrom;
		$this->levelTo = $imported->levelTo;
		$this->yearsFrom = $imported->yearsFrom;
		$this->yearsTo = $imported->yearsTo;
		$this->job = $imported->job;
		return $this;
	}
	
	public function yearsDoesntMather()
	{
		$this->yearsFrom = NULL;
		$this->yearsTo = NULL;
		return $this;
	}

}
