<?php

namespace App\Model\Entity\Traits;

trait JobMatching
{
    /** @ORM\OneToMany(targetEntity="JobCv", mappedBy="job", fetch="LAZY", orphanRemoval=true, cascade={"persist", "remove"}) */
	protected $cvs;
    
    
    public function hasMatchedCv($cvId) {
        foreach ($this->cvs as $jobCv) {
            if ($jobCv->cv->id == $cvId) {
                return true;
            }
        }
        return false;
    }
    
    public function setStateEntity(\App\Model\Entity\JobCv $entity) {
        $this->cvs->add($entity);
    }
    
    public function getStateEntity($cvId) {
        foreach ($this->cvs as $jobCv) {
            if ($jobCv->cv->id == $cvId) {
                return $jobCv;
            }
        }
    }
    
    public function removeStateEntity($cvId) {
        foreach ($this->cvs as $jobCv) {
            if ($jobCv->cv->id == $cvId) {
                $this->cvs->removeElement($jobCv);
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


    public function getCvsState() {
        $result = [];
        foreach ($this->cvs as $jobCv) {
            $result[$jobCv->cv->id] = $jobCv->state;
        }
        return $result;
    }

}
