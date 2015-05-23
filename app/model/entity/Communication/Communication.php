<?php

namespace App\Model\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Kdyby\Doctrine\Entities\Attributes\Identifier;
use Kdyby\Doctrine\Entities\BaseEntity;
use Nette\Utils\DateTime;

/**
 * @ORM\Entity
 * @property string $subject
 * @property ArrayCollection $messages
 * @property DateTime $created
 * @property ArrayCollection $contributors
 */
class Communication extends BaseEntity
{

	use Identifier;

	/**
	 * @ORM\Column(type="string", length=512, nullable=true)
	 */
	protected $subject;

	/**
	 * @ORM\OneToMany(targetEntity="Message", mappedBy="communication", cascade={"persist"}, orphanRemoval=true)
	 * @ORM\OrderBy({"id" = "DESC"})
	 * @var Message[]
	 */
	protected $messages;

	/**
	 * @ORM\Column(type="datetime", nullable=false)
	 * @var DateTime
	 */
	protected $created;

	/**
	 * @ORM\OneToMany(targetEntity="Sender", mappedBy="communication", cascade={"persist"}, orphanRemoval=true)
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
	public function addContributor(Sender $sender)
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
	 * Get Sender with exact $user and $company
	 *
	 * @param User $user
	 * @return Sender|FALSE
	 */
	public function getContributor(User $user, Company $company = NULL)
	{
		/** @var Sender $sender */
		foreach ($this->contributors as $sender) {
			if ($sender->user && $sender->user->id == $user->id) {
				if ($company === NULL) {
					if ($sender->company === NULL) {
						return $sender;
					}
				} else {
					if ($sender->company && $sender->company->id == $company->id) {
						return $sender;
					}
				}
			}
		}
		return FALSE;
	}

	/**
	 * Is User or one of User Companies contributor?
	 *
	 * @param User $user
	 * @return bool
	 */
	public function isUserAllowed(User $user)
	{
		if ($this->isUserContributor($user)) {
		    return TRUE;
		}
		foreach ($user->getCompanies() as $company) {
			if ($this->isCompanyContributor($company)) {
				return TRUE;
			}
		}
		return FALSE;
	}

	/**
	 * TRUE only for Senders without Company
	 *
	 * @param User $user
	 * @return bool
	 */
	public function isUserContributor(User $user)
	{
		/** @var Sender $sender */
		foreach ($this->contributors as $sender) {
			if ($sender->user === $user && $sender->company === NULL) {
				return TRUE;
			}
		}
		return FALSE;
	}

	/**
	 * @param Company $company
	 * @return bool
	 */
	public function isCompanyContributor(Company $company)
	{
		/** @var Sender $sender */
		foreach ($this->contributors as $sender) {
			if ($sender->company === $company) {
				return TRUE;
			}
		}
		return FALSE;
	}

	/**
	 * @param Sender $contributor
	 * @return bool
	 */
	public function isContributor(Sender $contributor)
	{
		/** @var Sender $sender */
		foreach ($this->contributors as $sender) {
			if ($sender === $contributor) {
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