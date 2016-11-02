<?php

namespace App\Model\Entity;

use App\Model\Entity\Traits\JobSkillsUsing;
use App\Model\Entity\Traits\JobTagsUsing;
use App\Model\Entity\Traits\JobMatching;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Kdyby\Doctrine\Entities\Attributes\Identifier;
use Kdyby\Doctrine\Entities\BaseEntity;
use Nette\Utils\DateTime;

/**
 * @ORM\Entity(repositoryClass="App\Model\Repository\MatchRepository")
 * @ORM\Table(name="`match`")
 *
 * @property Candidate $candidate
 * @property Job $job
 * @property bool $adminApprove
 * @property bool $candidateApprove
 * @property bool $fullApprove
 * @property int $state
 */
class Match extends BaseEntity
{

	const STATE_REJECTED = 1;
	const STATE_FAVORITE = 2;

	use Identifier;

	public function __construct(Job $job, Candidate $candidate)
	{
		parent::__construct();
		$this->job = $job;
		$this->candidate = $candidate;
	}

	/** @ORM\ManyToOne(targetEntity="Candidate", inversedBy="matches") */
	protected $candidate;

	/** @ORM\ManyToOne(targetEntity="Job", inversedBy="matches") */
	protected $job;

	/** @ORM\Column(type="boolean") */
	protected $adminApprove = FALSE;

	/** @ORM\Column(type="datetime", nullable=true) */
	private $adminApprovedAt;

	/** @ORM\Column(type="boolean") */
	protected $candidateApprove = FALSE;

	/** @ORM\Column(type="datetime", nullable=true) */
	private $candidateApprovedAt;

	/** @ORM\Column(type="smallint", nullable=true) */
	protected $state;

	public function setAdminApprove($value)
	{
		$this->adminApprove = $value;
		$this->adminApprovedAt = new DateTime();
		return $this;
	}

	public function setCandidateApprove($value)
	{
		$this->candidateApprove = $value;
		$this->candidateApprovedAt = new DateTime();
		return $this;
	}

	public function getFullApprove()
	{
		return $this->candidateApprove && $this->adminApprove;
	}

	public function getAdminApprovedAt()
	{
		return $this->adminApprovedAt;
	}

	public function getCandidateApprovedAt()
	{
		return $this->candidateApprovedAt;
	}

}
