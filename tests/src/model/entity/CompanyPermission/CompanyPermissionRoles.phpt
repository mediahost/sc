<?php

namespace Test\Model\Entity;

use App\Model\Entity\CompanyRole;
use Tester\Assert;

$container = require __DIR__ . '/../../../bootstrap.php';

/**
 * TEST: Company Permission Roles entity
 *
 * @testCase
 * @phpVersion 5.4
 */
class CompanyPermissionRolesTest extends CompanyPermissionTestBase
{

	public function testRoles()
	{
		$role1 = new CompanyRole(CompanyRole::MANAGER);
		$role2 = new CompanyRole(CompanyRole::EDITOR);

		Assert::count(0, $this->companyPermission->roles);
		$this->companyPermission->addRole($role1);
		$this->companyPermission->addRole($role2);
		Assert::type('Doctrine\Common\Collections\Collection', $this->companyPermission->roles);
		Assert::count(2, $this->companyPermission->roles);

		Assert::true($this->companyPermission->containRoleName(CompanyRole::MANAGER));
		Assert::true($this->companyPermission->containRoleName(CompanyRole::EDITOR));
		Assert::false($this->companyPermission->containRoleName('unknown role'));

		Assert::same([NULL, NULL], $this->companyPermission->rolesKeys);

		Assert::true($this->companyPermission->isAllowed('info'));
		Assert::true($this->companyPermission->isAllowed('users'));

		$this->companyPermission->clearRoles();
		Assert::count(0, $this->companyPermission->roles);
	}

	//TODO: test for isAllowed(), and getRolesKeys after saving

}

$test = new CompanyPermissionRolesTest($container);
$test->run();
