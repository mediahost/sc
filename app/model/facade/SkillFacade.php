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

}
