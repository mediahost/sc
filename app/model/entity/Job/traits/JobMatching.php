<?php

namespace App\Model\Entity\Traits;

trait JobMatching
{
    /** @ORM\OneToMany(targetEntity="JobCv", mappedBy="job", fetch="LAZY", cascade={"persist"}) */
	protected $cvs;
    
    
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
