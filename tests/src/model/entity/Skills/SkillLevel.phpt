<?php

namespace Test\Model\Entity;

use App\Model\Entity\SkillLevel;
use Tester\Assert;

$container = require __DIR__ . '/../../../bootstrap.php';

/**
 * TEST: SkillLevel entity
 *
 * @testCase
 */
class SkillLevelTest extends SkillTestBase
{

	private function setLevel($priority)
	{
		$this->level = new SkillLevel(self::NAME);
		$this->level->priority = $priority;
	}

	public function testSetAndGet()
	{
		$this->setLevel(3);
		Assert::same(self::NAME, $this->level->name);
		Assert::same(3, $this->level->priority);
		Assert::same(self::NAME, (string) $this->level);
	}

	public function testFirstLast()
	{
		$this->setLevel(3);
		Assert::false($this->level->first);
		Assert::false($this->level->last);

		$this->setLevel(SkillLevel::FIRST_PRIORITY);
		Assert::true($this->level->first);
		Assert::false($this->level->last);

		$this->setLevel(SkillLevel::LAST_PRIORITY);
		Assert::false($this->level->first);
		Assert::true($this->level->last);
	}

	public function testRelevant()
	{
		$this->setLevel(SkillLevel::FIRST_PRIORITY);
		Assert::false($this->level->relevant);

		$this->setLevel(2);
		Assert::true($this->level->relevant);

		$this->setLevel(3);
		Assert::true($this->level->relevant);

		$this->setLevel(4);
		Assert::true($this->level->relevant);

		$this->setLevel(SkillLevel::LAST_PRIORITY);
		Assert::true($this->level->relevant);
	}

	/**
	 * @dataProvider getAllSkillPriorities
	 * @param $priority
	 */
	public function testInFullRange($priority)
	{
		$this->setLevel($priority);

		$from = new SkillLevel('from');
		$to = new SkillLevel('to');

		$from->priority = SkillLevel::FIRST_PRIORITY;
		$to->priority = SkillLevel::LAST_PRIORITY;
		Assert::true($this->level->isInRange($from, $to));
	}

	public function testInRange()
	{
		$from = new SkillLevel('from');
		$to = new SkillLevel('to');

		$this->setLevel(SkillLevel::FIRST_PRIORITY);
		$from->priority = $to->priority = SkillLevel::FIRST_PRIORITY;
		Assert::true($this->level->isInRange($from, $to));
		$to->priority = 2;
		Assert::true($this->level->isInRange($from, $to));

		$from->priority = 2;
		$to->priority = 3;
		Assert::false($this->level->isInRange($from, $to));

		$this->setLevel(2);
		Assert::true($this->level->isInRange($from, $to));

		$from->priority = 3;
		$to->priority = 4;
		Assert::false($this->level->isInRange($from, $to));

		$this->setLevel(SkillLevel::LAST_PRIORITY);
		Assert::false($this->level->isInRange($from, $to));

		$from->priority = SkillLevel::LAST_PRIORITY;
		Assert::false($this->level->isInRange($from, $to));

		$to->priority = SkillLevel::LAST_PRIORITY;
		Assert::true($this->level->isInRange($from, $to));
	}

	public function getAllSkillPriorities()
	{
		return [
				[SkillLevel::FIRST_PRIORITY],
				[2],
				[3],
				[4],
				[5],
				[SkillLevel::LAST_PRIORITY],
		];
	}

}

$test = new SkillLevelTest($container);
$test->run();
