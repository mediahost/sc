<?php

namespace App\Model\Facade\Traits;
use App\Model\Entity\Job;
use App\Model\Entity\Cv;
use App\Model\Entity\JobCv;

trait JobMatchingFacade {
    
    public function invite(Job $job, Cv $cv) {
        $jobCvRepo = $this->em->getRepository(JobCv::getClassName());
        if(!$job->hasMatchedCv($cv->id)) {
            $entity = new JobCv();
            $entity->job = $job;
            $entity->cv = $cv;
            $entity->state = JobCv::CV_STATE_INVITED;
        } else {
            $entity = $job->getStateEntity($cv->id);
            $entity->state = ($entity->state == JobCv::CV_STATE_APLLIED) 
                ? JobCv::CV_STATE_MATCHED : JobCv::CV_STATE_INVITED;
        }
        $jobCvRepo->save($entity);
    }
    
    public function detach(Job $job, $cvId) {
        $jobCvRepo = $this->em->getRepository(JobCv::getClassName());
        $entity = $job->getStateEntity($cvId);
        $jobCvRepo->delete($entity);
    }
}

