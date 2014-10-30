<?php

namespace App\Model\Entity;

use Doctrine\ORM\Mapping as ORM;

/** @ORM\Entity */
class Twitter extends OAuth
{
	
	/** @ORM\Column(type="string", length=512, nullable=true) */
	protected $accessToken;
}
