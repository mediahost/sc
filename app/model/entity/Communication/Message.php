<?php

namespace App\Model\Entity;

use Doctrine\ORM\Mapping as ORM;
use Kdyby\Doctrine\Entities\Attributes\Identifier;
use Kdyby\Doctrine\Entities\BaseEntity;
use Nette\Utils\DateTime;

/**
 * @ORM\Entity
 * @property Sender $sender
 * @property DateTime $time
 * @property Read[] $reads
 * @property Communication $communication
 * @property string $text
 */
class Message extends BaseEntity
{

	use Identifier;

	/**
	 * @ORM\ManyToOne(targetEntity="Sender")
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
		parent::__construct();
	}

	/**
	 * @param Sender $sender
	 * @return bool
	 */
	public function isReadsBy(Sender $sender)
	{
		foreach ($this->reads as $read) {
			if ($read->sender->id == $sender->id) {
			    return TRUE;
			}
		}
		return FALSE;
	}

}