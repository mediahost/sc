<?php

namespace App\Model\Entity;

use Doctrine\ORM\Mapping as ORM;
use Kdyby\Doctrine\Entities\Attributes\Identifier;
use Kdyby\Doctrine\Entities\BaseEntity;

/**
 * @ORM\Entity
 *
 * @property Skill $skill
 * @property SkillLevel $level
 * @property int $years
 */
class SkillKnow extends BaseEntity
{

	use Identifier;

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

	public function __toString()
	{
		return $this->skill . ':' . $this->level . ($this->years ? (':' . $this->years) : '');
	}

	public function import(SkillKnow $imported)
	{
		$this->level = $imported->level;
		$this->years = $imported->years;
		$this->cv = $imported->cv;
		return $this;
	}

	public function isEmpty()
	{
		return (bool)(!$this->level || !$this->level->isRelevant());
	}

	public function hasYearsInRange($from = NULL, $to = NULL)
	{
		if ($this->isFilledYearsValue($from) && $this->isFilledYearsValue($to)) {
			return $from <= $this->years && $this->years <= $to;
		}
		if ($this->isFilledYearsValue($from) && !$this->isFilledYearsValue($to)) {
			return $from <= $this->years;
		}
		if ($this->isFilledYearsValue($to) && !$this->isFilledYearsValue($from)) {
			return $this->years <= $to;
		}
		return TRUE;
	}

	private function isFilledYearsValue($value)
	{
		return (bool)($value !== NULL);
	}

}
