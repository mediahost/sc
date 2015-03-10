<?php

namespace App\Model\Entity\Traits;

use App\Model\Entity\SkillKnow;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

trait CvSkillsUsing
{

	/** @ORM\OneToMany(targetEntity="SkillKnow", mappedBy="cv", cascade={"persist", "remove"}, orphanRemoval=true) */
	private $skillKnows;

	/** @var ArrayCollection */
	private $newSkills;

	public function setSkillKnow(SkillKnow $skillKnow)
	{
		$this->initNewSkills();

		if ($skillKnow->isEmpty()) {
			return $this;
		}

		$existedSkill = $this->findExistedSkill($skillKnow);
		if ($existedSkill) {
			$skillKnow = $existedSkill->import($skillKnow);
		} else if (!$this->skillKnows->contains($skillKnow)) {
			$this->skillKnows->add($skillKnow);
		}

		$this->newSkills->add($skillKnow);

		return $this;
	}

	public function clearSkills()
	{
		$this->skillKnows->clear();
		$this->newSkills->clear();
		return $this;
	}

	public function removeSkill(SkillKnow $skillKnow)
	{
		$this->skillKnows->removeElement($skillKnow);
		return $this;
	}

	public function removeOldSkillKnows()
	{
		$removeUnchanged = function (SkillKnow $skillKnow) {
			if (!$this->newSkills->contains($skillKnow)) {
				$this->removeSkill($skillKnow);
			}
		};
		if ($this->newSkills) {
			$this->skillKnows->map($removeUnchanged);
		}
		return $this;
	}

	/** @return Collection */
	public function getSkillKnows()
	{
		return $this->skillKnows;
	}

	private function initNewSkills()
	{
		if (!$this->newSkills) {
			$this->newSkills = new ArrayCollection;
		}
		return $this;
	}

	private function findExistedSkill(SkillKnow $skillKnow)
	{
		$isSkillsSame = function ($item) use ($skillKnow) {
			return
					($skillKnow->id && $skillKnow->id === $item->id) ||
					(!$skillKnow->id && $skillKnow->skill->id === $item->skill->id);
		};
		$findedSkills = $this->skillKnows->filter($isSkillsSame);
		return $findedSkills->first();
	}

}