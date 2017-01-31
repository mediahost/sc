<?php

namespace App\Model\Entity;

use App\Helpers;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Kdyby\Doctrine\Entities\Attributes\Identifier;
use Kdyby\Doctrine\Entities\BaseEntity;
use Knp\DoctrineBehaviors\Model;
use Nette\Utils\DateTime;

/**
 * @ORM\Entity(repositoryClass="App\Model\Repository\CommunicationRepository")
 * @property string $subject
 * @property ArrayCollection $messages
 * @property Message $lastMessage
 * @property Job $job
 * @property Candidate $candidate
 * @property DateTime $createdAt
 * @property DateTime $updatedAt
 */
class Communication extends BaseEntity
{

	use Identifier;
	use Model\Timestampable\Timestampable;

	/** @ORM\Column(type="string", length=512, nullable=true) */
	protected $subject;

	/** @ORM\ManyToMany(targetEntity="Sender", inversedBy="communication", cascade={"persist"}, orphanRemoval=true) */
	private $contributors;

	/** @ORM\Column(type="smallint") */
	private $contributorsCount = 0;

	/** @ORM\Column(type="simple_array") */
	private $contributorsArray = [];

	/** @ORM\OneToMany(targetEntity="Notification", mappedBy="communication") */
	protected $notifications;

	/**
	 * @ORM\OneToMany(targetEntity="Message", mappedBy="communication", cascade={"persist", "remove"}, orphanRemoval=true)
	 * @ORM\OrderBy({"id" = "ASC"})
	 */
	protected $messages;

	/** @ORM\ManyToOne(targetEntity="Candidate") */
	protected $candidate;

	/** @ORM\ManyToOne(targetEntity="Job") */
	protected $job;

	public function __construct(array $contributors)
	{
		$this->messages = new ArrayCollection();
		$this->contributors = new ArrayCollection();
		parent::__construct();
		foreach ($contributors as $contributor) {
			$this->addContributor($contributor);
		}
	}

	public function addContributor(Sender $sender)
	{
		if (!$this->isContributor($sender)) {
			$this->contributors->add($sender);
		}
		$this->recountContributors();
		return $this;
	}

	private function recountContributors()
	{
		$this->contributorsCount = $this->contributors->count();
		$this->contributorsArray = [];
		foreach ($this->contributors as $contributor) {
			$this->contributorsArray[] = $contributor->id;
		}
		return $this;
	}

	public function isContributor(Sender $sender)
	{
		$isContributor = function ($key, Sender $contributor) use ($sender) {
			return $contributor->id === $sender->id;
		};
		return $this->contributors->exists($isContributor);
	}

	public function removeContributor(Sender $sender)
	{
		$this->contributors->removeElement($sender);
		$this->recountContributors();
	}

	public function getContributors(Sender $me = NULL)
	{
		if (!$me) {
			return $this->contributors;
		}
		$contributors = new ArrayCollection();
		$getContributors = function ($key, Sender $sender) use ($contributors, $me) {
			if ($me->id !== $sender->id) {
				$contributors->add($sender);
			}
			return TRUE;
		};
		$this->contributors->forAll($getContributors);
		return $contributors;
	}

	public function getContributorsName(Sender $me = NULL)
	{
		$names = NULL;
		$concatNames = function ($key, Sender $sender) use (&$names) {
			$names = Helpers::concatStrings(', ', $names, (string)$sender);
			return TRUE;
		};
		$this->getContributors($me)->forAll($concatNames);
		return $names;
	}

	public function markMessagesAsRead(Sender $reader)
	{
		$markAsRead = function ($key, Message $message) use ($reader) {
			if (!$message->isReadBy($reader)) {
				$message->addRead(new Read($reader));
			}
			return TRUE;
		};
		return $this->messages->forAll($markAsRead);
	}

	public function addMessage(Sender $sender, $text, $state = Message::STATE_DEFAULT)
	{
		$message = new Message($sender, $text, $state);
		$message->communication = $this;
		$this->addContributor($sender);
		$this->messages->add($message);
	}

	public function removeMessage(Message $message)
	{
		$this->messages->removeElement($message);
	}

	public function getLastMessage()
	{
		return $this->messages->last();
	}

	public function getUnreadCount(Sender $sender)
	{
		$unread = 0;
		$countUnread = function ($key, Message $message) use (&$unread, $sender) {
			if (!$message->isReadBy($sender)) {
				$unread++;
			}
			return TRUE;
		};
		$this->messages->forAll($countUnread);
		return $unread;
	}

}