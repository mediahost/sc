<?php

namespace App\Model\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * User's personal settings
 * @ORM\Entity
 */
class UserSettings extends \Kdyby\Doctrine\Entities\BaseEntity
{

	use \Kdyby\Doctrine\Entities\Attributes\Identifier;

	// <editor-fold defaultstate="collapsed" desc="constants & variables">

	/**
	 * @ORM\OneToOne(targetEntity="User", inversedBy="settings", fetch="LAZY")
	 * @ORM\JoinColumn(name="user_id", referencedColumnName="id", nullable=false)
	 * */
	protected $user;

	/**
	 * @ORM\Column(type="string", length=2, nullable=true)
	 */
	protected $language;

	// </editor-fold>
	// <editor-fold defaultstate="collapsed" desc="setters">
	// </editor-fold>
	// <editor-fold defaultstate="collapsed" desc="getters">
	// </editor-fold>
}
