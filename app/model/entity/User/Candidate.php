<?php

namespace App\Model\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Kdyby\Doctrine\Entities\Attributes\Identifier;
use Kdyby\Doctrine\Entities\BaseEntity;

/**
 * @ORM\Entity
 *
 * @property Person $person
 * @property Cv $cv
 * @property bool $freelancer
 * @property Document[] $documents
 */
class Candidate extends BaseEntity
{

	use Identifier;

	/** @ORM\OneToOne(targetEntity="Person", mappedBy="candidate", fetch="LAZY") */
	protected $person;

	/** @ORM\OneToOne(targetEntity="Cv", mappedBy="candidate", fetch="EAGER", cascade={"persist", "remove"}) */
	protected $cv;

	/** @ORM\Column(type="boolean") */
	protected $freelancer = FALSE;

	/** @ORM\Column(type="array") */
	protected $workLocations;

	/** @ORM\Column(type="array") */
	protected $jobCategories;

	/** @ORM\OneToMany(targetEntity="Document", mappedBy="candidate", fetch="EAGER", cascade={"persist", "remove"}) */
	protected $documents;

	/** @ORM\Column(type="string", length=64, nullable=true) */
	protected $tags;

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
		return count($this->workLocations) && count($this->jobCategories);
	}

	public function addDocument($document)
	{
		$this->documents[] = $document;
	}

	public function removeDocument($documentId)
	{
		foreach ($this->documents as $document) {
			if ($document->id == $documentId) {
				$this->documents->removeElement($document);
			}
		}
		return $this;
	}

	public function getDocuments()
	{
		return $this->documents;
	}

}
