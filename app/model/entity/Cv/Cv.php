<?php

namespace App\Model\Entity;

use App\Model\Entity\Traits\CvSkillsUsing;
use CvSkillKnowsTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Kdyby\Doctrine\Entities\Attributes\Identifier;
use Kdyby\Doctrine\Entities\BaseEntity;

/**
 * @ORM\Entity(repositoryClass="App\Model\Repository\CvRepository")
 *
 * @property string $name
 * @property integer $lastOpenedPreviewPage Get last opened preview page
 * @property integer $lastUsedPreviewScale Get last used preview scale
 * @property boolean $isDefault
 * @property-read ArrayCollection $skillKnows
 * @property-write SkillKnow $skillKnow
 * @property Candidate $candidate
 */
class Cv extends BaseEntity
{

	use Identifier;
	use CvSkillsUsing;

	/** @ORM\Column(type="string", length=100, nullable=true) */
	protected $name;

	/** @ORM\Column(type="integer", nullable=true) */
	protected $lastOpenedPreviewPage;

	/** @ORM\Column(type="float", nullable=true) */
	protected $lastUsedPreviewScale;

	/** @ORM\Column(type="boolean", nullable=false) */
	protected $isDefault;

	/** @ORM\ManyToOne(targetEntity="Candidate", inversedBy="cvs") */
	protected $candidate;

	public function __construct($name = NULL)
	{
		if ($name) {
			$this->name = $name;
		}
		$this->skillKnows = new ArrayCollection;
		parent::__construct();
	}

	public function __toString()
	{
		return (string) $this->name;
	}

}
