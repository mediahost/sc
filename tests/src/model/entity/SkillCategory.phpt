<?php

namespace Test\Model\Entity;

use App\Model\Entity\SkillCategory;
use Kdyby\Doctrine\EntityDao;
use Nette\DI\Container;
use Test\ParentTestCase;
use Tester\Assert;

$container = require __DIR__ . '/../../bootstrap.php';

/**
 * TEST: SkillCategory entity
 *
 * @testCase
 */
class SkillCategoryTest extends ParentTestCase
{

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
		$name = 'foo';
		$this->skillCategory->name = $name;
		Assert::same($name, $this->skillCategory->name);

		$parent = new SkillCategory;
		$this->skillCategory->parent = $parent;
		Assert::same($parent, $this->skillCategory->parent);
	}

	public function testChilds()
	{
		$name1 = 'foo bar';
		$name2 = 'bar baz';
		$name3 = 'baz foo';

		$this->skillCategory->name = $name1;
		$this->skillCategoryDao->save($this->skillCategory);

		$child1 = new SkillCategory;
		$child1->name = $name2;
		$child1->parent = $this->skillCategory;
		$this->skillCategoryDao->save($child1);

		$child2 = new SkillCategory;
		$child2->name = $name3;
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
