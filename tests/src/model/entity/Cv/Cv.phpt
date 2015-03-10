<?php

namespace Test\Model\Entity\Special;

use App\Model\Entity\Candidate;
use App\Model\Entity\Cv;
use Test\BaseTestCase;
use Tester\Assert;

$container = require __DIR__ . '/../../../bootstrap.php';

/**
 * TEST: Cv entity
 *
 * @testCase
 * @phpVersion 5.4
 */
class CvTest extends BaseTestCase
{

	const NAME = 'my cv';

	/** @var Cv */
	private $cv;

	public function testSetAndGet()
	{
		$this->cv = new Cv(self::NAME);
		$this->cv->lastOpenedPreviewPage = 5;
		$this->cv->lastUsedPreviewScale = 2.5;
		$this->cv->isDefault = TRUE;
		$this->cv->candidate = new Candidate('my name');

		Assert::same(self::NAME, $this->cv->name);
		Assert::same(5, $this->cv->lastOpenedPreviewPage);
		Assert::same(2.5, $this->cv->lastUsedPreviewScale);
		Assert::true($this->cv->isDefault);
		Assert::same('my name', $this->cv->candidate->name);
		Assert::same(self::NAME, (string) $this->cv);
	}

	public function testSkillKnows()
	{
		$this->cv = new Cv();

		// TODO: setSkillKnow()
		// TODO: clearSkills()
		// TODO: removeOldSkillKnows()

		Assert::type('Doctrine\Common\Collections\Collection', $this->cv->skillKnows);
	}

}

$test = new CvTest($container);
$test->run();
