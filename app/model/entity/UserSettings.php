<?php

namespace App\Model\Entity;

use Doctrine\ORM\Mapping as ORM;
use Kdyby\Doctrine\Entities\BaseEntity;

/**
 * User's personal settings
 * @ORM\Entity
 * 
 * TODO: DELETE!!!
 */
class UserSettings extends BaseEntity
{

	use \Kdyby\Doctrine\Entities\Attributes\Identifier;

	/**
	 * @ORM\OneToOne(targetEntity="User", inversedBy="settings", fetch="LAZY")
	 * @ORM\JoinColumn(name="user_id", referencedColumnName="id", nullable=false, onDelete="CASCADE")
	 */
	protected $user;

	/** @ORM\Column(type="string", length=2, nullable=true) */
	protected $language;

}
