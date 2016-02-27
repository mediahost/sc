<?php

namespace App\Model\Entity;

use Doctrine\ORM\Mapping as ORM;
use Kdyby\Doctrine\Entities\Attributes\Identifier;
use Kdyby\Doctrine\Entities\BaseEntity;

/**
 * @ORM\Entity
 * 
 * @property string $placeId
 * @property string $placeName
 * @property int $placeType
 * @property string $placeIcon
 * @property string $placeLocation
 * @property float $lat
 * @property float $lng
 */
class Location extends BaseEntity
{
	use Identifier;
	
	const PLACE_TYPE_LOCALITY = 1;
	const PLACE_TYPE_COUNTRY = 2;
	
	
	/** @ORM\Column(type="string", length=64, nullable=false) */
	protected $placeId;
	
	/** @ORM\Column(type="string", length=64, nullable=false) */
	protected $placeName;
	
	/** @ORM\Column(type="smallint", nullable=false) */
	protected $placeType;
	
	/** @ORM\Column(type="string", length=128, nullable=false) */
	protected $placeIcon;
	
	/** @ORM\Column(type="string", length=64, nullable=false) */
	protected $placeLocation;
	
	/** @ORM\Column(type="float", nullable=false) */
	protected $lat;
	
	/** @ORM\Column(type="float", nullable=false) */
	protected $lng;
	
	
	public function __toString()
	{
		return (string) $this->name;
	}
}
