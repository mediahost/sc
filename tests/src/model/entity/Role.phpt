<?php

namespace Test\Model\Entity;

use App\Model\Entity\Role;
use Tester\Assert;
use Tester\TestCase;

$container = require __DIR__ . '/../../bootstrap.php';

/**
 * TEST: Role entity
 *
 * @testCase
 * @phpVersion 5.4
 */
class RoleTest extends TestCase
{

	const R_NAME = 'Jean Luc Picard';

	public function testSetAndGet()
	{
		$role = new Role();
		$role->name = self::R_NAME;
		Assert::same(self::R_NAME, $role->name);
	}

}

$test = new RoleTest();
$test->run();
