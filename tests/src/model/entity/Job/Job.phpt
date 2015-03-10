<?php

namespace Test\Model\Entity\Special;

use App\Model\Entity\Company;
use App\Model\Entity\Job;
use Test\BaseTestCase;
use Tester\Assert;

$container = require __DIR__ . '/../../../bootstrap.php';

/**
 * TEST: Job entity
 *
 * @testCase
 * @phpVersion 5.4
 */
class JobTest extends BaseTestCase
{

	const NAME = 'my job';

	/** @var Job */
	private $job;

	public function testSetAndGet()
	{
		$this->job = new Job(self::NAME);
		$this->job->company = new Company('company');
		$this->job->description = 'short description';

		Assert::same(self::NAME, $this->job->name);
		Assert::same('company', $this->job->company->name);
		Assert::same('short description', $this->job->description);
		Assert::same(self::NAME, (string) $this->job);
	}

	public function testSkillRequests()
	{
		$this->job = new Job();

		// TODO: setSkillRequest()
		// TODO: clearSkills()
		// TODO: removeOldSkillRequests()

		Assert::type('Doctrine\Common\Collections\Collection', $this->job->skillRequests);
	}

}

$test = new JobTest($container);
$test->run();
