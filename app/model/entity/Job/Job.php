<?php

namespace App\Model\Entity;

use App\Model\Entity\Traits\JobSkillsUsing;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Kdyby\Doctrine\Entities\Attributes\Identifier;
use Kdyby\Doctrine\Entities\BaseEntity;

/**
 * @ORM\Entity(repositoryClass="App\Model\Repository\JobRepository")
 *
 * @property Company $company
 * @property string $name
 * @property string $description
 * @property-read ArrayCollection $skillRequests
 * @property-write SkillKnowRequest $skillRequest
 */
class Job extends BaseEntity
{

	use Identifier;
	use JobSkillsUsing;

	/** @ORM\ManyToOne(targetEntity="Company", inversedBy="jobs") * */
	protected $company;

	/** @ORM\Column(type="string", length=512, nullable=false) */
	protected $name;

	/** @ORM\Column(type="text", nullable=true) */
	protected $description;

	public function __construct($name = NULL)
	{
		if ($name) {
			$this->name = $name;
		}
		$this->skillRequests = new ArrayCollection;
		parent::__construct();
	}

	public function __toString()
	{
		return (string) $this->name;
	}

	public function isNew()
	{
		return $this->id === NULL;
	}

}
