<?php

namespace App\Model\Entity;

use Doctrine\ORM\Mapping as ORM;
use Kdyby\Doctrine\Entities\Attributes\Identifier;
use Kdyby\Doctrine\Entities\BaseEntity;


/**
 * @ORM\Entity
 * 
 * @property string $name
 */
class JobType extends BaseEntity
{
	use Identifier;
	
	/** @ORM\Column(type="string", length=64, nullable=false) */
	protected $name;
	
	
	public function __construct($name = NULL) {
		if ($name) {
			$this->name = $name;
		}
		parent::__construct();
	}
}
