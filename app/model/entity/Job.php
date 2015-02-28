<?php

namespace App\Model\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Kdyby\Doctrine\Entities\BaseEntity;

/**
 * @ORM\Entity(repositoryClass="App\Model\Repository\JobRepository")
 *
 * @property Company $company
 * @property string $name
 * @property string $description
 * @property-read ArrayCollection $skillRequests
 * @property-write SkillKnowRequest $skillRequest
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

	/** @var ArrayCollection */
	private $settedSkillRequests;

	public function __construct($name = NULL)
	{
		if ($name) {
			$this->name = $name;
		}
		parent::__construct();
		$this->skillRequests = new ArrayCollection;
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
	 * @return self
	 */
	public function setSkillRequest(SkillKnowRequest $skillRequest)
	{
		if ($skillRequest->isEmpty()) {
			$this->initSettedSkillRequests();
			return $this;
		}

		$existedSkill = $this->getExistedSkill($skillRequest);
		if ($existedSkill) {
			$skillRequest = $existedSkill->import($skillRequest);
		} else if (!$this->skillRequests->contains($skillRequest)) {
			$this->skillRequests->add($skillRequest);
		}
		$this->addSkillAsSetted($skillRequest);

		return $this;
	}

	private function addSkillAsSetted(SkillKnowRequest $skillRequest)
	{
		$this->initSettedSkillRequests();
		$this->settedSkillRequests->add($skillRequest);
		return $this;
	}

	private function initSettedSkillRequests()
	{
		if (!$this->settedSkillRequests) {
			$this->settedSkillRequests = new ArrayCollection;
		}
		return $this;
	}

	public function clearSkills()
	{
		$this->skillRequests->clear();
		$this->settedSkillRequests->clear();
		return $this;
	}

	public function removeOldSkillRequests()
	{
		$mapFunc = function (SkillKnowRequest $skillRequest) {
			if (!$this->settedSkillRequests->contains($skillRequest)) {
				$this->removeSkill($skillRequest);
			}
		};
		if ($this->settedSkillRequests) {
			$this->skillRequests->map($mapFunc);
		}
		return $this;
	}

	public function removeSkill(SkillKnowRequest $skillRequest)
	{
		$this->skillRequests->removeElement($skillRequest);
		return $this;
	}

	// </editor-fold>
	// <editor-fold defaultstate="collapsed" desc="getters">

	public function isNew()
	{
		return $this->id === NULL;
	}

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
