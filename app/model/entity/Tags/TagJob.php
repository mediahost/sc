<?php

namespace App\Model\Entity;

use Doctrine\ORM\Mapping as ORM;
use Kdyby\Doctrine\Entities\Attributes\Identifier;

/**
 * @ORM\Entity
 *
 * @property Tag $tag
 * @property Job $job
 * @property int $type
 */
class TagJob extends \Kdyby\Doctrine\Entities\BaseEntity
{
	use Identifier;
	
	const TYPE_OFFERS = 1;
	const TYPE_REQUIREMENTS = 2;
	
	/**
	 * @ORM\ManyToOne(targetEntity="Tag")
	 * @ORM\JoinColumn(onDelete="CASCADE")
	 */
	protected $tag;
	
	/** 
	 * @ORM\ManyToOne(targetEntity="Job", inversedBy="tags")
	 * @ORM\JoinColumn(onDelete="CASCADE")
	 */
	protected $job;
	
	/** @ORM\Column(type="smallint", nullable=false) */
	protected $type;

	public function __toString()
	{
		return (string)$this->tag;
	}
}
