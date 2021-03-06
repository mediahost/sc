<?php

namespace App\Model\Entity;

use App\Model\Entity\Traits\JobSkillsUsing;
use App\Model\Entity\Traits\JobTagsUsing;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Kdyby\Doctrine\Entities\Attributes\Identifier;
use Kdyby\Doctrine\Entities\BaseEntity;
use Knp\DoctrineBehaviors\Model;
use Tracy\Debugger;

/**
 * @ORM\Entity(repositoryClass="App\Model\Repository\JobRepository")
 *
 * @property Company $company
 * @property string $name
 * @property int $wordpressId
 * @property string $wordpressLink
 * @property string $wordpressImageLink
 * @property int $salaryFrom
 * @property int $salaryTo
 * @property string $description
 * @property string $summary
 * @property JobType $type
 * @property JobCategory $category
 * @property Location $location
 * @property-read ArrayCollection $skillRequests
 * @property-write SkillKnowRequest $skillRequest
 * @property-read ArrayCollection $tags
 * @property-write TagJob $tag
 * @property array $questions
 * @property ArrayCollection $matches
 * @property User $accountManager
 * @property User $companyAccountManager
 * @property-read Image $image
 */
class Job extends BaseEntity
{

	use Identifier;
	use Model\Timestampable\Timestampable;
	use JobSkillsUsing;
	use JobTagsUsing;

	/** @ORM\ManyToOne(targetEntity="Company", inversedBy="jobs") */
	protected $company;

	/** @ORM\ManyToOne(targetEntity="User") */
	protected $accountManager;

	/** @ORM\ManyToOne(targetEntity="User") */
	protected $companyAccountManager;

	/** @ORM\Column(type="string", length=512, nullable=false) */
	protected $name;

	/** @ORM\Column(type="integer", nullable=true) */
	protected $wordpressId;

	/** @ORM\Column(type="string", length=256) */
	protected $wordpressLink;

	/** @ORM\Column(type="string", length=256) */
	protected $wordpressImageLink;

	/** @ORM\Column(type="integer", nullable=true) */
	protected $salaryFrom;

	/** @ORM\Column(type="integer", nullable=true) */
	protected $salaryTo;

	/** @ORM\Column(type="text", nullable=true) */
	protected $description;

	/** @ORM\Column(type="text", nullable=true) */
	protected $summary;

	/** @ORM\ManyToOne(targetEntity="JobType") */
	protected $type;

	/** @ORM\ManyToOne(targetEntity="JobCategory") */
	protected $category;

	/** @ORM\ManyToOne(targetEntity="Location", cascade="all") */
	protected $location;

	/** @ORM\Column(type="array", nullable=true) */
	protected $questions;

	/** @ORM\Column(type="text", nullable=true) */
	protected $notes;

	/** @ORM\Column(type="datetime", nullable=true) */
	protected $notesUpdated;

	/** @ORM\OneToMany(targetEntity="Match", mappedBy="job") */
	protected $matches;

	/** @ORM\OneToMany(targetEntity="Communication", mappedBy="job") */
	protected $communications;

	public function __construct($name = NULL)
	{
		if ($name) {
			$this->name = $name;
		}
		$this->skillRequests = new ArrayCollection();
		$this->tags = new ArrayCollection();
		$this->matches = new ArrayCollection();
		parent::__construct();
	}

	public function __toString()
	{
		return (string)$this->name;
	}

	public function isNew()
	{
		return $this->id === NULL;
	}

	public function setWordpressLink($value)
	{
		$this->wordpressLink = $this->removeBaseUrl($value);
		return $this;
	}

	public function setWordpressImageLink($value)
	{
		$this->wordpressImageLink = $this->removeBaseUrl($value);
		return $this;
	}

	private function removeBaseUrl($value)
	{
		return preg_replace('/^\w+:\/\/[^\/]+/i', NULL, $value);
	}

	public function getImage()
	{
		return $this->company->logo;
	}

	public function getCompanyAccountManager($raw = FALSE)
	{
		if ($this->companyAccountManager) {
			return $this->companyAccountManager;
		} else if (!$raw) {
			return $this->company->delegate;
		}
		return NULL;
	}

	public function getAppliedCount()
	{
		$count = 0;
		$isMatch = function ($key, Match $match) use (&$count) {
			if ($match->candidateApprove && !$match->adminApprove) {
				$count++;
			}
			return TRUE;
		};
		$this->matches->forAll($isMatch);
		return $count;
	}

	public function getInvitedCount()
	{
		$count = 0;
		$isMatch = function ($key, Match $match) use (&$count) {
			if ($match->adminApprove && !$match->candidateApprove) {
				$count++;
			}
			return TRUE;
		};
		$this->matches->forAll($isMatch);
		return $count;
	}

	public function getMatchedCount($only = TRUE)
	{
		$count = 0;
		$isMatch = function ($key, Match $match) use (&$count, $only) {
			if ($only ? $match->matchedOnly : $match->matched) {
				$count++;
			}
			return TRUE;
		};
		$this->matches->forAll($isMatch);
		return $count;
	}

	public function getAcceptedCount($only = TRUE)
	{
		$count = 0;
		$isAccepted = function ($key, Match $match) use (&$count, $only) {
			if ($only ? $match->acceptedOnly : $match->accepted) {
				$count++;
			}
			return TRUE;
		};
		$this->matches->forAll($isAccepted);
		return $count;
	}

	public function getRejectedCount()
	{
		$count = 0;
		$isAccepted = function ($key, Match $match) use (&$count) {
			if ($match->rejected) {
				$count++;
			}
			return TRUE;
		};
		$this->matches->forAll($isAccepted);
		return $count;
	}

	public function getInStateCount($state)
	{
		$count = 0;
		$isInState = function ($key, Match $match) use (&$count, $state) {
			if ($match->getInState($state)) {
				$count++;
			}
			return TRUE;
		};
		$this->matches->forAll($isInState);
		return $count;
	}

}
