<?php

namespace App\Model\Facade\Traits;

use App\Model\Entity\Candidate;
use App\Model\Entity\JobCategory;

trait JobCategoryFacade
{

	private $categoriesPairs;


	public function findCategories()
	{
		$jobCategorydao = $this->em->getDao(JobCategory::getClassName());
		return $jobCategorydao->findAll();
	}

	public function findTopCategories()
	{
		$jobCategorydao = $this->em->getDao(JobCategory::getClassName());
		return $jobCategorydao->findBy([
			'parent' => NULL,
		]);
	}

	public function findCategoriesPairs()
	{
		if (!$this->categoriesPairs) {
			$jobCategorydao = $this->em->getDao(JobCategory::getClassName());
			$this->categoriesPairs = $jobCategorydao->findPairs('name');
		}
		return $this->categoriesPairs;
	}

	public function findCategoriesTree($categories = null)
	{
		$result = [];
		if (!$categories) {
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

	public function findCandidatePreferedCategories(Candidate $candidate)
	{
		$categories = [];
		$allCategories = $this->findCategoriesPairs();
		foreach ($allCategories as $id => $category) {
			if (in_array($id, $candidate->jobCategories)) {
				$categories[] = $category;
			}
		}
		return $categories;
	}

	public function findJobCategory($id)
	{
		$jobCategorydao = $this->em->getDao(JobCategory::getClassName());
		return $jobCategorydao->find($id);
	}

	public function findOrCreateCategory($category)
	{
		$categoryRepo = $this->em->getDao(JobCategory::getClassName());
		$entity = $categoryRepo->findOneBy(['name' => $category]);
		if (!isset($entity)) {
			$entity = new JobCategory();
			$entity->name = $category;
			$categoryRepo->save($entity);
			$this->categoriesPairs = null;
		}
		return $entity;
	}

	public function saveJobCategory(JobCategory $category)
	{
		$this->em->persist($category);
		$this->em->flush();
		$this->categoriesPairs = null;
	}

	public function deleteJobCategory(JobCategory $category)
	{
		$jobCategorydao = $this->em->getDao(JobCategory::getClassName());
		$jobCategorydao->delete($category);
		$this->categoriesPairs = null;
	}

	public function isInParentTree(JobCategory $category, JobCategory $parent)
	{
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
