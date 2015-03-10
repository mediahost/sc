<?php

namespace Test\Model\Entity;

use App\Model\Entity\CompanyPermission;
use App\Model\Entity\CompanyRole;
use App\Model\Entity\User;
use Tester\Assert;

$container = require __DIR__ . '/../../../bootstrap.php';

/**
 * TEST: Company accesses entity
 *
 * @testCase
 * @phpVersion 5.4
 */
class CompanyAccessesTest extends CompanyTestBase
{

	public function testAccesses()
	{
		$user1 = new User('user1@mail.com');
		$user2 = new User('user2@mail.com');
		$user3 = new User('user3@mail.com');
		$roleAdmin = new CompanyRole(CompanyRole::ADMIN);
		$roleManager = new CompanyRole(CompanyRole::MANAGER);
		$roleEditor = new CompanyRole(CompanyRole::EDITOR);

		$adminPermission = new CompanyPermission;
		$adminPermission->company = $this->company;
		$adminPermission->user = $user1;
		$adminPermission->addRole($roleAdmin);

		$adminManagerPermission = new CompanyPermission;
		$adminManagerPermission->company = $this->company;
		$adminManagerPermission->user = $user2;
		$adminManagerPermission->addRole($roleAdmin);
		$adminManagerPermission->addRole($roleManager);

		$editorPermission = new CompanyPermission;
		$editorPermission->company = $this->company;
		$editorPermission->user = $user3;
		$editorPermission->addRole($roleEditor);

		Assert::type('Doctrine\Common\Collections\Collection', $this->company->accesses);
		Assert::count(0, $this->company->accesses);

		$this->company->addAccess($adminPermission);
		$this->company->addAccess($adminManagerPermission);
		$this->company->addAccess($editorPermission);

		Assert::count(3, $this->company->accesses);

		Assert::count(2, $this->company->adminAccesses);
		Assert::count(1, $this->company->managerAccesses);
		Assert::count(1, $this->company->editorAccesses);

		$this->company->clearAccesses();
		Assert::count(0, $this->company->accesses);
	}

}

$test = new CompanyAccessesTest($container);
$test->run();
