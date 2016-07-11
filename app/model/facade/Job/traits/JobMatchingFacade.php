<?php

namespace App\Model\Facade\Traits;
use App\Model\Entity\Job;
use App\Model\Entity\Cv;
use App\Model\Entity\JobCv;

trait JobMatchingFacade {
    
    public function invite(Job $job, Cv $cv) {
        if(!$job->hasMatchedCv($cv->id)) {
            $entity = new JobCv();
            $entity->job = $job;
            $entity->cv = $cv;
            $entity->state = JobCv::CV_STATE_INVITED;
            $job->setStateEntity($entity);
        } else {
            $entity = $job->getStateEntity($cv->id);
            $entity->state = ($entity->state == JobCv::CV_STATE_APLLIED) 
                ? JobCv::CV_STATE_MATCHED : JobCv::CV_STATE_INVITED;
        }
        $this->em->persist($job);
		$this->em->flush();
    }
    
    public function detach(Job $job, $cvId) {
        $job->removeStateEntity($cvId);
        $this->em->persist($job);
		$this->em->flush();
    }
}

