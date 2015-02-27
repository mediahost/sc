<?php

namespace App\Model\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Kdyby\Doctrine\Entities\BaseEntity;

/**
 * @ORM\Entity(repositoryClass="App\Model\Repository\CvRepository")
 *
 * @property string $name
 * @property integer $lastOpenedPreviewPage Get last opened preview page
 * @property integer $lastUsedPreviewScale Get last used preview scale
 * @property boolean $isDefault
 * @property-read ArrayCollection $skillKnows
 * @property-write SkillKnow $skillKnow
 * @property Candidate $candidate
 */
class Cv extends BaseEntity
{

	use \Kdyby\Doctrine\Entities\Attributes\Identifier;

	/** @ORM\Column(type="string", length=100, nullable=true) */
	protected $name;

	/** @ORM\Column(type="integer", nullable=true) */
	protected $lastOpenedPreviewPage;

	/** @ORM\Column(type="float", nullable=true) */
	protected $lastUsedPreviewScale;

	/** @ORM\Column(type="boolean", nullable=false) */
	protected $isDefault;

	/** @ORM\OneToMany(targetEntity="SkillKnow", mappedBy="cv", cascade={"persist", "remove"}, orphanRemoval=true) */
	protected $skillKnows;

	/** @ORM\ManyToOne(targetEntity="Candidate", inversedBy="cvs") */
	protected $candidate;

	/** @var ArrayCollection */
	private $settedSkillKnows;

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
	 * Add skillKnow or edit existing skillKnow (by ID or SkillID)
	 * @param SkillKnow $skillKnow
	 * @return self
	 */
	public function setSkillKnow(SkillKnow $skillKnow)
	{
		if ($skillKnow->isEmpty()) {
			return $this;
		}

		$existedSkill = $this->getExistedSkill($skillKnow);
		if ($existedSkill) {
			$skillKnow = $existedSkill->import($skillKnow);
		} else if (!$this->skillKnows->contains($skillKnow)) {
			$this->skillKnows->add($skillKnow);
		}
		$this->addSkillAsSetted($skillKnow);
		return $this;
	}

	private function addSkillAsSetted(SkillKnow $skillKnow)
	{
		if (!$this->settedSkillKnows) {
			$this->settedSkillKnows = new ArrayCollection;
		}
		$this->settedSkillKnows->add($skillKnow);
		return $this;
	}

	/** @return self */
	public function clearSkills()
	{
		$this->skillKnows->clear();
		$this->settedSkillKnows->clear();
		return $this;
	}

	public function removeOldSkillKnows()
	{
		$mapFunc = function (SkillKnow $skillKnow) {
			if (!$this->settedSkillKnows->contains($skillKnow)) {
				$this->removeSkill($skillKnow);
			}
		};
		if ($this->settedSkillKnows) {
			$this->skillKnows->map($mapFunc);
		}
		return $this;
	}

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
