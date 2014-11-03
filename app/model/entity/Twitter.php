<?php

namespace App\Model\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * 
 * @property string $accessToken
 */
class Twitter extends OAuth
{

	/**
	 * @ORM\OneToOne(targetEntity="User", inversedBy="twitter", fetch="LAZY")
	 * @ORM\JoinColumn(name="user_id", referencedColumnName="id", nullable=false, onDelete="CASCADE")
	 */
	protected $user;
	
	/** @ORM\Column(type="string", length=512, nullable=true) */
	protected $accessToken;
}
