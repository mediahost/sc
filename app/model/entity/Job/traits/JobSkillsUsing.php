<?php

namespace App\Model\Entity\Traits;

use App\Model\Entity\SkillKnowRequest;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

trait JobSkillsUsing
{

	/** @ORM\OneToMany(targetEntity="SkillKnowRequest", mappedBy="job", cascade={"persist", "remove"}, orphanRemoval=true) */
	private $skillRequests;

	/** @var ArrayCollection */
	private $newSkills;

	public function setSkillRequest(SkillKnowRequest $skillRequest)
	{
		$this->initNewSkills();

		if ($skillRequest->isEmpty()) {
			return $this;
		}

		$existedSkill = $this->findExistedSkill($skillRequest);
		if ($existedSkill) {
			$skillRequest = $existedSkill->import($skillRequest);
		} else if (!$this->skillRequests->contains($skillRequest)) {
			$this->skillRequests->add($skillRequest);
		}

		$this->newSkills->add($skillRequest);

		return $this;
	}

	public function clearSkills()
	{
		$this->skillRequests->clear();
		$this->newSkills->clear();
		return $this;
	}

	public function removeSkill(SkillKnowRequest $skillRequest)
	{
		$this->skillRequests->removeElement($skillRequest);
		return $this;
	}

	public function removeOldSkillRequests()
	{
		$removeUnchanged = function (SkillKnowRequest $skillRequest) {
			if (!$this->newSkills->contains($skillRequest)) {
				$this->removeSkill($skillRequest);
			}
		};
		if ($this->newSkills) {
			$this->skillRequests->map($removeUnchanged);
		}
		return $this;
	}

	/** @return Collection */
	public function getSkillRequests()
	{
		return $this->skillRequests;
	}

	private function initNewSkills()
	{
		if (!$this->newSkills) {
			$this->newSkills = new ArrayCollection;
		}
		return $this;
	}

	private function findExistedSkill(SkillKnowRequest $skillRequest)
	{
		$isSkillsSame = function ($item) use ($skillRequest) {
			return
					($skillRequest->id && $skillRequest->id === $item->id) ||
					(!$skillRequest->id && $skillRequest->skill->id === $item->skill->id);
		};
		$findedSkills = $this->skillRequests->filter($isSkillsSame);
		return $findedSkills->first();
	}

}