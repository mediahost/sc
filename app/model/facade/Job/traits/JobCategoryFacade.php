<?php

namespace App\Model\Facade\Traits;

trait JobCategoryFacade {
    
    
    public function findCategories() {
        $jobCategorydao = $this->em->getDao(\App\Model\Entity\JobCategory::getClassName());
        return $jobCategorydao->findAll();
    }
}
