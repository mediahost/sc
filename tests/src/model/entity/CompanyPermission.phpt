<?php

namespace Test\Model\Entity;

use App\Model\Entity\Company;
use App\Model\Entity\CompanyPermission;
use App\Model\Entity\CompanyRole;
use App\Model\Entity\User;
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
		$user = new User;
		$user->mail = 'user@domain.com';
		$company = new Company;
		$company->name = 'company';
		$role1 = new CompanyRole(CompanyRole::MANAGER);
		$role2 = new CompanyRole(CompanyRole::EDITOR);

		$companyPriviledge = new CompanyPermission;
		$companyPriviledge->user = $user;
		$companyPriviledge->company = $company;
		$companyPriviledge->addRole($role1);
		$companyPriviledge->addRole($role2);

		Assert::same($user->mail, $companyPriviledge->user->mail);
		Assert::same($company->name, $companyPriviledge->company->name);
		Assert::count(2, $companyPriviledge->roles);
		
		Assert::true($companyPriviledge->containRoleName(CompanyRole::MANAGER));
		Assert::true($companyPriviledge->containRoleName(CompanyRole::EDITOR));
		Assert::false($companyPriviledge->containRoleName('unknown role'));
		
		Assert::same([NULL, NULL], $companyPriviledge->rolesKeys);
		
		Assert::true($companyPriviledge->isAllowed('info'));
		Assert::true($companyPriviledge->isAllowed('users'));

		$companyPriviledge->clearRoles();
		Assert::count(0, $companyPriviledge->roles);
	}

}

$test = new CompanyPermissionTest();
$test->run();
