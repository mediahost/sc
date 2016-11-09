<?php

namespace App\Model\Facade;

use App\Model\Entity\SkillCategory;
use App\Model\Entity\SkillLevel;
use Doctrine\Common\Collections\ArrayCollection;
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

	public function getSkillLevelNames()
	{
		$result = [];
		$skillLevels = $this->em->getRepository(SkillLevel::getClassName())->findAll();
		foreach ($skillLevels as $level) {
			$result[] = $level->name;
		}
		return $result;
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
			foreach ($category->skills as $skill) {
				if(in_array($skill->id, $knowsId)) {
					$empty = false;
				}
			}
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

	public function sortCategoriesBySkillCount($categories, $skillKnows) {
		$knowsId = array();
		foreach($skillKnows as $know) {
			$knowsId[] = $know->skill->id;
		}
		usort($categories, function ($item1, $item2) use($knowsId) {
			$cnt1 = 0;
			foreach ($item1->childs as $child) {
				foreach ($child->skills as $skill) {
					if(in_array($skill->id, $knowsId)) {
						$cnt1++;
					}
				}
			}
			$cnt2 = 0;
			foreach ($item2->childs as $child) {
				foreach ($child->skills as $skill) {
					if(in_array($skill->id, $knowsId)) {
						$cnt2++;
					}
				}
			}
			return $cnt1 <= $cnt2;
		});
		return $categories;
	}
    
    public function isInParentTree(SkillCategory $category, SkillCategory $parent) {
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
