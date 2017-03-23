<?php

namespace App\Model\Entity;

use Doctrine\ORM\Mapping as ORM;
use Kdyby\Doctrine\Entities\Attributes\Identifier;
use Kdyby\Doctrine\Entities\BaseEntity;
use Knp\DoctrineBehaviors\Model;

/**
 * @ORM\Entity
 *
 * @property User $user
 * @property int $type
 * @property string $typeFormated
 * @property Job $job
 */
class Action extends BaseEntity
{
	const TYPE_JOB_VIEW = 1;
	const TYPE_JOB_APPLY = 2;

	use Identifier;
	use Model\Timestampable\Timestampable;

	/** @ORM\ManyToOne(targetEntity="User") */
	protected $user;

	/** @ORM\Column(type="smallint") */
	protected $type;

	/** @ORM\ManyToOne(targetEntity="Job") */
	protected $job;

	public function __construct(User $user, $type)
	{
		parent::__construct();
		$this->user = $user;
		$this->type = $type;
	}

	public function getTypeFormated()
	{
		$names = self::getTypes();
		return array_key_exists($this->type, $names) ? $names[$this->type] : $this->type;
	}

	public function __toString()
	{
		return $this->user . '|' . $this->job . '|' . $this->createdAt->format('d.m.Y H:i:s');
	}

	public static function getTypes()
	{
		return [
			self::TYPE_JOB_VIEW => 'View',
			self::TYPE_JOB_APPLY => 'Apply',
		];
	}

}
