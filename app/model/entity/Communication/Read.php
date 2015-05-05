<?php

namespace App\Model\Entity;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\ManyToOne;
use Kdyby\Doctrine\Entities\Attributes\Identifier;
use Kdyby\Doctrine\Entities\BaseEntity;
use Nette\Utils\DateTime;

/**
 * @Entity
 * @property Sender $sender
 * @property Message $message
 * @property DateTime $time
 */
class Read extends BaseEntity
{

	use Identifier;

	/**
	 * @ManyToOne(targetEntity="Message", inversedBy="reads")
	 * @var Message
	 */
	protected $message;

	/**
	 * @ManyToOne(targetEntity="Sender")
	 * @var Sender
	 */
	protected $sender;

	/**
	 * @Column(type="date")
	 * @var DateTime
	 */
	protected $time;

}