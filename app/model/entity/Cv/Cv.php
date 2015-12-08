<?php

namespace App\Model\Entity;

use App\Model\Entity\Traits\CvSkillsUsing;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Kdyby\Doctrine\Entities\Attributes\Identifier;
use Kdyby\Doctrine\Entities\BaseEntity;
use Nette\Utils\DateTime;

/**
 * @ORM\Entity(repositoryClass="App\Model\Repository\CvRepository")
 * @ORM\EntityListeners({"App\Listeners\Model\Entity\CvListener"})
 *
 * @property string $name
 * @property integer $lastOpenedPreviewPage Get last opened preview page
 * @property integer $lastUsedPreviewScale Get last used preview scale
 * @property boolean $isDefault
 * @property-read ArrayCollection $skillKnows
 * @property-write SkillKnow $skillKnow
 * @property Candidate $candidate
 * @property boolean $isPublic
 * @property string $theme
 * @property ArrayCollection $works
 * @property ArrayCollection $experiences
 * @property ArrayCollection $educations
 * @property Competences $competence
 * @property string $motherLanguage
 * @property ArrayCollection $languages
 * @property string $careerObjective
 * @property boolean $careerObjectiveIsPublic
 * @property string $careerSummary
 * @property boolean $careerSummaryIsPublic
 * @property string $desiredPosition
 * @property boolean $desiredEmploymentIsPublic
 * @property DateTime $availableFrom
 * @property integer $salaryFrom
 * @property integer $salaryTo
 * @property boolean $salaryIsPublic
 * @property string $additionalInfo
 */
class Cv extends BaseEntity
{

	use Identifier;
	use CvSkillsUsing;

	/** @ORM\Column(type="string", length=100, nullable=true) */
	protected $name;

	/** @ORM\Column(type="integer", nullable=true) */
	protected $lastStep;

	/** @ORM\Column(type="integer", nullable=true) */
	protected $lastOpenedPreviewPage;

	/** @ORM\Column(type="float", nullable=true) */
	protected $lastUsedPreviewScale;

	/** @ORM\Column(type="boolean", nullable=false) */
	protected $isDefault;

	/** @ORM\ManyToOne(targetEntity="Candidate", inversedBy="cvs") */
	protected $candidate;

	/** @ORM\Column(type="boolean", nullable=false) */
	protected $isPublic = FALSE;

	/** @ORM\Column(type="string", length=20, nullable=true) */
	protected $theme;

	/** @ORM\OneToMany(targetEntity="Work", mappedBy="cv", fetch="LAZY", cascade="all") */
	protected $works;

	/** @ORM\OneToMany(targetEntity="Work", mappedBy="cv", fetch="LAZY", cascade="all") */
	protected $experiences;

	/** @ORM\OneToMany(targetEntity="Education", mappedBy="cv", fetch="LAZY", cascade="all") */
	protected $educations;

	/** @ORM\OneToOne(targetEntity="Competences", cascade="all", fetch="LAZY") */
	protected $competence;

	/** @ORM\Column(type="string", length=20, nullable=true) */
	protected $motherLanguage;

	/** @ORM\OneToMany(targetEntity="Language", mappedBy="cv", fetch="LAZY", cascade="all") */
	protected $languages;

	/** @ORM\Column(type="text", nullable=true) */
	protected $careerObjective;

	/** @ORM\Column(type="boolean") */
	protected $careerObjectiveIsPublic = FALSE;

	/** @ORM\Column(type="text", nullable=true) */
	protected $careerSummary;

	/** @ORM\Column(type="boolean") */
	protected $careerSummaryIsPublic = FALSE;

	/** @ORM\Column(type="text", nullable=true) */
	protected $desiredPosition;

	/** @ORM\Column(type="boolean") */
	protected $desiredEmploymentIsPublic = FALSE;

	/** @ORM\Column(type="date", nullable=true) */
	protected $availableFrom;

	/** @ORM\Column(type="integer", nullable=true) */
	protected $salaryFrom;

	/** @ORM\Column(type="integer", nullable=true) */
	protected $salaryTo;

	/** @ORM\Column(type="boolean") */
	protected $salaryIsPublic = FALSE;

	/** @ORM\Column(type="text", nullable=true) */
	protected $additionalInfo;

	public function __construct($name = NULL)
	{
		if ($name) {
			$this->name = $name;
		}
		$this->skillKnows = new ArrayCollection();
		$this->works = new ArrayCollection();
		$this->experiences = new ArrayCollection();
		$this->educations = new ArrayCollection();
		parent::__construct();
	}

	public function setIsPublic($value = TRUE)
	{
		// TODO: set URL for public access
		return $this->isPublic = (bool) $value;
	}

	public function __toString()
	{
		return (string) $this->name;
	}
	
	public function addWork(Work $work)
	{
		if (!$work->id) {
			$this->works->add($work);
		}
		$work->cv = $this;
		return $this;
	}
	
	public function deleteWork(Work $work) {
		$this->works->removeElement($work);
		return $this;
	}
	
	public function deleteEducation(Education $edu) {
		$this->educations->removeElement($edu);
		return $this;
	}


	public function existsWorkId($id)
	{
		$exists = function ($key, Work $work) use ($id) {
			return $work->id === (int) $id;
		};
		return $this->works->exists($exists);
	}
	
	public function addExperience(Work $experience)
	{
		if (!$experience->id) {
			$this->experiences->add($experience);
		}
		$experience->cv = $this;
		return $this;
	}
	
	public function existsExperienceId($id)
	{
		$exists = function ($key, Work $experience) use ($id) {
			return $experience->id === (int) $id;
		};
		return $this->experiences->exists($exists);
	}
	
	public function addEducation(Education $education)
	{
		if (!$education->id) {
			$this->educations->add($education);
		}
		$education->cv = $this;
		return $this;
	}
	
	public function existsEducationId($id)
	{
		$exists = function ($key, Education $education) use ($id) {
			return $education->id === (int) $id;
		};
		return $this->educations->exists($exists);
	}
	
	public function addLanguage(Language $language)
	{
		if (!$language->id) {
			$this->languages->add($language);
		}
		$language->cv = $this;
		return $this;
	}
	
	public function existsLanguageId($id)
	{
		$exists = function ($key, Language $language) use ($id) {
			return $language->id === (int) $id;
		};
		return $this->languages->exists($exists);
	}
	
	public function getMotherLanguageName()
	{
		return Language::getLanguageNameById($this->motherLanguage);
	}

}
