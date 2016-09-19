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
 * @property Sender $firstContributor
 * @property Message $lastMessage
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

	/** @ORM\OneToMany(targetEntity="Notification", mappedBy="communication") */
	protected $notifications;

	/**
	 * @ORM\OneToMany(targetEntity="Message", mappedBy="communication", cascade={"persist"}, orphanRemoval=true)
	 * @ORM\OrderBy({"id" = "ASC"})
	 */
	protected $messages;

	public function __construct(Sender $sender, Sender $reciever)
	{
		$this->messages = new ArrayCollection();
		$this->contributors = new ArrayCollection();
		parent::__construct();
		$this->addContributor($sender);
		$this->addContributor($reciever);
	}

	public function addContributor(Sender $sender)
	{
		$this->contributors->add($sender);
	}

	public function removeSender(Sender $sender)
	{
		$this->contributors->removeElement($sender);
	}

	public function getFirstContributor()
	{
		return $this->contributors->first();
	}

	public function getContributors()
	{
		return $this->contributors;
	}

	public function getOpposites(Sender $me)
	{
		$opposites = new ArrayCollection();
		$getOpposites = function ($key, Sender $sender) use ($opposites, $me) {
			if ($me->id !== $sender->id) {
				$opposites->add($sender);
			}
			return TRUE;
		};
		$this->contributors->forAll($getOpposites);
		return $opposites;
	}

	public function getOppositesName(Sender $me)
	{
		$opposites = $this->getOpposites($me);
		$names = NULL;
		$concatNames = function ($key, Sender $sender) use (&$names) {
			$names = Helpers::concatStrings(', ', $names, $sender->name);
			return TRUE;
		};
		$opposites->forAll($concatNames);
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

	public function addMessage(Sender $sender, $text)
	{
		$message = new Message($sender, $text);
		$message->communication = $this;
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

	public function isContributor(Sender $sender)
	{
		$isContributor = function ($key, Sender $contributor) use ($sender) {
			return $contributor->id === $sender->id;
		};
		return $this->contributors->exists($isContributor);
	}

}