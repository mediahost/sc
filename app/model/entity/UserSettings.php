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

    /**
     * @ORM\OneToOne(targetEntity="User", inversedBy="settings", fetch="LAZY")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     **/
    protected $user;
	
	/**
	 * @ORM\Column(type="string", length=2)
	 */
	protected $language;
}
