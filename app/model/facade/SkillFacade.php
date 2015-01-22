<?php

namespace App\Model\Facade;

use App\Model\Entity\Skill;
use App\Model\Entity\SkillCategory;
use App\Model\Entity\SkillLevel;
use Kdyby\Doctrine\EntityDao;
use Kdyby\Doctrine\EntityManager;
use Nette\Object;

/**
 * SkillFacade
 * TODO: Test it
 */
class SkillFacade extends Object
{

	/** @var EntityManager @inject */
	public $em;

	/** @var EntityDao */
	private $skillDao;

	/** @var EntityDao */
	private $skillCategoryDao;

	/** @var EntityDao */
	private $skillLevelDao;

	public function __construct(EntityManager $em)
	{
		$this->em = $em;
		$this->skillDao = $this->em->getDao(Skill::getClassName());
		$this->skillCategoryDao = $this->em->getDao(SkillCategory::getClassName());
		$this->skillLevelDao = $this->em->getDao(SkillLevel::getClassName());
	}

	// <editor-fold defaultstate="expanded" desc="create & add">
	// </editor-fold>
	// <editor-fold defaultstate="expanded" desc="getters">

	/**
	 * Return all end skill categories
	 * @param type $onlyUsed
	 */
	public function getTopCategories()
	{
		return $this->skillCategoryDao->findBy([
			'parent' => NULL,
		]);
	}

	// </editor-fold>
	// <editor-fold defaultstate="expanded" desc="finders">
	// </editor-fold>
	// <editor-fold defaultstate="expanded" desc="checkers">
	// </editor-fold>
	// <editor-fold defaultstate="expanded" desc="delete">
	// </editor-fold>
}
