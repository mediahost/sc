<?php

namespace App\Model\Entity;

use Doctrine\ORM\Mapping as ORM;
use Kdyby\Doctrine\Entities\Attributes\Identifier;

/**
 * @ORM\Entity
 * 
 * @property string $name
 */
class Tag extends \Kdyby\Doctrine\Entities\BaseEntity
{
	use Identifier;

	/** @ORM\Column(type="string", nullable=false) */
	protected $name;
	
	
	public function __construct($name = NULL)
	{
		if ($name) {
			$this->name = $name;
		}
		parent::__construct();
	}
	
	public function __toString()
	{
		return (string) $this->name;
	}
}
