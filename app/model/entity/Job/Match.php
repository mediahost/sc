<?php

namespace App\Model\Entity;

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
 * @property bool $accept
 * @property-read bool $matched
 * @property-read bool $matchedOnly
 * @property-read bool $rejected
 * @property-read bool $accepted
 * @property-read bool $acceptedOnly
 * @property int $state
 * @property-read ArrayCollection $adminNotes
 * @property-read ArrayCollection $companyNotes
 */
class Match extends BaseEntity
{

	const STATE_APPROVED = 'approved';
	const STATE_MATCHED = 'matched';
	const STATE_MATCHED_ONLY = 'matchedOnly';
	const STATE_REJECTED = 'rejected';
	const STATE_ACCEPTED = 'accepted';
	const STATE_ACCEPTED_ONLY = 'acceptedOnly';
	const STATE_INVITED = 1;
	const STATE_COMPLETE = 2;
	const STATE_OFFERED = 3;

	use Identifier;

	public function __construct(Job $job, Candidate $candidate)
	{
		parent::__construct();
		$this->job = $job;
		$this->candidate = $candidate;
		$this->notes = new ArrayCollection();
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

	/** @ORM\Column(type="boolean", nullable=true) */
	protected $accept;

	/** @ORM\Column(type="smallint", nullable=true) */
	protected $state;

	/** @ORM\OneToMany(targetEntity="Note", mappedBy="match", cascade="all") */
	private $notes;

	public function getAdminNotes()
	{
		$filter = function (Note $note) {
			return $note->isAdmin();
		};
		return $this->notes->filter($filter);
	}

	public function getCompanyNotes()
	{
		$filter = function (Note $note) {
			return $note->isCompany();
		};
		return $this->notes->filter($filter);
	}

	public function addAdminNote(User $user, $text)
	{
		return $this->addNote($user, $text, Note::TYPE_ADMIN);
	}

	public function addCompanyNote(User $user, $text)
	{
		return $this->addNote($user, $text, Note::TYPE_COMPANY);
	}

	private function addNote(User $user, $text, $type)
	{
		$note = new Note();
		$note->match = $this;
		$note->user = $user;
		$note->text = $text;
		$note->type = $type;
		$this->notes->add($note);
		return $this;
	}

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

	public function getAdminApprovedAt()
	{
		return $this->adminApprovedAt;
	}

	public function getCandidateApprovedAt()
	{
		return $this->candidateApprovedAt;
	}

	public function getFullApprove()
	{
		return $this->candidateApprove && $this->adminApprove;
	}

	public function getMatched()
	{
		return $this->fullApprove;
	}

	public function getMatchedOnly()
	{
		return $this->getMatched() && $this->accept === NULL;
	}

	public function getAccepted()
	{
		return $this->getMatched() && $this->accept === TRUE;
	}

	public function getRejected()
	{
		return $this->getMatched() && $this->accept === FALSE;
	}

	public function getAcceptedOnly()
	{
		return $this->getAccepted() && $this->state === NULL;
	}

	public function getInState($state)
	{
		return $this->getAccepted() && $this->state === $state;
	}

	public static function getStates()
	{
		return [
			self::STATE_INVITED => 'Invited for IV',
			self::STATE_COMPLETE => 'IV process completed',
			self::STATE_OFFERED => 'Offer made',
		];
	}

	public static function isAcceptedState($state)
	{
		$accptedStates = [
			self::STATE_APPROVED,
			self::STATE_MATCHED,
			self::STATE_MATCHED_ONLY,
			self::STATE_REJECTED,
			self::STATE_ACCEPTED,
			self::STATE_ACCEPTED_ONLY,
			self::STATE_INVITED,
			self::STATE_COMPLETE,
			self::STATE_OFFERED,
		];
		return in_array($state, $accptedStates);
	}

}
