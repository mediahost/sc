<?php

namespace App\Model\Entity\Traits;

trait CvMatching
{
    /**  @ORM\OneToMany(targetEntity="JobCv", mappedBy="cv", fetch="LAZY", cascade={"persist"}) */
	private $jobs;
    
    
    public function getJobs() {
        $result = []; 
        foreach ($this->jobs as $jobCv) {
            $result[] = $jobCv->job;
        }
        return $result;
    }
}
