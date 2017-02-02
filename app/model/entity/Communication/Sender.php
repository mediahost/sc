<?php

namespace App\Model\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Kdyby\Doctrine\Entities\Attributes\Identifier;
use Kdyby\Doctrine\Entities\BaseEntity;

/**
 * @ORM\Entity
 * @property User $user
 * @property-read string $name
 * @property Company $company
 * @property ArrayCollection $communications
 * @property Communication $lastCommunication
 * @property bool|NULL $beNotified
 * @property bool $isCompany
 * @property-read int $unreadCount
 */
class Sender extends BaseEntity
{

	use Identifier;

	/** @ORM\ManyToOne(targetEntity="User") */
	protected $user;

	/** @ORM\ManyToOne(targetEntity="Company") */
	protected $company;

	/** @ORM\ManyToMany(targetEntity="Communication", mappedBy="contributors") */
	private $communications;

	/** @ORM\OneToMany(targetEntity="Notification", mappedBy="sender") */
	protected $notifications;

	/** @ORM\Column(type="boolean") */
	protected $beNotified = TRUE;

	public function __construct(User $user)
	{
		$this->communications = new ArrayCollection();
		parent::__construct();
		$this->user = $user;
	}

	public function getIsCompany()
	{
		return (bool)$this->company;
	}

	public function getPhoto()
	{
		if ($this->getIsCompany()) {
			return $this->company->logo;
		} else {
			return $this->user->person->photo;
		}
	}

	public function getName()
	{
		if ($this->company) {
			return $this->getUserName() . ' at ' . $this->company->name;
		} else {
			return $this->getUserName();
		}
	}

	public function getUserName()
	{
		if ($this->user->person && $this->user->person->fullName) {
			return $this->user->person->fullName;
		} else {
			return 'User #' . $this->user->id;
		}
	}

	public function getUnreadCount()
	{
		$unread = 0;
		$countUnread = function ($key, Communication $communication) use (&$unread) {
			if ($communication->getUnreadCount($this)) {
				$unread++;
			}
			return TRUE;
		};
		$this->communications->forAll($countUnread);
		return $unread;
	}

	public function getCommunications($order = TRUE)
	{
		if ($order) {
			$iterator = $this->communications->getIterator();
			@$iterator->uasort(function (Communication $a, Communication $b) {
				return ($a->lastMessage->createdAt > $b->lastMessage->createdAt) ? -1 : 1;
			});
			return new ArrayCollection(iterator_to_array($iterator));
		}
		return $this->communications;
	}

	public function getLastCommunication()
	{
		return $this->getCommunications()->first();
	}

	/**
	 * TODO: implement - vrací odkaz na veřejný profil společnosti nebo uživatele
	 */
	public function getPublicLink()
	{

	}

	public function __toString()
	{
		return $this->getName();
	}

}