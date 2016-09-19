<?php

namespace App\Model\Entity;

use Doctrine\ORM\Mapping as ORM;
use Kdyby\Doctrine\Entities\Attributes\Identifier;
use Kdyby\Doctrine\Entities\BaseEntity;
use Nette\Utils\DateTime;

/**
 * @ORM\Entity
 * @ORM\Table(name="message_read")
 * @property Sender $sender
 * @property Message $message
 * @property DateTime $time
 */
class Read extends BaseEntity
{

	use Identifier;

	/** @ORM\ManyToOne(targetEntity="Message", inversedBy="reads") */
	protected $message;

	/** @ORM\ManyToOne(targetEntity="Sender") */
	protected $reader;

	/** @ORM\Column(type="datetime") */
	protected $time;

	public function __construct(Sender $reader = NULL)
	{
		$this->reader = $reader;
		$this->time = new DateTime();
		parent::__construct();
	}

}