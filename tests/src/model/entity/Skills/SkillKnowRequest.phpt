<?php

namespace Test\Model\Entity;

use App\Model\Entity\Skill;
use App\Model\Entity\SkillCategory;
use App\Model\Entity\SkillKnow;
use App\Model\Entity\SkillKnowRequest;
use App\Model\Entity\SkillLevel;
use Kdyby\Doctrine\MemberAccessException;
use Tester\Assert;
use Tracy\Debugger;

$container = require __DIR__ . '/../../../bootstrap.php';

/**
 * TEST: SkillKnowRequest entity
 *
 * @testCase
 */
class SkillKnowRequestTest extends SkillTestBase
{

	const SKILL_NAME = 'skill';
	const LEVEL_FROM_NAME = 'levelFrom';
	const LEVEL_TO_NAME = 'levelTo';

	private function setSkillRequest($yearsFrom = NULL, $yearsTo = NULL,
	                                 $priorityFrom = SkillLevel::LAST_PRIORITY,
	                                 $priorityTo = SkillLevel::LAST_PRIORITY)
	{
		$this->request = new SkillKnowRequest();
		$this->request->skill = new Skill(self::SKILL_NAME);
		$levelFrom = new SkillLevel(self::LEVEL_FROM_NAME);
		$levelFrom->priority = $priorityFrom;
		$levelTo = new SkillLevel(self::LEVEL_TO_NAME);
		$levelTo->priority = $priorityTo;
		$this->request->setLevels($levelFrom, $levelTo);
		$this->request->setYears($yearsFrom, $yearsTo);
		return $this;
	}

	public function testSetAndGet()
	{
		$this->setSkillRequest();
		$this->request->setYears(1, 10);
		Assert::same(self::LEVEL_FROM_NAME, $this->request->levelFrom->name);
		Assert::same(self::LEVEL_TO_NAME, $this->request->levelTo->name);
		Assert::same(1, $this->request->yearsFrom);
		Assert::same(10, $this->request->yearsTo);

		Assert::exception(function () {
			$this->request->levelFrom = new SkillLevel();
		}, MemberAccessException::class);
		Assert::exception(function () {
			$this->request->levelTo = new SkillLevel();
		}, MemberAccessException::class);
		Assert::exception(function () {
			$this->request->yearsFrom = 1;
		}, MemberAccessException::class);
		Assert::exception(function () {
			$this->request->yearsTo = 1;
		}, MemberAccessException::class);
	}

	public function testToString()
	{
		$this->setSkillRequest();
		$string = self::SKILL_NAME
				. ':' . self::LEVEL_FROM_NAME . '-' . self::LEVEL_TO_NAME
				. ':-';
		Assert::same($string, (string) $this->request);

		$this->setSkillRequest(3, 4);
		$string = self::SKILL_NAME
				. ':' . self::LEVEL_FROM_NAME . '-' . self::LEVEL_TO_NAME
				. ':3-4';
		Assert::same($string, (string) $this->request);
	}

	public function testImport()
	{
		$this->setSkillRequest();
		$toImport = new SkillKnowRequest;
		$toImport->skill = new Skill(self::SKILL_NAME);
		$levelFrom = new SkillLevel(self::LEVEL_FROM_NAME);
		$levelFrom->priority = 2;
		$levelTo = new SkillLevel(self::LEVEL_TO_NAME);
		$levelTo->priority = 3;
		$toImport->setLevels($levelFrom, $levelTo);
		$toImport->setYears(5, 20);
		$this->request->import($toImport);

		Assert::same($toImport->levelFrom, $this->request->levelFrom);
		Assert::same($toImport->levelTo, $this->request->levelTo);
		Assert::same($toImport->yearsFrom, $this->request->yearsFrom);
		Assert::same($toImport->yearsTo, $this->request->yearsTo);
		Assert::same($toImport->job, $this->request->job);
	}

	public function testHasOneLevel()
	{
		$this->setSkillRequest();
		Assert::false($this->request->hasOneLevel());
		$this->request->setLevels($this->request->levelFrom, $this->request->levelFrom);
		Assert::true($this->request->hasOneLevel());
	}

	public function testIsEmpty()
	{
		$this->setSkillRequest();
		Assert::false($this->request->empty);

		$this->request->levelFrom->priority = SkillLevel::IRELEVANT_PRIORITY;
		Assert::true($this->request->empty);
	}

	public function testLevelMathers()
	{
		$this->setSkillRequest();
		Assert::true($this->request->levelsMatter);

		$this->request->levelFrom->priority = SkillLevel::IRELEVANT_PRIORITY;
		Assert::false($this->request->levelsMatter);
	}

	/**
	 * @dataProvider getPassYears
	 * @param $from
	 * @param $to
	 */
	public function testYearsMathersPass($from, $to)
	{
		$this->setSkillRequest($from, $to);
		Assert::true($this->request->yearsMatter);
	}

	/**
	 * @dataProvider getFailYears
	 * @param $from
	 * @param $to
	 */
	public function testYearsMathersFails($from, $to)
	{
		$this->setSkillRequest($from, $to);
		Assert::false($this->request->yearsMatter);
	}

