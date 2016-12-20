<?php

namespace App\Model\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Kdyby\Doctrine\Entities\Attributes\Identifier;
use Kdyby\Doctrine\Entities\BaseEntity;
use Nette\Http\FileUpload;
use Nette\Utils\Random;

/**
 * @ORM\Entity(repositoryClass="App\Model\Repository\CandidateRepository")
 * @ORM\EntityListeners({"App\Listeners\Model\Entity\CandidateListener"})
 *
 * @property Person $person
 * @property Cv $cv
 * @property bool $freelancer
 * @property ArrayCollection $documents
 * @property ArrayCollection $jobCategories
 * @property FileUpload|string $cvFile
 * @property string $profileId
 */
class Candidate extends BaseEntity
{

	use Identifier;

	/** @ORM\OneToOne(targetEntity="Person", mappedBy="candidate", fetch="LAZY") */
	protected $person;

	/** @ORM\OneToOne(targetEntity="Cv", mappedBy="candidate", fetch="EAGER", cascade={"persist", "remove"}) */
	protected $cv;

	/** @ORM\Column(type="string", length=256, nullable=true) */
	protected $cvFile;

	/** @ORM\Column(type="boolean") */
	protected $freelancer = FALSE;

	/** @ORM\Column(type="array") */
	protected $workLocations;

	/** @ORM\ManyToMany(targetEntity="JobCategory") */
	protected $jobCategories;

	/** @ORM\OneToMany(targetEntity="Document", mappedBy="candidate", fetch="EAGER", cascade={"all"}) */
	protected $documents;

	/** @ORM\Column(type="string", length=64, nullable=true) */
	protected $tags;

	/** @ORM\OneToMany(targetEntity="Match", mappedBy="candidate") */
	protected $matches;

	/** @ORM\Column(type="string", length=64, nullable=true) */
	protected $profileId;

	public function __construct($name = NULL)
	{
		$this->workLocations = [];
		$this->jobCategories = new ArrayCollection();
		$this->documents = new ArrayCollection();
		$this->profileId = Random::generate(20);
		parent::__construct($name);
	}

	public function __toString()
	{
		return (string)$this->getPerson();
	}

	public function getCv()
	{
		if (!$this->cv) {
			$this->cv = new Cv();
			$this->cv->candidate = $this;
		}
		return $this->cv;
	}

	public function isFilled()
	{
		return TRUE;
	}

	public function isCompleted()
	{
		return $this->isFilled() && $this->person->isFilled();
	}

	public function isApplyable()
	{
		return (bool) $this->cvFile;
	}

	public function addDocument($document)
	{
		$this->documents->add($document);
		$document->setCandidate($this);
		return $this;
	}

	public function getDocuments()
	{
		return $this->documents;
	}

	public function addJobCategory(JobCategory $category)
	{
		$this->jobCategories->add($category);
		return $this;
	}

	public function clearJobCategories()
	{
		return $this->jobCategories->clear();
	}

	public function getJobCategories()
	{
		return $this->jobCategories;
	}

	public function getJobCategoriesIds()
	{
		$ids = [];
		$fillIds = function ($key, JobCategory $category) use (&$ids) {
			$ids[$category->id] = $category->id;
			return TRUE;
		};
		$this->jobCategories->forAll($fillIds);
		return $ids;
	}

	public function getJobCategoriesArray()
	{
		$ids = [];
		$fillIds = function ($key, JobCategory $category) use (&$ids) {
			$ids[$category->id] = (string)$category;
			return TRUE;
		};
		$this->jobCategories->forAll($fillIds);
		return $ids;
	}

	public function findMatch(Job $job)
	{
		foreach ($this->matches as $match) {
			if ($match->job->id == $job->id) {
				return $match;
			}
		}
		return null;
	}
}
