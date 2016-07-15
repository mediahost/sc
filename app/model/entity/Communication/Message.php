<?php

namespace App\Model\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Kdyby\Doctrine\Entities\Attributes\Identifier;
use Kdyby\Doctrine\Entities\BaseEntity;
use Nette\Utils\DateTime;

/**
 * @ORM\Entity
 * @property Sender $sender
 * @property DateTime $time
 * @property ArrayCollection|Read[] $reads
 * @property Communication $communication
 * @property string $text
 */
class Message extends BaseEntity
{

	use Identifier;

	/**
	 * @ORM\ManyToOne(targetEntity="Sender")
	 * @ORM\JoinColumn(onDelete="CASCADE")
	 * @var Sender
	 */
	protected $sender;

	/**
	 * @ORM\Column(type="datetime")
	 * @var DateTime
	 */
	protected $time;

	/**
	 * @ORM\OneToMany(targetEntity="Read", mappedBy="message", cascade={"persist"}, orphanRemoval=true)
	 * @var Read[]
	 */
	protected $reads;

	/**
	 * @ORM\ManyToOne(targetEntity="Communication", inversedBy="messages")
	 * @var Communication
	 */
	protected $communication;

	/**
	 * @ORM\Column(type="text")
	 * @var string
	 */
	protected $text;

	public function __construct()
	{
		$this->time = new DateTime();
		$this->reads = new ArrayCollection();
		parent::__construct();
	}

	/**
	 * @param Sender $sender
	 * @return bool
	 */
	public function isReadBySender(Sender $sender)
	{
		foreach ($this->reads as $read) {
			if ($read->sender->id == $sender->id) {
			    return TRUE;
			}
		}
		return FALSE;
	}

	/**
	 * @param User $user
	 * @return bool
	 */
	public function isReadByUser(User $user)
	{
		foreach ($this->reads as $read) {
			if ($read->sender->user && $read->sender->user->id == $user->id) {
				return TRUE;
			}
		}
		return FALSE;
	}

	/**
	 * @param Company $company
	 * @return bool
	 */
	public function isReadByCompany(Company $company)
	{
		foreach ($this->reads as $read) {
			if ($read->sender->company && $read->sender->company->id == $company->id) {
				return TRUE;
			}
		}
		return FALSE;
	}

	public function addRead(Read $read)
	{
		$read->message = $this;
		$this->reads->add($read);
	}

}