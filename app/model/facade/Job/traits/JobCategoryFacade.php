<?php

namespace App\Model\Facade\Traits;

trait JobCategoryFacade {
    
    
    public function findCategories() {
        $jobCategorydao = $this->em->getDao(\App\Model\Entity\JobCategory::getClassName());
        return $jobCategorydao->findAll();
    }
    
    public function findParentCategories() {
        $jobCategorydao = $this->em->getDao(\App\Model\Entity\JobCategory::getClassName());
        return $jobCategorydao->findPairs('name');
    }
    
    public function findJobCategory($id) {
        $jobCategorydao = $this->em->getDao(\App\Model\Entity\JobCategory::getClassName());
        return $jobCategorydao->find($id);
    }
    
    public function saveJObCategory(\App\Model\Entity\JobCategory $category) {
        $this->em->persist($category);
		$this->em->flush();
    }
}
