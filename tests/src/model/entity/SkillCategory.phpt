<?php

namespace Test\Model\Entity;

use Nette\DI\Container;
use Kdyby\Doctrine\EntityDao;
use App\Model\Entity\Skill;
use App\Model\Entity\SkillCategory;
use Test\ParentTestCase;
use Tester\Environment;
use Tester\Assert;

$container = require __DIR__ . '/../../bootstrap.php';

/**
 * TEST: Skill entity
 *
 * @testCase
 * @skip
 */
class SkillCategoryTest extends ParentTestCase
{
	
	const C_NAME_1 = 'foo';
	const C_NAME_2 = 'bar';
	const C_NAME_3 = 'baz';
	
	/** @var EntityDao */
	protected $skillCategoryDao;
	
	/** @var SkillCategory */
	protected $skillCategory;
	
	public function __construct(Container $container)
	{
		parent::__construct($container);
		
		$this->skillCategoryDao = $this->em->getDao(SkillCategory::getClassName());
	}
	
	public function setUp()
	{
		Environment::lock('db', LOCK_DIR);
		$this->updateSchema();
		$this->skillCategory = new SkillCategory;
	}
	
	public function tearDown()
	{
		unset($this->skillCategory);
		$this->dropSchema();
	}
	
	public function testSetAndGet()
	{
		$this->skillCategory->name = self::C_NAME_1;
		Assert::same(self::C_NAME_1, $this->skillCategory->name);
		
		$parent = new SkillCategory; 
		$this->skillCategory->parent = $parent;
		Assert::same($parent, $this->skillCategory->parent);
	}
	
	public function testChilds()
	{
		$this->skillCategory->name = self::C_NAME_1;
		$this->skillCategoryDao->save($this->skillCategory);
		
		$child1 = new SkillCategory;
		$child1->name = self::C_NAME_2;
		$child1->parent = $this->skillCategory;
		$this->skillCategoryDao->save($child1);
		
		$child2 = new SkillCategory;
		$child2->name = self::C_NAME_3;
		$child2->parent = $this->skillCategory;
		$this->skillCategoryDao->save($child2);
		
		$id = $this->skillCategory->getId();
		$this->em->detach($this->skillCategory);
		
		$parent = $this->skillCategoryDao->find($id);
		
		Assert::count(2, $parent->childs);	
	}
	
}

$test = new SkillCategoryTest($container);
$test->run();