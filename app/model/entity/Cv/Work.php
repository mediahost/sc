<?php

namespace App\Model\Entity;

use Doctrine\ORM\Mapping as ORM;
use Kdyby\Doctrine\Entities\Attributes\Identifier;
use Kdyby\Doctrine\Entities\BaseEntity;
use Nette\Utils\DateTime;

/**
 * @ORM\Entity
 *
 * @property string $company
 * @property DateTime $dateStart
 * @property DateTime $dateEnd
 * @property string $position
 * @property string $activities
 * @property string $achievment
 * @property Referee $referee
 * @property boolean $refereeIsPublic
 * @property Cv $cv
 */
class Work extends BaseEntity
{

	use Identifier;

	/** @ORM\ManyToOne(targetEntity="Cv", inversedBy="works") */
	protected $cv;

	/** @ORM\Column(type="string", length=100, nullable=true) */
	protected $company;

	/** @ORM\Column(type="date", nullable=true) */
	protected $dateStart;

	/** @ORM\Column(type="date", nullable=true) */
	protected $dateEnd;

	/** @ORM\Column(type="string", length=255, nullable=true) */
	protected $position;

	/** @ORM\Column(type="text", nullable=true) */
	protected $activities;

	/** @ORM\Column(type="text", nullable=true) */
	protected $achievment;

	/** @ORM\OneToOne(targetEntity="Referee", cascade="all") */
	protected $referee;

	/** @ORM\Column(type="boolean") */
	protected $refereeIsPublic = FALSE;
	
	public function __construct()
	{
		parent::__construct();
	}

	public function __toString()
	{
		return $this->company . ' - ' . $this->position;
	}

}
