<?php

namespace App\Model\Entity;

use Doctrine\ORM\Mapping as ORM;
use Kdyby\Doctrine\Entities\BaseEntity;

/**
 * @ORM\Entity(repositoryClass="App\Model\Repository\SkillLevelRepository")
 * 
 * @property string $name
 * @property int $priority
 */
class SkillLevel extends BaseEntity
{
	
	const IRELEVANT_ID = 1;

	use \Kdyby\Doctrine\Entities\Attributes\Identifier;

	/** @ORM\Column(type="string")*/
	protected $name;

	/** @ORM\Column(type="integer")*/
	protected $priority;

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
	
	public function isRelevant()
	{
		return (bool) ($this->id !== self::IRELEVANT_ID);
	}

}
