<?php

namespace App\Model\Entity;

use Doctrine\ORM\Mapping as ORM;
use Kdyby\Doctrine\Entities\BaseEntity;

/**
 * Skill know entity
 * @ORM\Entity
 * 
 * @property Skill $skill
 * @property SkillLevel $level
 * @property int $years
 */
class SkillKnow extends BaseEntity
{

	use \Kdyby\Doctrine\Entities\Attributes\Identifier;

	/** @ORM\ManyToOne(targetEntity="Cv", inversedBy="skillKnows") */
	protected $cv;

	/**
	 * @ORM\ManyToOne(targetEntity="Skill")
	 * @ORM\JoinColumn(onDelete="CASCADE") 
	 */
	protected $skill;

	/** @ORM\ManyToOne(targetEntity="SkillLevel") */
	protected $level;

	/** @ORM\Column(type="integer", nullable=false) */
	protected $years;

	public function __construct()
	{
		parent::__construct();
	}

	public function __toString()
	{
		return $this->skill . ':' . $this->level . ':' . $this->years;
	}

	/**
	 * Import all data (except id) from inserted item
	 * @param SkillKnow $imported
	 * @return self
	 */
	public function import(SkillKnow $imported)
	{
		$this->level = $imported->level;
		$this->years = $imported->years;
		$this->cv = $imported->cv;
		return $this;
	}

	public function isEmpty()
	{
		return (bool) (!$this->level->isRelevant());
	}

	public function hasYearsInRange($from = NULL, $to = NULL)
	{
		if ($this->isFilledValue($from) && $this->isFilledValue($to)) {
			return $from <= $this->years && $this->years <= $to;
		}
		if ($this->isFilledValue($from) && !$this->isFilledValue($to)) {
			return $from <= $this->years;
		}
		if ($this->isFilledValue($to) && !$this->isFilledValue($from)) {
			return $this->years <= $to;
		}
		return TRUE;
	}

	private function isFilledValue($value)
	{
		return (bool) ($value !== NULL);
	}

}
