<?php

namespace App\Model\Entity;

use Doctrine\ORM\Mapping as ORM;
use Kdyby\Doctrine\Entities\Attributes\Identifier;
use Kdyby\Doctrine\Entities\BaseEntity;

/**
 * @ORM\Entity(repositoryClass="App\Model\Repository\SkillLevelRepository")
 *
 * @property string $name
 * @property int $priority
 * @property bool $first
 * @property bool $last
 * @property bool $relevant
 */
class SkillLevel extends BaseEntity
{

	const NONE = 0;
	const FIRST_PRIORITY = 1;
	const LAST_PRIORITY = 5;
	const NOT_DEFINED = 6;
	const IRELEVANT_PRIORITY = -1; //self::FIRST_PRIORITY;

	use Identifier;

	/** @ORM\Column(type="string") */
	protected $name;

	/** @ORM\Column(type="integer") */
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
		return (bool) ($this->priority === self::FIRST_PRIORITY);
	}

	public function isLast()
	{
		return (bool) ($this->priority === self::LAST_PRIORITY);
	}

	public function isRelevant()
	{
		return (bool) ($this->priority !== self::IRELEVANT_PRIORITY);
	}

	public function isNotDefined()
	{
		return (bool) ($this->priority === self::NOT_DEFINED);
	}

	public function isInRange(SkillLevel $from, SkillLevel $to)
	{
		return (bool) ($from->priority <= $this->priority && $this->priority <= $to->priority);
	}

}
