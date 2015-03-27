<?php

namespace Test\Model\Entity;

use App\Model\Entity\Skill;
use App\Model\Entity\SkillCategory;
use Kdyby\Doctrine\EmptyValueException;
use Tester\Assert;

$container = require __DIR__ . '/../../../bootstrap.php';

/**
 * TEST: Skill entity
 *
 * @testCase
 */
class SkillTest extends SkillTestBase
{

	private function setSkill()
	{
		$this->category = new SkillCategory('category');
		$this->em->persist($this->category);
		$this->em->flush();

		$this->skill = new Skill(self::NAME);
		$this->skill->category = $this->category;
	}

	public function testSetAndGet()
	{
		$this->setSkill();

		Assert::same(self::NAME, $this->skill->name);
		Assert::same(self::NAME, (string) $this->skill);

		Assert::same($this->category, $this->skill->category);
	}

	public function testIsNew()
	{
		$this->setSkill();
		Assert::true($this->skill->isNew());

		$this->saveSkill();
		Assert::false($this->skill->isNew());
	}

	public function testIsEqual()
	{
		$this->setSkill();
		Assert::true($this->skill->isEqual(new Skill));

		$this->saveSkill();
		Assert::true($this->skill->isEqual($this->skill));
		Assert::false($this->skill->isEqual(new Skill));
	}

	public function testSaveWithoutCategory()
	{
		$this->skill = new Skill(self::NAME);
		Assert::exception(function () {
			$this->saveSkill();
		}, EmptyValueException::class);

	}

}

$test = new SkillTest($container);
$test->run();