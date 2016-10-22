<?php

namespace App\Model\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Kdyby\Doctrine\Entities\Attributes\Identifier;
use Kdyby\Doctrine\Entities\BaseEntity;
use Nette\Http\FileUpload;

/**
 * @ORM\Entity(repositoryClass="App\Model\Repository\CandidateRepository")
 * @ORM\EntityListeners({"App\Listeners\Model\Entity\CandidateListener"})
 *
 * @property Person $person
 * @property Cv $cv
 * @property bool $freelancer
 * @property Document[] $documents
 * @property FileUpload|string $cvFile
 */
class Candidate extends BaseEntity
{

	use Identifier;

	/** @ORM\OneToOne(targetEntity="Person", mappedBy="candidate", fetch="LAZY") */
	protected $person;

	/** @ORM\OneToOne(targetEntity="Cv", mappedBy="candidate", fetch="EAGER", cascade={"persist", "remove"}) */
	protected $cv;

	/** @ORM\Column(type="string", length=128, nullable=true) */
	protected $cvFile;

	/** @ORM\Column(type="boolean") */
	protected $freelancer = FALSE;

	/** @ORM\Column(type="array") */
	protected $workLocations;

	/** @ORM\Column(type="array") */
	protected $jobCategories;

	/** @ORM\OneToMany(targetEntity="Document", mappedBy="candidate", fetch="EAGER", cascade={"all"}) */
	protected $documents;

	/** @ORM\Column(type="string", length=64, nullable=true) */
	protected $tags;

	/** @ORM\OneToMany(targetEntity="Match", mappedBy="candidate") */
	protected $matches;

	public function __construct($name = NULL)
	{
		$this->workLocations = [];
		$this->jobCategories = [];
		$this->documents = new ArrayCollection();
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
		return (bool)$this->cvFile;
	}

	public function addDocument($document)
	{
		$this->documents->add($document);
		$document->setCandidate($this);
	}

	public function getDocuments()
	{
		return $this->documents;
	}
}
