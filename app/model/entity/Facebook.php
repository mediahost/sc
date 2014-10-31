<?php

namespace App\Model\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * 
 * @property string $accessToken
 */
class Facebook extends OAuth
{
	
	/** @ORM\Column(type="string", length=512, nullable=true) */
	protected $accessToken;
}
