<?php

namespace App\Model\Entity;

use Doctrine\ORM\Mapping as ORM;
use Kdyby\Doctrine\Entities\Attributes\Identifier;
use Kdyby\Doctrine\Entities\BaseEntity;

/**
 * @ORM\Entity
 *
 * @property Skill $skill
 * @property-read SkillLevel $levelFrom
 * @property-read SkillLevel $levelTo
 * @property-read int $yearsFrom
 * @property-read int $yearsTo
 * @property-read bool $levelsMatter
 * @property-read bool $yearsMatter
 * @property-read bool $empty
 */
class SkillKnowRequest extends BaseEntity
{

	use Identifier;

	/** @ORM\ManyToOne(targetEntity="Job", inversedBy="skillRequests") */
	protected $job;

	/**
	 * @ORM\ManyToOne(targetEntity="Skill")
	 * @ORM\JoinColumn(onDelete="CASCADE")
	 */
	protected $skill;

	/** @ORM\ManyToOne(targetEntity="SkillLevel") */
	private $levelFrom;

	/** @ORM\ManyToOne(targetEntity="SkillLevel") */
	private $levelTo;

	/** @ORM\Column(type="integer", nullable=true) */
	private $yearsFrom;

	/** @ORM\Column(type="integer", nullable=true) */
	private $yearsTo;

	public function __toString()
	{
		return
				$this->skill . ':' .
				$this->levelFrom . '-' . $this->levelTo . ':' .
				$this->yearsFrom . '-' . $this->yearsTo;
	}

	public function import(SkillKnowRequest $imported)
	{
		$this->setLevels($imported->levelFrom, $imported->levelTo);
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
		$this->yearsFrom = $from ? (int) $from : NULL;
		$this->yearsTo = $to ? (int) $to : NULL;
	}

	public function hasOneLevel()
	{
		return (bool) ($this->levelFrom === $this->levelTo);
	}

	public function isLevelsMatter()
	{
		return !$this->levelFrom->isFirst() || !$this->levelTo->isLast();
	}

	public function isYearsMatter()
	{
		return (bool) ($this->yearsFrom || $this->yearsTo);
	}

	public function isEmpty()
	{
		return !$this->isLevelsMatter();
	}

	public function isSatisfiedBy(SkillKnow $skillKnow)
	{
		$skillFits = $skillKnow->skill->isEqual($this->skill);
		$levelFits = $this->isLevelsMatter() ? $skillKnow->level->isInRange($this->levelFrom, $this->levelTo) : TRUE;
		$yearsFits = $this->isYearsMatter() ? $skillKnow->hasYearsInRange($this->yearsFrom, $this->yearsTo) : TRUE;

		return $skillFits && $levelFits && $yearsFits;
	}

	/** @return SkillLevel */
	public function getLevelFrom()
	{
		return $this->levelFrom;
	}

	/** @return SkillLevel */
	public function getLevelTo()
	{
		return $this->levelTo;
	}

	/** @return int */
	public function getYearsFrom()
	{
		return $this->yearsFrom;
	}

	/** @return int */
	public function getYearsTo()
	{
		return $this->yearsTo;
	}

}
