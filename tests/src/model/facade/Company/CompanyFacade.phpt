<?php

namespace Test\Model\Facade;

use App\Model\Entity\Company;
use App\Model\Entity\CompanyRole;
use App\Model\Entity\Role;
use App\Model\Entity\User;
use Tester\Assert;

$container = require __DIR__ . '/../../../bootstrap.php';

/**
 * TEST: CompanyFacade
 *
 * @testCase
 * @phpVersion 5.4
 */
class CompanyFacadeTest extends CompanyFacade
{

	public function testCreate()
	{
		$companyRole = new CompanyRole(CompanyRole::ADMIN);
		$this->companyRoleDao->save($companyRole);

		$role = $this->roleFacade->create(Role::ADMIN);
		$user = $this->userFacade->create('user', 'user', $role);

		$company1 = new Company('company1');
		$company1->companyId = 'id123';
		$this->companyFacade->create($company1, $user);
		$company2 = $this->companyFacade->create('comapany2', $user);

		$permission1 = $this->companyFacade->findPermission($company1, $user);
		$permission2 = $this->companyFacade->findPermission($company2, $user);

		Assert::same($user->id, $permission1->user->id);
		Assert::same($user->id, $permission1->user->id);

		Assert::same($company1->id, $permission1->company->id);
		Assert::same($company2->id, $permission2->company->id);
	}

	public function testCreateRole()
	{
		Assert::same(CompanyRole::ADMIN, $this->companyFacade->createRole(CompanyRole::ADMIN)->name);
		Assert::same(CompanyRole::MANAGER, $this->companyFacade->createRole(CompanyRole::MANAGER)->name);
		Assert::same(CompanyRole::EDITOR, $this->companyFacade->createRole(CompanyRole::EDITOR)->name);
		Assert::null($this->companyFacade->createRole(CompanyRole::EDITOR));
	}

	public function testAddPermission()
	{
		$company = new Company('my company');
		$this->companyDao->save($company);
		$user = new User('user@mail.com');
		$this->userDao->save($user);
		$role1 = new CompanyRole(CompanyRole::ADMIN);
		$this->companyRoleDao->save($role1);
		$role2 = new CompanyRole(CompanyRole::MANAGER);
		$this->companyRoleDao->save($role2);
		$role3 = new CompanyRole(CompanyRole::EDITOR);
		$this->companyRoleDao->save($role3);

		Assert::null($this->companyFacade->addPermission($company, $user, []));
		$permission1 = $this->companyFacade->addPermission($company, $user, [$role1]);
		Assert::same($company->name, $permission1->company->name);
		Assert::same($user->mail, $permission1->user->mail);
		Assert::count(1, $permission1->roles);

		$permission2 = $this->companyFacade->addPermission($company, $user, [$role1, $role2, $role3]);
		Assert::count(3, $permission2->roles);
	}

	public function testDelete()
	{
		$company = new Company('my company');
		$this->companyDao->save($company);
		$user = new User('user@mail.com');
		$this->userDao->save($user);
		$role1 = new CompanyRole(CompanyRole::ADMIN);
		$this->companyRoleDao->save($role1);
		$role2 = new CompanyRole(CompanyRole::MANAGER);
		$this->companyRoleDao->save($role2);

		$permission = $this->companyFacade->addPermission($company, $user, [$role1, $role2]);
		Assert::count(2, $permission->roles);
		Assert::count(1, $this->companyFacade->findPermissions($company));
		$this->companyFacade->clearPermissions($company);
		Assert::count(0, $this->companyFacade->findPermissions($company));
		Assert::count(1, $this->companyFacade->getCompaniesNames());

		$this->companyFacade->addPermission($company, $user, [$role1, $role2]);
		$this->companyFacade->delete($company);
		Assert::count(0, $this->companyFacade->findPermissions($company));
		Assert::count(0, $this->companyFacade->getCompaniesNames());
	}

}

$test = new CompanyFacadeTest($container);
$test->run();
