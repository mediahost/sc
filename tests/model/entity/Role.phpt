<?php

namespace Test\Model\Entity;

use Nette,
	Tester,
	Tester\Assert;

use App\Model\Entity;

$container = require __DIR__ . '/../../bootstrap.php';

/**
 * TEST: Role entity
 *
 * @testCase
 * @phpVersion 5.4
 */
class RoleTest extends Tester\TestCase
{

	const R_NAME = 'Jean Luc Picard';

	public function testSetAndGet()
	{
		$role = new Entity\Role();

		$role->name = self::R_NAME;
		Assert::same(self::R_NAME, $role->name);
	}

}

$test = new RoleTest();
$test->run();
