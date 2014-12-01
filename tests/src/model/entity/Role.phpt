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

	const CMP_BIGGER = 1;
	const CMP_EQUAL = 0;
	const CMP_LOWER = -1;
	const R_NAME = 'signed';

	public function testSetAndGet()
	{
		$role = new Role(self::R_NAME);
		Assert::same(self::R_NAME, $role->name);
		Assert::same(self::R_NAME, (string) $role);
	}

	public function testStatics()
	{
		Assert::same(self::CMP_EQUAL, Role::cmpRoles(new Role(Role::ROLE_SUPERADMIN), Role::ROLE_SUPERADMIN));
		Assert::same(self::CMP_LOWER, Role::cmpRoles(new Role(Role::ROLE_ADMIN), new Role(Role::ROLE_SUPERADMIN)));
		Assert::same(self::CMP_BIGGER, Role::cmpRoles(new Role(Role::ROLE_SUPERADMIN), new Role(Role::ROLE_ADMIN)));
		Assert::same(self::CMP_BIGGER, Role::cmpRoles(new Role(Role::ROLE_ADMIN), new Role(Role::ROLE_COMPANY)));
		Assert::same(self::CMP_BIGGER, Role::cmpRoles(new Role(Role::ROLE_COMPANY), new Role(Role::ROLE_CANDIDATE)));
		Assert::same(self::CMP_BIGGER, Role::cmpRoles(new Role(Role::ROLE_CANDIDATE), new Role(Role::ROLE_SIGNED)));
		Assert::same(self::CMP_BIGGER, Role::cmpRoles(new Role(Role::ROLE_SIGNED), new Role(Role::ROLE_GUEST)));

		$roles1 = [
			Role::ROLE_ADMIN,
			new Role(Role::ROLE_COMPANY),
			new Role(Role::ROLE_SIGNED),
		];
		$maxRole1 = new Role(Role::ROLE_ADMIN);
		Assert::same((string) $maxRole1, (string) Role::getMaxRole($roles1));
		$roles2 = [
			new Role(Role::ROLE_SIGNED),
			new Role(Role::ROLE_COMPANY),
			Role::ROLE_CANDIDATE,
		];
		$maxRole2 = new Role(Role::ROLE_COMPANY);
		Assert::same((string) $maxRole2, (string) Role::getMaxRole($roles2));
	}

}

$test = new RoleTest();
$test->run();
