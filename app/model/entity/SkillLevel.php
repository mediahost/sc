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
	
	const FIRST_ID = 1; // TODO: do by facade
	const LAST_ID = 5; // TODO: do by facade
	const IRELEVANT_ID = self::FIRST_ID;

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
	
	public function isFirst()
	{
		return (bool) ($this->id === self::FIRST_ID);
	}
	
	public function isLast()
	{
		return (bool) ($this->id === self::LAST_ID);
	}
	
	public function isRelevant()
	{
		return (bool) ($this->id !== self::IRELEVANT_ID);
	}

}
