<?php

namespace App\Model\Facade\Traits;

trait JobCategoryFacade {
    
    
    public function findCategories() {
        $jobCategorydao = $this->em->getDao(\App\Model\Entity\JobCategory::getClassName());
        return $jobCategorydao->findAll();
    }
    
    public function findTopCategories() {
        $jobCategorydao = $this->em->getDao(\App\Model\Entity\JobCategory::getClassName());
        return $jobCategorydao->findBy([
				'parent' => NULL,
		]);
    }
    
    public function findCategoriesPairs() {
        $jobCategorydao = $this->em->getDao(\App\Model\Entity\JobCategory::getClassName());
        return $jobCategorydao->findPairs('name');
    }
    
    public function findJobCategory($id) {
        $jobCategorydao = $this->em->getDao(\App\Model\Entity\JobCategory::getClassName());
        return $jobCategorydao->find($id);
    }
    
    public function saveJobCategory(\App\Model\Entity\JobCategory $category) {
        $this->em->persist($category);
		$this->em->flush();
    }
    
    public function deleteJobCategory(\App\Model\Entity\JobCategory $category) {
        $jobCategorydao = $this->em->getDao(\App\Model\Entity\JobCategory::getClassName());
        $jobCategorydao->delete($category);
    }
}
