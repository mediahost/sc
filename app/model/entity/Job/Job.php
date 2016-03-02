<?php

namespace App\Model\Entity;

use App\Model\Entity\Traits\JobSkillsUsing;
use App\Model\Entity\Traits\JobTagsUsing;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Kdyby\Doctrine\Entities\Attributes\Identifier;
use Kdyby\Doctrine\Entities\BaseEntity;

/**
 * @ORM\Entity(repositoryClass="App\Model\Repository\JobRepository")
 *
 * @property Company $company
 * @property string $name
 * @property integer $salaryFrom
 * @property integer $salaryTo
 * @property string $description
 * @property string $summary
 * @property JobType $type
 * @property JobCategory $category
 * @property Location $location 
 * @property-read ArrayCollection $skillRequests
 * @property-write SkillKnowRequest $skillRequest
 * @property-read ArrayCollection $tags
 * @property-write TagJob $tag
 * @property string $questions
 */
class Job extends BaseEntity
{

	use Identifier;
	use JobSkillsUsing;
	use JobTagsUsing;
	

	/** @ORM\ManyToOne(targetEntity="Company", inversedBy="jobs") */
	protected $company;

	/** @ORM\Column(type="string", length=512, nullable=false) */
	protected $name;
	
	/** @ORM\Column(type="integer", nullable=true) */
	protected $salaryFrom;

	/** @ORM\Column(type="integer", nullable=true) */
	protected $salaryTo;

	/** @ORM\Column(type="text", nullable=true) */
	protected $description;
	
	/** @ORM\Column(type="text", nullable=true) */
	protected $summary;
	
	/** @ORM\OneToOne(targetEntity="JobType") */
	protected $type;
	
	/** @ORM\OneToOne(targetEntity="JobCategory") */
	protected $category;
	
	/** @ORM\OneToOne(targetEntity="Location", cascade="all") */
	protected $location;
	
	/** @ORM\Column(type="text", nullable=true) */
	protected $questions;

	

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
