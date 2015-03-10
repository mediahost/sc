<?php

namespace Test\Model\Entity;

use App\Model\Entity\Skill;
use App\Model\Entity\SkillCategory;
use App\Model\Entity\SkillKnow;
use App\Model\Entity\SkillKnowRequest;
use App\Model\Entity\SkillLevel;
use Kdyby\Doctrine\EntityDao;
use Nette\DI\Container;
use Test\DbTestCase;

abstract class SkillTestBase extends DbTestCase
{

	const NAME = 'name';

	/** @var EntityDao */
	protected $skillDao;

	/** @var EntityDao */
	protected $categoryDao;

	/** @var Skill */
	protected $skill;

	/** @var SkillCategory */
	protected $category;

	/** @var SkillLevel */
	protected $level;

	/** @var SkillKnow */
	protected $know;

	/** @var SkillKnowRequest */
	protected $request;

	public function __construct(Container $container)
	{
		parent::__construct($container);

		$this->skillDao = $this->em->getDao(Skill::getClassName());
		$this->categoryDao = $this->em->getDao(SkillCategory::getClassName());
	}

	public function setUp()
	{
		parent::setUp();
		$this->updateSchema();
	}

	public function tearDown()
	{
		$this->dropSchema();
		parent::tearDown();
	}

	protected function saveSkill()
	{
		$this->skillDao->save($this->skill);
		$this->reloadSkill();
		return $this;
	}

	protected function reloadSkill()
	{
		$this->em->detach($this->skill);
		$this->skill = $this->skillDao->find($this->skill->id);
		return $this;
	}

	protected function saveCategory()
	{
		$this->categoryDao->save($this->category);
		$this->reloadCategory();
		return $this;
	}

	protected function reloadCategory()
	{
		$this->em->detach($this->category);
		$this->category = $this->categoryDao->find($this->category->id);
		return $this;
	}

	protected function getClasses()
	{
		return [
				$this->em->getClassMetadata(Skill::getClassName()),
				$this->em->getClassMetadata(SkillCategory::getClassName()),
		];
	}

}
