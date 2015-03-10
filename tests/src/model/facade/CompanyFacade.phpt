<?php

namespace Test\Model\Facade;

use App\Model\Entity\Company;
use App\Model\Entity\CompanyRole;
use App\Model\Entity\User;
use Kdyby\Doctrine\EntityDao;
use Nette\DI\Container;
use Tester\Assert;

$container = require __DIR__ . '/../../bootstrap.php';

/**
 * TEST: CompanyFacade
 *
 * @testCase
 * @phpVersion 5.4
 */
class CompanyFacadeTest extends BaseFacade
{

	/** @var EntityDao */
	private $userDao;

	/** @var EntityDao */
	private $companyDao;

	/** @var User */
	private $user;

	public function __construct(Container $container)
	{
		parent::__construct($container);
		$this->userDao = $this->em->getDao(User::getClassName());
		$this->companyDao = $this->em->getDao(Company::getClassName());
		$this->companyRoleDao = $this->em->getDao(CompanyRole::getClassName());
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

	public function testGetters()
	{
		$company1 = new Company('my company 1');
		$this->companyDao->save($company1);
		$company2 = new Company('my company 2');
		$this->companyDao->save($company2);
		$company3 = new Company('my company 3');
		$this->companyDao->save($company3);

		$companies = $this->companyFacade->getCompaniesNames();
		Assert::same([1 => 'my company 1', 2 => 'my company 2', 3 => 'my company 3'], $companies);

		$role1 = new CompanyRole(CompanyRole::ADMIN);
		$this->companyRoleDao->save($role1);
		$role2 = new CompanyRole(CompanyRole::MANAGER);
		$this->companyRoleDao->save($role2);
		$role3 = new CompanyRole(CompanyRole::EDITOR);
		$this->companyRoleDao->save($role3);

		$roles = $this->companyFacade->getRolesNames();
		Assert::same([1 => CompanyRole::ADMIN, 2 => CompanyRole::MANAGER, 3 => CompanyRole::EDITOR], $roles);
	}

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

	public function testCheckers()
	{
		$company = new Company('my company');
		$company->companyId = 'myCompany';
		$this->companyDao->save($company);
		
		Assert::false($this->companyFacade->isUniqueId($company->companyId));
		Assert::true($this->companyFacade->isUniqueId($company->companyId, $company->id));
		Assert::true($this->companyFacade->isUniqueId('uniqueId'));
		
		Assert::false($this->companyFacade->isUniqueName($company->name));
		Assert::true($this->companyFacade->isUniqueName($company->name, $company->id));
		Assert::true($this->companyFacade->isUniqueName('unique name'));
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
