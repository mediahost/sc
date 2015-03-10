<?php

namespace Test\Model\Entity;

use App\Model\Entity\CompanyPermission;
use App\Model\Entity\CompanyRole;
use App\Model\Entity\User;
use Tester\Assert;

$container = require __DIR__ . '/../../../bootstrap.php';

/**
 * TEST: Company jobs entity
 *
 * @testCase
 * @phpVersion 5.4
 */
class CompanyJobsTest extends CompanyTestBase
{

	public function testJobs()
	{
		Assert::type('Doctrine\Common\Collections\Collection', $this->company->jobs);
	}

}

$test = new CompanyJobsTest($container);
$test->run();
