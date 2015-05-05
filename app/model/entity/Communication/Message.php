<?php

namespace App\Model\Entity;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\OneToMany;
use Kdyby\Doctrine\Entities\Attributes\Identifier;
use Kdyby\Doctrine\Entities\BaseEntity;
use Nette\Utils\DateTime;

/**
 * @Entity
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
	 * @ManyToOne(targetEntity="Sender")
	 * @var Sender
	 */
	protected $sender;

	/**
	 * @Column(type="datetime")
	 * @var DateTime
	 */
	protected $time;

	/**
	 * @OneToMany(targetEntity="Read", mappedBy="message", cascade={"persist"}, orphanRemoval=true)
	 * @var Read[]
	 */
	protected $reads;

	/**
	 * @ManyToOne(targetEntity="Communication", inversedBy="messages")
	 * @var Communication
	 */
	protected $communication;

	/**
	 * @Column(type="text")
	 * @var string
	 */
	protected $text;

	public function __construct()
	{
		$this->time = new DateTime();
		parent::__construct();
	}

	/**
	 * TODO: implement
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