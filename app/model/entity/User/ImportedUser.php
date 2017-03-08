<?php

namespace App\Model\Entity;

use Doctrine\ORM\Mapping as ORM;
use Kdyby\Doctrine\Entities\Attributes\Identifier;
use Kdyby\Doctrine\Entities\BaseEntity;

/**
 * @ORM\Entity
 *
 * @property string $mail
 * @property string $firstname
 * @property string $surname
 * @property string $country
 * @property string $coreSkill
 * @property string $otherSkill
 * @property string $currentJob
 * @property string $linkedinLink
 * @property string $notes
 */
class ImportedUser extends BaseEntity
{
	use Identifier;

	/** @ORM\Column(type="string", nullable=false, unique=true) */
	protected $mail;

	/** @ORM\Column(type="string", length=150, nullable=true) */
	protected $firstname;

	/** @ORM\Column(type="string", length=150, nullable=true) */
	protected $surname;

	/** @ORM\Column(type="string", length=150, nullable=true) */
	protected $country;

	/** @ORM\Column(type="string", length=150, nullable=true) */
	protected $coreSkill;

	/** @ORM\Column(type="string", length=150, nullable=true) */
	protected $otherSkill;

	/** @ORM\Column(type="string", length=150, nullable=true) */
	protected $currentJob;

	/** @ORM\Column(type="string", length=256, nullable=true) */
	protected $linkedinLink;

	/** @ORM\Column(type="text", nullable=true) */
	protected $notes;

	public function __construct($mail)
	{
		$this->mail = $mail;
		parent::__construct();
	}

	public function __toString()
	{
		return (string)$this->mail;
	}

	public function isNew()
	{
		return $this->id === NULL;
	}

}
