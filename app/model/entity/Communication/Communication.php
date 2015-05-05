<?php

namespace App\Model\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\OneToMany;
use Doctrine\ORM\Mapping\OrderBy;
use Kdyby\Doctrine\Entities\Attributes\Identifier;
use Kdyby\Doctrine\Entities\BaseEntity;
use Nette\Utils\DateTime;

/**
 * @Entity
 * @property string $subject
 * @property ArrayCollection $messages
 * @property DateTime $created
 * @property ArrayCollection $contributors
 */
class Communication extends BaseEntity
{

	use Identifier;

	/**
	 * @Column(type="string", length=512, nullable=true)
	 */
	protected $subject;

	/**
	 * @OneToMany(targetEntity="Message", mappedBy="communication", cascade={"persist"}, orphanRemoval=true)
	 * @OrderBy({"id" = "DESC"})
	 * @var Message[]
	 */
	protected $messages;

	/**
	 * @Column(type="datetime", nullable=false)
	 * @var DateTime
	 */
	protected $created;

	/**
	 * @OneToMany(targetEntity="Sender", mappedBy="communication", cascade={"persist"}, orphanRemoval=true)
	 * @var ArrayCollection
	 */
	protected $contributors;

	public function __construct()
	{
		$this->messages = new ArrayCollection();
		$this->contributors = new ArrayCollection();
		$this->created = new DateTime();
		parent::__construct();
	}

	/**
	 * @param Sender $sender
	 */
	public function addSender(Sender $sender)
	{
		$this->contributors->add($sender);
	}

	/**
	 * @param Sender $sender
	 */
	public function removeSender(Sender $sender)
	{
		$this->contributors->removeElement($sender);
	}

	/**
	 * @param User $user
	 * @return Sender|FALSE
	 */
	public function getSender(User $user)
	{
		/** @var Sender $sender */
		foreach ($this->contributors as $sender) {
			if ($sender->user->id == $user->id) {
			    return $sender;
			}
		}
		return FALSE;
	}

	public function isSender(User $user)
	{
		/** @var Sender $sender */
		foreach ($this->contributors as $sender) {
			if ($sender->user->id == $user->id) {
				return TRUE;
			}
		}
		return FALSE;
	}

	/**
	 * @param Message $message
	 */
	public function addMessage(Message $message)
	{
		$this->messages->add($message);
	}

	/**
	 * @param Message $message
	 */
	public function removeMessage(Message $message)
	{
		$this->messages->removeElement($message);
	}

	public function getOppositeName(User $user)
	{
		/** @var Sender $sender */
		foreach ($this->contributors as $sender) {
			if ($user->id != $sender->user->id) {
			    return $sender->getName();
			}
		}
		return '';
	}

	/**
	 * @return Message
	 */
	public function getLastMessage()
	{
		return $this->messages->first();
}

}