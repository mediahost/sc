<?php

namespace App\Model\Entity;

use Doctrine\ORM\Mapping as ORM;
use Kdyby\Doctrine\Entities\BaseEntity;

/**
 * @ORM\Entity
 *
 * @property Company $company
 * @property string $name
 * @property string $description
 */
class Job extends BaseEntity
{

	use \Kdyby\Doctrine\Entities\Attributes\Identifier;

	/** @ORM\ManyToOne(targetEntity="Company", inversedBy="jobs") * */
	protected $company;

	/** @ORM\Column(type="string", length=512, nullable=false) */
	protected $name;

	/** @ORM\Column(type="text", nullable=true) */
	protected $description;

	/** @ORM\OneToMany(targetEntity="SkillKnowRequest", mappedBy="job", cascade={"persist", "remove"}, orphanRemoval=true) */
	protected $skillRequests;

	public function __construct($name = NULL)
	{
		parent::__construct();
		if ($name) {
			$this->name = $name;
		}
	}

	public function isNew()
	{
		return $this->id === NULL;
	}

	/** @return string */
	public function __toString()
	{
		return (string) $this->name;
	}

	// <editor-fold defaultstate="collapsed" desc="setters">

	/**
	 * Add skillRequest or edit existing skillRequest (by ID or SkillID)
	 * @param SkillKnowRequest $skillRequest
	 * @param bool $clear Clear all previous skills.
	 * @return self
	 */
	public function setSkillRequest(SkillKnowRequest $skillRequest, $clear = FALSE)
	{
		if ($clear) {
			$this->skillRequests->clear();
			$existedSkill = FALSE;
		} else {
			$existedSkill = $this->getExistedSkill($skillRequest);
		}
		if ($existedSkill) {
			$existedSkill->import($skillRequest);
		} else if (!$this->skillRequests->contains($skillRequest)) {
			$this->skillRequests->add($skillRequest);
		}
		return $this;
	}

	/** @return self */
	public function clearSkills()
	{
		$this->skillRequests->clear();
		return $this;
	}

	/** @return self */
	public function removeSkill(SkillKnowRequest $skillRequest)
	{
		$this->skillRequests->removeElement($skillRequest);
		return $this;
	}

	// </editor-fold>
	// <editor-fold defaultstate="collapsed" desc="getters">

	/**
	 * Return SkillKnowRequest from collection by skill
	 * @param SkillKnowRequest $skillRequest
	 * @return SkillKnowRequest
	 */
	public function getExistedSkill(SkillKnowRequest $skillRequest)
	{
		$findedSkills = $this->skillRequests->filter(function ($item) use ($skillRequest) {
			return ($skillRequest->id && $skillRequest->id === $item->id) ||
					(!$skillRequest->id && $skillRequest->skill->id === $item->skill->id);
		});
		return $findedSkills->first();
	}

	// </editor-fold>
}
