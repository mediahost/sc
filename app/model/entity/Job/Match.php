<?php

namespace App\Model\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Kdyby\Doctrine\Entities\Attributes\Identifier;
use Kdyby\Doctrine\Entities\BaseEntity;
use Nette\Utils\DateTime;
use Nette\Utils\Strings;

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
 * @property string $acceptReason
 * @property-read bool $matched
 * @property-read bool $matchedOnly
 * @property-read bool $matchedNext
 * @property-read bool $rejected
 * @property-read bool $accepted
 * @property-read bool $acceptedOnly
 * @property int $state
 * @property string $customState
 * @property-read ArrayCollection $adminNotes
 * @property-read ArrayCollection $companyNotes
 */
class Match extends BaseEntity
{

	const STATE_INVITED = 'invited';
	const STATE_INVITED_ONLY = 'invitedOnly';
	const STATE_APPLIED = 'applied';
	const STATE_APPLIED_ONLY = 'appliedOnly';
	const STATE_APPROVED = 'approved';
	const STATE_MATCHED = 'matched';
	const STATE_MATCHED_ONLY = 'matchedOnly';
	const STATE_REJECTED = 'rejected';
	const STATE_ACCEPTED = 'accepted';
	const STATE_ACCEPTED_ONLY = 'acceptedOnly';
	const STATE_INVITED_FOR_IV = 1;
	const STATE_COMPLETE_IV = 2;
	const STATE_OFFERED = 3;
	const STATE_CUSTOM = 10;

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
	private $accept;

	/** @ORM\Column(type="datetime", nullable=true) */
	private $acceptChangedAt;

	/** @ORM\Column(type="text", nullable=true) */
	protected $acceptReason;

	/** @ORM\Column(type="smallint", nullable=true) */
	private $state;

	/** @ORM\Column(type="datetime", nullable=true) */
	private $stateChangedAt;

	/** @ORM\Column(type="string", length=64, nullable=true) */
	private $customStateName;

	/** @ORM\OneToMany(targetEntity="Note", mappedBy="match", cascade="all") */
	private $notes;

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

	public function setAccept($value)
	{
		$this->accept = $value;
		$this->acceptChangedAt = new DateTime();
		return $this;
	}

	public function setState($value)
	{
		switch ($value) {
			case self::STATE_INVITED_FOR_IV:
			case self::STATE_COMPLETE_IV:
			case self::STATE_OFFERED:
				$this->state = $value;
				$this->customStateName = NULL;
				break;
			default:
				$this->state = self::STATE_CUSTOM;
				$this->customStateName = $value;
				break;
		}
		$this->stateChangedAt = new DateTime();
		return $this;
	}

	public function setCustomState($value)
	{
		$this->customStateName = $value;
		return $this;
	}

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

	public function getAdminApprovedAt()
	{
		return $this->adminApprovedAt;
	}

	public function getCandidateApprovedAt()
	{
		return $this->candidateApprovedAt;
	}

	public function getAccept()
	{
		return $this->accept;
	}

	public function getAcceptChangedAt()
	{
		return $this->acceptChangedAt;
	}

	public function getFullApprove()
	{
		return $this->candidateApprove && $this->adminApprove;
	}

	public function getFullApprovedAt()
	{
		return $this->candidateApprovedAt > $this->adminApprovedAt ? $this->candidateApprovedAt : $this->adminApprovedAt;
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

	public function getState()
	{
		return $this->state;
	}

	public function getCustomState()
	{
		return $this->customStateName;
	}

	public function getStateChangedAt()
	{
		return $this->stateChangedAt;
	}

	public function getInState($state)
	{
		return $this->getAccepted() && $this->state === $state;
	}

	public function getTotalStateName($truncate = 15)
	{
		if ($this->state) {
			switch ($this->state) {
				case self::STATE_CUSTOM:
					return Strings::truncate($this->customStateName, $truncate);
				default:
					return self::getStateName($this->state);
			}
		} else if ($this->accept !== NULL) {
			return self::getStateName($this->accept ? self::STATE_ACCEPTED_ONLY : self::STATE_REJECTED);
		} else if ($this->fullApprove) {
			return self::getStateName(self::STATE_MATCHED_ONLY);
		} else if ($this->adminApprove) {
			return self::getStateName(self::STATE_INVITED_ONLY);
		} else if ($this->candidateApprove) {
			return self::getStateName(self::STATE_APPLIED_ONLY);
		} else {
			return NULL;
		}
	}

	public function getTotalStateTime()
	{
		if ($this->state) {
			return $this->stateChangedAt;
		} else if ($this->accept !== NULL) {
			return $this->acceptChangedAt;
		} else if ($this->fullApprove) {
			return $this->getFullApprovedAt();
		} else if ($this->adminApprove) {
			return $this->adminApprovedAt;
		} else if ($this->candidateApprove) {
			return $this->candidateApprovedAt;
		} else {
			return NULL;
		}
	}

	public static function getStates()
	{
		return [
			self::STATE_INVITED_FOR_IV => 'Invited for IV',
			self::STATE_COMPLETE_IV => 'IV process completed',
			self::STATE_OFFERED => 'Offer made',
		];
	}

	public static function getStateName($state)
	{
		$states = [
			Match::STATE_APPLIED_ONLY => 'Requested',
			Match::STATE_INVITED_ONLY => 'Invited',
			Match::STATE_MATCHED_ONLY => 'Applied',
			Match::STATE_REJECTED => 'Rejected',
			Match::STATE_ACCEPTED_ONLY => 'Shortlisted',
			Match::STATE_INVITED_FOR_IV => 'Invited for IV',
			Match::STATE_COMPLETE_IV => 'Completed IV',
			Match::STATE_OFFERED => 'Offered',
		];
		if (is_array($state)) {
			$newStates = [];
			foreach ($state as $key => $item) {
				$newStates[$item] = array_key_exists($item, $states) ? $states[$item] : $item;
			}
			return $newStates;
		} else {
			return array_key_exists($state, $states) ? $states[$state] : NULL;
		}
	}

	public static function isAcceptedState($state)
	{
		$accptedStates = [
			self::STATE_APPLIED,
			self::STATE_APPLIED_ONLY,
			self::STATE_INVITED,
			self::STATE_INVITED_ONLY,
			self::STATE_APPROVED,
			self::STATE_MATCHED,
			self::STATE_MATCHED_ONLY,
			self::STATE_REJECTED,
			self::STATE_ACCEPTED,
			self::STATE_ACCEPTED_ONLY,
			self::STATE_INVITED_FOR_IV,
			self::STATE_COMPLETE_IV,
			self::STATE_OFFERED,
			self::STATE_CUSTOM,
		];
		return in_array($state, $accptedStates);
	}

}
