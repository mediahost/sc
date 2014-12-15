<?php

namespace Test\Model\Entity;

use App\Model\Entity\CompanyRole;
use Tester\Assert;
use Tester\TestCase;

$container = require __DIR__ . '/../../bootstrap.php';

/**
 * TEST: Company Role entity
 *
 * @testCase
 * @phpVersion 5.4
 */
class CompanyRoleTest extends TestCase
{

	public function testSetAndGet()
	{
		$name = 'role name';
		$role = new CompanyRole;
		$role->name = $name;
		
		Assert::same($name, $role->name);
		Assert::same($name, (string) $role);
	}

}

$test = new CompanyRoleTest();
$test->run();
