<?php

namespace App\Model\Entity;

use Doctrine\ORM\Mapping as ORM;
use Kdyby\Doctrine\Entities\BaseEntity;

/**
 * @ORM\Entity
 *
 * @property User $user
 * @property string $name
 * @property DateTime $birthday
 */
class Candidate extends BaseEntity
{

	use \Kdyby\Doctrine\Entities\Attributes\Identifier;

	/**
	 * @ORM\OneToOne(targetEntity="User", inversedBy="candidate", fetch="LAZY")
	 * @ORM\JoinColumn(name="user_id", referencedColumnName="id", nullable=false, onDelete="CASCADE")
	 */
	protected $user;

	/** @ORM\Column(type="string", length=512, nullable=false) */
	protected $name;

	/** @ORM\Column(type="date", nullable=true) */
	protected $birthday;

	/** @return string */
	public function __toString()
	{
		return (string) $this->name;
	}

}
