<?php

namespace App\Model\Entity;

use Doctrine\ORM\Mapping as ORM;
use Kdyby\Doctrine\Entities\Attributes\Identifier;
use Kdyby\Doctrine\Entities\BaseEntity;
use Nette\Utils\DateTime;

/**
 * @ORM\Entity
 * @ORM\Table(name="`read`")
 * @property Sender $sender
 * @property Message $message
 * @property DateTime $time
 */
class Read extends BaseEntity
{

	use Identifier;

	/**
	 * @ORM\ManyToOne(targetEntity="Message", inversedBy="reads")
	 * @var Message
	 */
	protected $message;

	/**
	 * @ORM\ManyToOne(targetEntity="Sender")
	 * @ORM\JoinColumn(onDelete="CASCADE")
	 * @var Sender
	 */
	protected $sender;

	/**
	 * @ORM\Column(type="date")
	 * @var DateTime
	 */
	protected $time;

	public function __construct(Sender $sender = NULL)
	{
		$this->sender = $sender;
		$this->time = new DateTime();
		parent::__construct();
	}

}