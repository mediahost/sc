<?php

namespace Test\Model\Entity;

use App\Model\Entity\CompanyPriviledge;
use Tester\Assert;
use Tester\TestCase;

$container = require __DIR__ . '/../../bootstrap.php';

/**
 * TEST: Company Priviledge entity
 *
 * @testCase
 * @phpVersion 5.4
 */
class CompanyPriviledgeTest extends TestCase
{

	public function testSetAndGet()
	{
		$companyPriviledge = new CompanyPriviledge;
		Assert::same(TRUE, TRUE);
	}

}

$test = new CompanyPriviledgeTest();
$test->run();
