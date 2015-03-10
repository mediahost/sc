<?php

namespace Test\Model\Entity;

use App\Model\Entity\Skill;
use App\Model\Entity\SkillCategory;
use Doctrine\ORM\ORMInvalidArgumentException;
use Tester\Assert;

$container = require __DIR__ . '/../../../bootstrap.php';

/**
 * TEST: SkillCategory entity
 *
 * @testCase
 */
class SkillCategoryTest extends SkillTestBase
{

	private function setCategory()
	{
		$this->category = new SkillCategory(self::NAME);
		return $this;
	}

	public function testSetAndGet()
	{
		$this->setCategory();
		Assert::same(self::NAME, $this->category->name);
	}

	public function testSaveNewParent()
	{
		$this->setCategory();

		$parent = new SkillCategory('parent');
		$this->category->parent = $parent;

		Assert::exception(function () {
			$this->saveCategory();
		}, ORMInvalidArgumentException::class);
	}

	public function testParent()
	{
		$parent = new SkillCategory('parent');
		$this->categoryDao->save($parent);

		$this->setCategory();
		$this->category->parent = $parent;
		$this->saveCategory();
		Assert::type(SkillCategory::getClassName(), $this->category->parent);
		Assert::same($parent, $this->category->parent);

		// clear saved parent
		$this->category->parent = NULL;
		$this->saveCategory();
		Assert::null($this->category->parent);
	}

	public function testChilds()
	{
		$this->setCategory();
		$this->saveCategory();

		$child1 = new SkillCategory('bar baz');
		$child1->parent = $this->category;
		$this->categoryDao->save($child1);

		$child2 = new SkillCategory('baz foo');
		$child2->parent = $this->category;
		$this->categoryDao->save($child2);

		$this->reloadCategory();
		Assert::count(2, $this->category->childs);
		Assert::type('Doctrine\Common\Collections\Collection', $this->category->childs);
	}

	public function testSkills()
	{
		$this->setCategory();
		$this->saveCategory();

		$skill1 = new Skill('bar baz');
		$skill1->category = $this->category;
		$this->skillDao->save($skill1);

		$skill2 = new Skill('baz foo');
		$skill2->category = $this->category;
		$this->skillDao->save($skill2);

		$this->reloadCategory();
		Assert::count(2, $this->category->skills);
		Assert::type('Doctrine\Common\Collections\Collection', $this->category->skills);
	}

}

$test = new SkillCategoryTest($container);
$test->run();
