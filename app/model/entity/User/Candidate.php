<?php

namespace App\Model\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Kdyby\Doctrine\Entities\Attributes\Identifier;
use Kdyby\Doctrine\Entities\BaseEntity;
use Nette\Http\FileUpload;
use Nette\Utils\DateTime;
use Nette\Utils\Strings;

/**
 * @ORM\Entity
 *
 * @property bool $freelancer
 * @property Cv[] $cvs
 * @property Document[] $documents
 * @property User $user
 */
class Candidate extends Person
{

	/** @ORM\Column(type="boolean") */
	protected $freelancer = FALSE;

	/** @ORM\Column(type="array") */
	protected $workLocations;

	/** @ORM\Column(type="array") */
	protected $jobCategories;

	/** @ORM\OneToMany(targetEntity="Cv", mappedBy="candidate", fetch="EAGER", cascade={"persist", "remove"}) */
	protected $cvs;

	/** @ORM\OneToMany(targetEntity="Document", mappedBy="candidate", fetch="EAGER", cascade={"persist", "remove"}) */
	protected $documents;

	public function __construct($name = NULL)
	{
		$this->workLocations = [];
		$this->jobCategories = [];
		$this->cvs = new ArrayCollection();
		$this->documents = new ArrayCollection();
		parent::__construct($name);
	}

	public function __toString()
	{
		return parent::__toString();
	}

	public function hasDefaultCv()
	{
		$isDefault = function (Cv $cv) {
			return $cv->isDefault;
		};
		return $this->cvs->exists($isDefault);
	}

	public function getDefaultCv()
	{
		foreach ($this->cvs as $cv) {
			if ($cv->isDefault) {
				return $cv;
			}
		}
		throw new EntityException('Candidate has no default CV');
	}

	public function isRequiredPersonalFilled()
	{
		return $this->isFilled();
	}

	public function isRequiredOtherFilled()
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
