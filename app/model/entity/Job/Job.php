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
 * @property Collection $cvs 
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
	
	/** @ORM\ManyToOne(targetEntity="JobType") */
	protected $type;
	
	/** @ORM\ManyToOne(targetEntity="JobCategory") */
	protected $category;
	
	/** @ORM\ManyToOne(targetEntity="Location", cascade="all") */
	protected $location;
	
	/** @ORM\Column(type="text", nullable=true) */
	protected $questions;
    
    /** @ORM\OneToMany(targetEntity="JobCv", mappedBy="job", fetch="LAZY", cascade={"persist"}) */
	protected $cvs;

	

	public function __construct($name = NULL)
	{
		if ($name) {
			$this->name = $name;
		}
		$this->skillRequests = new ArrayCollection;
		$this->tags = new ArrayCollection;
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

    public function hasMatchedCv($cvId) {
        foreach ($this->cvs as $jobCv) {
            if ($jobCv->cv->id == $cvId) {
                return true;
            }
            return false;
        }
    }
    
    public function getStateEntity($cvId) {
        foreach ($this->cvs as $jobCv) {
            if ($jobCv->cv->id == $cvId) {
                return $jobCv;
            }
        }
    }
    
    public function getCvs() {
        $result = []; 
        foreach ($this->cvs as $jobCv) {
            $result[] = $jobCv->cv;
        }
        return $result;
    }
    
    public function removeCv($cvId) {
        foreach ($this->cvs as $jobCv) {
            if ($jobCv->id == $cvId) {
                $this->cvs->removeElement($jobCv);
            }
        }
    }


    public function getCvsState() {
        $result = [];
        foreach ($this->cvs as $jobCv) {
            $result[$jobCv->cv->id] = $jobCv->state;
        }
        return $result;
    }
}
