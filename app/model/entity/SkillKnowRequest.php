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
 * @property-read int $yearsFrom
 * @property-read int $yearsTo
 * @property-read int $yearsTo
 * @property-read bool $isEmpty
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

	/** @ORM\Column(type="integer") */
	protected $yearsFrom;

	/** @ORM\Column(type="integer") */
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
		$this->setYears($imported->yearsFrom, $imported->yearsTo);
		$this->job = $imported->job;
		return $this;
	}

	public function setLevels(SkillLevel $from, SkillLevel $to)
	{
		$this->levelFrom = $from;
		$this->levelTo = $to;
	}

	public function setYears($from, $to)
	{
		$this->yearsFrom = $from ? (int) $from : 0;
		$this->yearsTo = $to ? (int) $to : 0;
	}

	public function hasOneLevel()
	{
		return (bool) ($this->levelFrom === $this->levelTo);
	}

	public function isLevelsMather()
	{
		return (bool) (!$this->levelFrom->isFirst() || !$this->levelTo->isLast());
	}

	public function isYearsMather()
	{
		return (bool) ($this->yearsFrom || $this->yearsTo);
	}
	
	public function isEmpty()
	{
		return !$this->isLevelsMather() && !$this->isYearsMather();
	}

}
