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
 * @property string $placeIcon
 * @property-read string $placeLocation
 * @property float $lat
 * @property float $lng
 */
class Location extends BaseEntity
{
	use Identifier;
	
	const PLACE_TYPE_LOCALITY = 1;
	const PLACE_TYPE_ADMINISTRATIVE_1 = 2;
	const PLACE_TYPE_COUNTRY = 3;
	
	
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
		return (string) $this->placeName;
	}
	
	public function setPlaceLocation($location) 
	{
		$this->placeLocation = $location;
		sscanf($location, "(%f, %f)", $this->lat, $this->lng);
	}
	
	public function setPlaceType($type)
	{
		switch($type) {
			case 'locality': $this->placeType = self::PLACE_TYPE_LOCALITY; break;
			case 'administrative_area_level_1': $this->placeType = self::PLACE_TYPE_ADMINISTRATIVE_1; break;
			case 'country': $this->placeType = self::PLACE_TYPE_COUNTRY; break;
		}
	}
	
	public function getPlaceType()
	{
		switch($this->placeType) {
			case self::PLACE_TYPE_LOCALITY: return 'locality';
			case self::PLACE_TYPE_ADMINISTRATIVE_1: return 'administrative_area_level_1';
			case self::PLACE_TYPE_COUNTRY: return 'country';
		}
	}
}
