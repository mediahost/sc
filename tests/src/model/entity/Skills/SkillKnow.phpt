<?php

namespace Test\Model\Entity;

use App\Model\Entity\Skill;
use App\Model\Entity\SkillKnow;
use App\Model\Entity\SkillLevel;
use Tester\Assert;

$container = require __DIR__ . '/../../../bootstrap.php';

/**
 * TEST: SkillKnow entity
 *
 * @testCase
 */
class SkillKnowTest extends SkillTestBase
{

	const SKILL_NAME = 'skill';
	const LEVEL_NAME = 'level';

	private function setKnow($years = NULL, $priority = SkillLevel::LAST_PRIORITY)
	{
		$this->know = new SkillKnow;
		$this->know->skill = new Skill(self::SKILL_NAME);
		$this->know->level = new SkillLevel(self::LEVEL_NAME);
		$this->know->level->priority = $priority;
		$this->know->years = $years;
		return $this;
	}

	public function testToString()
	{
		$years = 4;
		$this->setKnow($years);
		Assert::same(self::SKILL_NAME . ':' . self::LEVEL_NAME . ':' . $years, (string) $this->know);
		$this->setKnow();
		Assert::same(self::SKILL_NAME . ':' . self::LEVEL_NAME, (string) $this->know);
	}

	public function testImport()
	{
		$this->setKnow();

		$toImport = new SkillKnow;
		$toImport->skill = new Skill(self::SKILL_NAME);
		$toImport->level = new SkillLevel(self::LEVEL_NAME);
		$toImport->level->priority = 4;
		$toImport->years = 5;
		$this->know->import($toImport);

		Assert::same($toImport->level, $this->know->level);
		Assert::same($toImport->years, $this->know->years);
		Assert::same($toImport->cv, $this->know->cv);
	}

	public function testIsEmpty()
	{
		$this->setKnow();
		Assert::false($this->know->isEmpty());

		$this->setKnow(NULL, SkillLevel::IRELEVANT_PRIORITY);
		Assert::true($this->know->isEmpty());
	}

	/**
	 * @dataProvider getPassRanges
	 * @param $years
	 * @param $from
	 * @param $to
	 */
	public function testYearsIsInRange($years, $from, $to)
	{
		$this->setKnow($years);
		Assert::true($this->know->hasYearsInRange($from, $to));
	}

	/**
	 * @dataProvider getFailRanges
	 * @param $years
	 * @param $from
	 * @param $to
	 */
	public function testYearsIsntInRange($years, $from, $to)
	{
		$this->setKnow($years);
		Assert::false($this->know->hasYearsInRange($from, $to));
	}

	public function getPassRanges()
	{
		return [
				[4, 1, 5],
				[4, 3, 4],
				[4, 4, 5],
				[4, 4, 4],
				[3, 1, 3],
				[3, NULL, 3],
				[3, 3, NULL],
				[5, 4, NULL],
				[5, NULL, 5],
				[5, NULL, 6],
				[NULL, NULL, 1],
				[NULL, NULL, 10],
		];
	}

	public function getFailRanges()
	{
		return [
				[4, 5, 10],
				[4, NULL, 3],
				[4, 5, 3],
				[4, NULL, 3],
				[4, 5, NULL],
				[NULL, 1, 10],
				[NULL, 3, NULL],
		];
	}

}

$test = new SkillKnowTest($container);
$test->run();
