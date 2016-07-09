<?php

namespace App\Model\Facade\Traits;
use App\Model\Entity\Job;
use App\Model\Entity\Cv;
use App\Model\Entity\JobCv;

trait JobMatchingFacade {
    
    public function invite(Job $job, Cv $cv) {
        $jobCvRepo = $this->em->getRepository(JobCv::getClassName());
        $entity = new JobCv();
        $entity->job = $job;
        $entity->cv = $cv;
        $jobCvRepo->save($entity);
    }
}

