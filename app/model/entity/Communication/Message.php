<?php

namespace App\Model\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Kdyby\Doctrine\Entities\Attributes\Identifier;
use Kdyby\Doctrine\Entities\BaseEntity;
use Knp\DoctrineBehaviors\Model;
use Nette\Utils\DateTime;

/**
 * @ORM\Entity
 * @ORM\EntityListeners({"App\Listeners\Model\Entity\MessageListener"})
 * @property Sender $sender
 * @property ArrayCollection $reads
 * @property Communication $communication
 * @property string $text
 * @property DateTime $createdAt
 * @property DateTime $updatedAt
 * @property int $state
 * @property Candidate $candidate
 * @property Job $job
 */
class Message extends BaseEntity
{

	const STATE_NORMAL = 1;
	const STATE_SYSTEM = 2;
	const STATE_DEFAULT = self::STATE_NORMAL;

	use Identifier;
	use Model\Timestampable\Timestampable;

	/** @ORM\ManyToOne(targetEntity="Sender") */
	protected $sender;

	/** @ORM\OneToMany(targetEntity="Read", mappedBy="message", cascade={"persist"}, orphanRemoval=true) */
	protected $reads;

	/** @ORM\ManyToOne(targetEntity="Communication", inversedBy="messages") */
	protected $communication;

	/** @ORM\Column(type="text") */
	protected $text;

	/** @ORM\Column(type="smallint", nullable=true) */
	protected $state;

	/** @ORM\ManyToOne(targetEntity="Candidate") */
	protected $candidate;

	/** @ORM\ManyToOne(targetEntity="Job") */
	protected $job;

	public function __construct(Sender $sender, $text, $state = self::STATE_DEFAULT)
	{
		$this->reads = new ArrayCollection();
		parent::__construct();
		$this->sender = $sender;
		$this->text = $text;
		$this->state = $state;
	}

	public function __toString()
	{
		return $this->text;
	}

	public function isReadBy(Sender $reader)
	{
		$isRead = function ($key, Read $read) use ($reader) {
			return $read->reader->id === $reader->id;
		};
		$isSender = $reader->id === $this->sender->id;
		return $isSender || $this->reads->exists($isRead);
	}

	public function addRead(Read $read)
	{
		$read->message = $this;
		$this->reads->add($read);
	}

	public function isSystem()
	{
		return $this->state === self::STATE_SYSTEM;
	}

}