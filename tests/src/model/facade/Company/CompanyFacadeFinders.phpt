<?php

namespace Test\Model\Facade;

use App\Model\Entity\Company;
use App\Model\Entity\CompanyRole;
use App\Model\Entity\User;
use Tester\Assert;

$container = require __DIR__ . '/../../../bootstrap.php';

/**
 * TEST: CompanyFacade Finders
 * TODO: for Revision!
 * @skip
 *
 * @testCase
 * @phpVersion 5.4
 */
class CompanyFacadeFindersTest extends CompanyFacade
{

	public function testFinders()
	{
		$company1 = new Company('my company 1');
		$company1->companyId = 'myCompanyOne';
		$company1->name = 'My Company One';
		$this->companyDao->save($company1);
		$company2 = new Company('my company 2');
		$company2->companyId = 'myCompanyTwo';
		$company2->name = 'My Company Two';
		$this->companyDao->save($company2);

		Assert::same(1, $this->companyFacade->find($company1)->id);
		Assert::same(1, $this->companyFacade->find(1)->id);
		Assert::same(2, $this->companyFacade->find($company2)->id);
		Assert::same(2, $this->companyFacade->find(2)->id);
		Assert::null($this->companyFacade->find('unknown_ID'));

		Assert::same(1, $this->companyFacade->findByCompanyId('myCompanyOne')->id);
		Assert::same(2, $this->companyFacade->findByCompanyId('myCompanyTwo')->id);
		Assert::null($this->companyFacade->findByCompanyId('unknownID'));

		Assert::same(1, $this->companyFacade->findByName('My Company One')->id);
		Assert::same(2, $this->companyFacade->findByName('My Company Two')->id);
		Assert::null($this->companyFacade->findByName('Unknown Company'));

		$companyRole1 = $this->companyFacade->createRole(CompanyRole::EDITOR);
		$companyRole2 = $this->companyFacade->createRole(CompanyRole::MANAGER);
		$companyRole3 = $this->companyFacade->createRole(CompanyRole::ADMIN);

		Assert::same(CompanyRole::EDITOR, $this->companyFacade->findRoleByName(CompanyRole::EDITOR)->name);
		Assert::same(CompanyRole::MANAGER, $this->companyFacade->findRoleByName(CompanyRole::MANAGER)->name);
		Assert::same(CompanyRole::ADMIN, $this->companyFacade->findRoleByName(CompanyRole::ADMIN)->name);
		Assert::null($this->companyFacade->findRoleByName('unknown name'));

		$user1 = new User('user1@mail.com');
		$this->userDao->save($user1);
		$user2 = new User('user2@mail.com');
		$this->userDao->save($user2);
		$user3 = new User('user3@mail.com');
		$this->userDao->save($user3);
		$permission1 = $this->companyFacade->addPermission($company1, $user1, [$companyRole1, $companyRole2]);
		$permission2 = $this->companyFacade->addPermission($company2, $user2, [$companyRole3]);
		$permission3 = $this->companyFacade->addPermission($company2, $user3, [$companyRole2]);
		$permission4 = $this->companyFacade->addPermission($company2, $user1, [$companyRole3]);

		Assert::same(1, $permission1->id);
		Assert::same(2, $permission2->id);
		Assert::same(3, $permission3->id);
		Assert::same(4, $permission4->id);
		Assert::same(1, $this->companyFacade->findPermission($company1, $user1)->id);
		Assert::same(2, $this->companyFacade->findPermission($company2, $user2)->id);

		$findedCompany1 = $this->companyDao->find($company1->id);
		$findedCompany2 = $this->companyDao->find($company2->id);
		$findedUser1 = $this->userDao->find($user1->id);
		$findedUser2 = $this->userDao->find($user2->id);
		$findedUser3 = $this->userDao->find($user3->id);

		Assert::same([], $this->companyFacade->findPermissions());
		Assert::count(1, $this->companyFacade->findPermissions($findedCompany1));
		Assert::count(3, $this->companyFacade->findPermissions($findedCompany2));
		Assert::count(2, $this->companyFacade->findPermissions(NULL, $findedUser1));
		Assert::count(1, $this->companyFacade->findPermissions(NULL, $findedUser2));
		Assert::count(1, $this->companyFacade->findPermissions(NULL, $findedUser3));

		Assert::count(1, $this->companyFacade->findUsersByCompany($findedCompany1));
		Assert::same($user1->id, $this->companyFacade->findUsersByCompany($findedCompany1)[0]->id);
		Assert::count(3, $this->companyFacade->findUsersByCompany($findedCompany2));
		Assert::same($user2->id, $this->companyFacade->findUsersByCompany($findedCompany2)[0]->id);

		Assert::count(1, $this->companyFacade->findUsersByCompanyAndRole($findedCompany2, $companyRole2));
		Assert::same($user3->id, $this->companyFacade->findUsersByCompanyAndRole($findedCompany2, $companyRole2)[0]->id);
	}

}

$test = new CompanyFacadeFindersTest($container);
$test->run();
