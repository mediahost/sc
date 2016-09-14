<?php

namespace App\Model\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Kdyby\Doctrine\Entities\Attributes\Identifier;
use Kdyby\Doctrine\Entities\BaseEntity;
use Nette\Utils\DateTime;
use Knp\DoctrineBehaviors\Model;

/**
 * @ORM\Entity
 * @property Sender $sender
 * @property ArrayCollection $reads
 * @property Communication $communication
 * @property string $text
 * @property DateTime $createdAt
 * @property DateTime $updatedAt
 */
class Message extends BaseEntity
{

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

	public function __construct(Sender $sender, $text)
	{
		$this->reads = new ArrayCollection();
		parent::__construct();
		$this->sender = $sender;
		$this->text = $text;
	}

	public function isReadBy(Sender $reader)
	{
		$isRead = function ($key, Read $read) use ($reader) {
			return $read->reader->id === $reader->id;
		};
		return $this->reads->exists($isRead);
	}

	public function addRead(Read $read)
	{
		$read->message = $this;
		$this->reads->add($read);
	}

}