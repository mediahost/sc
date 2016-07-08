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
    
    public function findCategoriesTree($categories=null) {
        $result = [];
        if(!$categories) {
            $categories = $this->findTopCategories();
        }
        foreach ($categories as $category) {
            if (count($category->childs)) {
                $result[$category->id] = $this->findCategoriesTree($category->childs);
            } else {
                $result[$category->id] = $category->name;
            }
        }
        return $result;
    }
    
    public function findCandidatePreferedCategories(\App\Model\Entity\Candidate $candidate) {
        $categories = [];
        $allCategories = $this->findCategoriesPairs();
        foreach ($allCategories as $id => $category) {
            if (in_array($id, $candidate->jobCategories)) {
                $categories[] = $category;
            }
        }
        return $categories;
    }
    
    public function findJobCategory($id) {
        $jobCategorydao = $this->em->getDao(\App\Model\Entity\JobCategory::getClassName());
        return $jobCategorydao->find($id);
    }
	
	public function findOrCreateCategory($category)
	{
		$categoryRepo = $this->em->getDao(\App\Model\Entity\JobCategory::getClassName());
		$entity = $categoryRepo->findOneBy(['name' => $category]);
		if(!isset($entity)) {
			$entity = new \App\Model\Entity\JobCategory();
			$entity->name = $category;
			$categoryRepo->save($entity);
		}
		return $entity;
	}
    
    public function saveJobCategory(\App\Model\Entity\JobCategory $category) {
        $this->em->persist($category);
		$this->em->flush();
    }
    
    public function deleteJobCategory(\App\Model\Entity\JobCategory $category) {
        $jobCategorydao = $this->em->getDao(\App\Model\Entity\JobCategory::getClassName());
        $jobCategorydao->delete($category);
    }
    
    public function isInParentTree(\App\Model\Entity\JobCategory $category, \App\Model\Entity\JobCategory $parent) {
        if ($parent->id == $category->id) {
            return true;
        }
        if ($parent->parent) {
            if ($this->isInParentTree($category, $parent->parent)) {
                return true;
            }
        }
        return false;
    }
}
