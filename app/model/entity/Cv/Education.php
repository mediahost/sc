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
 * @property Cv $cv
 */
class Education extends BaseEntity
{

	use Identifier;

	/** @ORM\ManyToOne(targetEntity="Cv", inversedBy="works") */
	protected $cv;

	/** @ORM\Column(type="string", length=200, nullable=true) */
	protected $institution;

	/** @ORM\Column(type="string", length=100, nullable=true) */
	protected $title;

	/** @ORM\Column(type="date", nullable=true) */
	protected $dateStart;

	/** @ORM\Column(type="date", nullable=true) */
	protected $dateEnd;

	/** @ORM\OneToOne(targetEntity="Address", cascade="all", fetch="LAZY") */
	protected $address;

	/** @ORM\Column(type="text", nullable=true) */
	protected $subjects;
	
	public function __construct()
	{
		parent::__construct();
	}

	public function __toString()
	{
		return $this->institution . ' - ' . $this->title;
	}

}
