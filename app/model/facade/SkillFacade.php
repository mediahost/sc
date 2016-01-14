<?php

namespace App\Model\Facade;

use App\Model\Entity\SkillCategory;
use Kdyby\Doctrine\EntityDao;
use Kdyby\Doctrine\EntityManager;
use Nette\Object;

class SkillFacade extends Object
{

	/** @var EntityManager @inject */
	public $em;

	/** @var EntityDao */
	private $skillCategoryDao;

	public function __construct(EntityManager $em)
	{
		$this->em = $em;
		$this->skillCategoryDao = $this->em->getDao(SkillCategory::getClassName());
	}

	public function getTopCategories()
	{
		return $this->skillCategoryDao->findBy([
				'parent' => NULL,
		]);
	}
	
	/**
	 * Filters only categories with filled skills
	 * @param ArrayCollection $categories
	 * @param ArrayCollection $skillKnows
	 * @return ArrayCollection
	 */
	public function filterFilledCategories($categories, $skillKnows) {
		$knowsId = array();
		foreach($skillKnows as $know) {
			$knowsId[] = $know->skill->id;
		}
		foreach($categories as $key => $category) {
			$empty = true;
			foreach ($category->childs as $child) {
				foreach ($child->skills as $skill) {
					if(in_array($skill->id, $knowsId)) {
						$empty = false;
					}
				}
			}
			if($empty) {
				unset($categories[$key]);
			}
		}
		return $categories;
	}

}
