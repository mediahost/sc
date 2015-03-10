<?php

namespace Test\Model\Entity;

use Tester\Assert;

$container = require __DIR__ . '/../../../bootstrap.php';

/**
 * TEST: Company Permission entity
 *
 * @testCase
 * @phpVersion 5.4
 */
class CompanyPermissionTest extends CompanyPermissionTestBase
{

	public function testSetAndGet()
	{
		Assert::same(self::USER_MAIL, $this->companyPermission->user->mail);
		Assert::same(self::COMPANY_NAME, $this->companyPermission->company->name);
	}

}

$test = new CompanyPermissionTest($container);
$test->run();
