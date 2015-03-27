<?php

namespace Test\Model\Facade;

use App\Model\Entity\CompanyRole;
use Tester\Assert;

$container = require __DIR__ . '/../../../bootstrap.php';

/**
 * TEST: CompanyFacade Finders
 *
 * @testCase
 * @phpVersion 5.4
 */
class CompanyFacadeFindersTest extends CompanyFacade
{

	public function testFind()
	{
		$findedCompany1 = $this->companyFacade->find(1);
		Assert::same(1, $findedCompany1->id);
		Assert::same(1, $this->companyFacade->find($findedCompany1)->id);

		$findedCompany2 = $this->companyFacade->find(2);
		Assert::same(2, $findedCompany2->id);
		Assert::same(2, $this->companyFacade->find($findedCompany2)->id);

		Assert::null($this->companyFacade->find('unknown_ID'));
	}

	public function testFindByCompanyId()
	{
		Assert::same(1, $this->companyFacade->findByCompanyId('id1')->id);
		Assert::same(2, $this->companyFacade->findByCompanyId('id2')->id);
		Assert::same(3, $this->companyFacade->findByCompanyId('id3')->id);
		Assert::null($this->companyFacade->findByCompanyId('unknownID'));
	}

	public function testFindByName()
	{
		Assert::same(1, $this->companyFacade->findByName('company1')->id);
		Assert::same(2, $this->companyFacade->findByName('company2')->id);
		Assert::same(3, $this->companyFacade->findByName('company3')->id);
		Assert::null($this->companyFacade->findByName('Unknown Company'));
	}

	public function testFindRoleByName()
	{
		Assert::same(CompanyRole::EDITOR, $this->companyFacade->findRoleByName(CompanyRole::EDITOR)->name);
		Assert::same(CompanyRole::MANAGER, $this->companyFacade->findRoleByName(CompanyRole::MANAGER)->name);
		Assert::same(CompanyRole::ADMIN, $this->companyFacade->findRoleByName(CompanyRole::ADMIN)->name);
		Assert::null($this->companyFacade->findRoleByName('unknown name'));
	}

	public function testFindPermission()
	{
		$company1 = $this->companyDao->find(1);
		$company2 = $this->companyDao->find(2);
		$user1 = $this->userRepo->find(3);
		$user2 = $this->userRepo->find(4);

		Assert::same(1, $this->companyFacade->findPermission($company1, $user1)->id);
		Assert::same(2, $this->companyFacade->findPermission($company2, $user2)->id);

		$this->companyFacade->addPermission($company2, $user2, [CompanyRole::MANAGER]);
		Assert::same(2, $this->companyFacade->findPermission($company2, $user2)->id);
	}

	public function testFindPermissions()
	{
		$company1 = $this->companyDao->find(1);
		$company2 = $this->companyDao->find(2);
		$user1 = $this->userRepo->find(5);
		$user2 = $this->userRepo->find(6);
		$user3 = $this->userRepo->find(7);

		Assert::same([], $this->companyFacade->findPermissions());
		Assert::count(1, $this->companyFacade->findPermissions($company1));
		Assert::count(1, $this->companyFacade->findPermissions($company2));
		Assert::count(1, $this->companyFacade->findPermissions(NULL, $user1));
		Assert::count(1, $this->companyFacade->findPermissions(NULL, $user2));
		Assert::count(1, $this->companyFacade->findPermissions(NULL, $user3));

		$this->companyFacade->addPermission($company2, $user3, [CompanyRole::EDITOR]);
		Assert::count(2, $this->companyFacade->findPermissions($company2));
		Assert::count(2, $this->companyFacade->findPermissions(NULL, $user3));
	}

	public function testFindUsersByCompany()
	{
		$company1 = $this->companyDao->find(1);
		$company2 = $this->companyDao->find(2);
		$user1 = $this->userRepo->find(3);
		$user2 = $this->userRepo->find(4);
		$user3 = $this->userRepo->find(5);

		Assert::count(1, $this->companyFacade->findUsersByCompany($company1));
		Assert::same($user1->id, $this->companyFacade->findUsersByCompany($company1)[0]->id);

		$this->companyFacade->addPermission($company2, $user3, [CompanyRole::ADMIN]);
		Assert::count(2, $this->companyFacade->findUsersByCompany($company2));
		Assert::same($user2->id, $this->companyFacade->findUsersByCompany($company2)[0]->id);
		Assert::same($user3->id, $this->companyFacade->findUsersByCompany($company2)[1]->id);
	}

	public function testFindUsersByCompanyAndRole()
	{
		$company1 = $this->companyDao->find(1);
		$user1 = $this->userRepo->find(3);
		$user2 = $this->userRepo->find(4);
		$this->companyFacade->addPermission($company1, $user2, [CompanyRole::MANAGER]);

		Assert::count(1, $this->companyFacade->findUsersByCompanyAndRole($company1, CompanyRole::ADMIN));
		Assert::same($user1->id, $this->companyFacade->findUsersByCompanyAndRole($company1, CompanyRole::ADMIN)[0]->id);
		Assert::count(1, $this->companyFacade->findUsersByCompanyAndRole($company1, CompanyRole::MANAGER));
		Assert::same($user2->id, $this->companyFacade->findUsersByCompanyAndRole($company1, CompanyRole::MANAGER)[0]->id);
	}

}

$test = new CompanyFacadeFindersTest($container);
$test->run();
