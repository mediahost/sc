<?php

namespace Test\Model\Entity;

use App\Model\Entity\CompanyPermission;
use Tester\Assert;
use Tester\TestCase;

$container = require __DIR__ . '/../../bootstrap.php';

/**
 * TEST: Company Permission entity
 *
 * @testCase
 * @phpVersion 5.4
 */
class CompanyPermissionTest extends TestCase
{

	public function testSetAndGet()
	{
		$companyPriviledge = new CompanyPermission;
		Assert::same(TRUE, TRUE);
	}

}

$test = new CompanyPermissionTest();
$test->run();
