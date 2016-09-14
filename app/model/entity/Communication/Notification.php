<?php

namespace App\Model\Entity;

use Doctrine\ORM\Mapping as ORM;
use Kdyby\Doctrine\Entities\BaseEntity;

/**
 * @ORM\Entity
 * @ORM\Table(name="message_notification")
 * @property Communication $communication
 * @property Sender $sender
 * @property bool $enabled
 */
class Notification extends BaseEntity
{

	/**
	 * @ORM\Id
	 * @ORM\ManyToOne(targetEntity="Communication", inversedBy="notifications")
	 */
	protected $communication;

	/**
	 * @ORM\Id
	 * @ORM\ManyToOne(targetEntity="Sender", inversedBy="notifications")
	 */
	protected $sender;

	/** @ORM\Column(type="boolean") */
	protected $enabled;

}