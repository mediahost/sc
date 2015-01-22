<?php

namespace App\Model\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Kdyby\Doctrine\Entities\BaseEntity;

/**
 * @ORM\Entity
 *
 * @property string $name
 * @property boolean $isDefault
 * @property ArrayCollection $skillKnows
 * @property-write SkillKnow $skillKnow
 * @property Candidate $candidate
 */
class Cv extends BaseEntity
{

	use \Kdyby\Doctrine\Entities\Attributes\Identifier;

	/** @ORM\Column(type="string", length=100, nullable=true) */
	protected $name;

	/** @ORM\Column(type="boolean", nullable=false) */
	protected $isDefault;

	/** @ORM\OneToMany(targetEntity="SkillKnow", mappedBy="cv", cascade={"persist", "remove"}, orphanRemoval=true) */
	protected $skillKnows;

	/** @ORM\ManyToOne(targetEntity="Candidate", inversedBy="cvs") */
	protected $candidate;

	public function __construct($name = NULL)
	{
		if ($name) {
			$this->name = $name;
		}
		parent::__construct();
		$this->skillKnows = new ArrayCollection;
	}

	/** @return string */
	public function __toString()
	{
		return (string) $this->name;
	}

	// <editor-fold defaultstate="collapsed" desc="setters">

	/**
	 * Set candidate and set is default by candidate cvs
	 * @param Candidate $candidate
	 * @return self
	 */
	public function setCandidate(Candidate $candidate)
	{
		$this->candidate = $candidate;
		return $this;
	}

	/**
	 * Add skillKnow or edit existing skillKnow (by ID or SkillID)
	 * @param SkillKnow $skillKnow
	 * @param bool $clear Clear all previous skills.
	 * @return self
	 */
	public function setSkillKnow(SkillKnow $skillKnow, $clear = FALSE)
	{
		if ($clear) {
			$this->skillKnows->clear();
			$existedSkill = FALSE;
		} else {
			$existedSkill = $this->getExistedSkill($skillKnow);
		}
		if ($existedSkill) {
			$existedSkill->import($skillKnow);
		} else if (!$this->skillKnows->contains($skillKnow)) {
			$this->skillKnows->add($skillKnow);
		}
		return $this;
	}

	/** @return self */
	public function clearSkills()
	{
		$this->skillKnows->clear();
		return $this;
	}

	/**
	 * @param SkillKnow $skillKnow
	 * @return self
	 */
	public function removeSkill(SkillKnow $skillKnow)
	{
		$this->skillKnows->removeElement($skillKnow);
		return $this;
	}

	// </editor-fold>
	// <editor-fold defaultstate="collapsed" desc="getters">

	/**
	 * Return SkillKnow from collection by skill
	 * @param SkillKnow $skillKnow
	 * @return SkillKnow
	 */
	public function getExistedSkill(SkillKnow $skillKnow)
	{
		$findedSkills = $this->skillKnows->filter(function ($item) use ($skillKnow) {
			return ($skillKnow->id && $skillKnow->id === $item->id) ||
					(!$skillKnow->id && $skillKnow->skill->id === $item->skill->id);
		});
		return $findedSkills->first();
	}

	// </editor-fold>
}