	public function getPassYears()
	{
		return [
				[1, 3],
				[2, NULL],
				[NULL, 3],
				[0, 3],
				[3, 0],
		];
	}

	public function getFailYears()
	{
		return [
				[NULL, NULL],
				[0, 0],
		];
	}

	/** @dataProvider getSatisfiedData */
	public function testSatisfied($skillNameK, $skillNameQ,
	                              $levelFrom, $level, $levelTo,
	                              $yearsFrom, $years, $yearsTo,
	                              $pass = TRUE)
	{
		$skillK = $this->createSkill($skillNameK);
		$skillQ = $this->createSkill($skillNameQ);
		$skillKnow = $this->createSkillKnow($skillK, $level, $years);
		$skillKnowRequest = $this->createSkillKnowRequest($skillQ, $levelFrom, $levelTo, $yearsFrom, $yearsTo);

		if ($pass) {
			Assert::true($skillKnowRequest->isSatisfiedBy($skillKnow));
		} else {
			Assert::false($skillKnowRequest->isSatisfiedBy($skillKnow));
		}
	}

	public function getSatisfiedData()
	{
		$passed = [
				['skill', 'skill', 2, 3, 4, 5, 6, 7, TRUE],
				['skill', 'skill', 3, 3, 3, 6, 6, 6, TRUE],
				['skill', 'skill', 3, 3, 3, NULL, 6, 10, TRUE], // zespodu neomezené roky
				['skill', 'skill', 3, 3, 3, NULL, NULL, 10, TRUE], // zespodu neomezené roky
				['skill', 'skill', 3, 3, 3, 3, 6, NULL, TRUE], // zvrchu neomezené roky
				['skill', 'skill', 3, 3, 3, NULL, 6, NULL, TRUE], // neomezené roky
				['skill', 'skill', 3, 3, 3, NULL, NULL, NULL, TRUE], // neomezené roky
				['skill', 'skill', 3, 3, 3, NULL, NULL, NULL, TRUE], // neomezené roky
				['skill', 'skill', SkillLevel::FIRST_PRIORITY, 3, SkillLevel::LAST_PRIORITY, 6, 6, 6, TRUE], // neomezené levely (zadaný rozsah všech levelů)
				['skill', 'skill', SkillLevel::IRELEVANT_PRIORITY, 3, SkillLevel::IRELEVANT_PRIORITY, 6, 6, 6, TRUE], // neomezené levely (zadaný pouze irelevantní level)
		];
		$failed = [
				['skill', 'skill2', 2, 3, 4, 5, 6, 7, FALSE], // rozdílné skily
				['skill', 'skill2', NULL, NULL, NULL, NULL, NULL, NULL, FALSE], // rozdílné skily
				['skill', 'skill', 4, 3, 5, 5, 5, 5, FALSE], // levely mimo rozsah
				['skill', 'skill', 5, 3, 4, 5, 5, 5, FALSE], // levely mimo rozsah
				['skill', 'skill', 3, 4, 3, 5, 6, 7, FALSE], // levely mimo rozsah
				['skill', 'skill', 3, NULL, 3, NULL, 6, 5, FALSE], // levely mimo rozsah
				['skill', 'skill', 2, 3, 4, 6, 8, 7, FALSE], // roky mimo rozsah (nevyhovující vrchní omezení)
				['skill', 'skill', 2, 3, 4, 1, NULL, 2, FALSE], // roky mimo rozsah (nevyhovující spodní omezení)
				['skill', 'skill', 2, 3, 4, 8, 7, 9, FALSE], // roky mimo rozsah (nevyhovující spodní omezení)
		];
		return array_merge($passed, $failed);
	}

	private function createSkill($name)
	{
		$findedSkill = $this->skillDao->findOneBy(['name' => $name]);
		if ($findedSkill) {
			return $findedSkill;
		} else {
			$category = new SkillCategory('category for ' . $name);
			$this->em->persist($category);
			$skill = new Skill($name);
			$skill->category = $category;
			$this->em->persist($skill);
			$this->em->flush();
			return $skill;
		}
	}

	private function createSkillKnow(Skill $skill, $levelPriority, $years)
	{
		$level = new SkillLevel();
		$level->priority = $levelPriority;

		$know = new SkillKnow();
		$know->skill = $skill;
		$know->level = $level;
		$know->years = $years;

		return $know;
	}

	private function createSkillKnowRequest(Skill $skill, $levelPriorityFrom, $levelPriorityTo, $yearsFrom, $yearsTo)
	{
		$levelFrom = new SkillLevel();
		$levelFrom->priority = $levelPriorityFrom;
		$levelTo = new SkillLevel();
		$levelTo->priority = $levelPriorityTo;

		$request = new SkillKnowRequest();
		$request->skill = $skill;
		$request->setLevels($levelFrom, $levelTo);
		$request->setYears($yearsFrom, $yearsTo);

		return $request;
	}

}

$test = new SkillKnowRequestTest($container);
$test->run();
