<?php

namespace Test\Model\Facade;

use App\Model\Entity\CompanyRole;
use App\Model\Entity\Role;
use App\Model\Facade\Traits\CantDeleteUserException;
use Tester\Assert;

$container = require __DIR__ . '/../../../bootstrap.php';

/**
 * TEST: UserFacade Delete
 *
 * @testCase
 * @phpVersion 5.4
 */
class UserFacadeDeleteTest extends UserFacade
{

	const ID_COMPANY = 1;
	const ID_COMPANY_USER = 4;

	public function testDelete()
	{
		Assert::count(6, $this->roleDao->findAll());
		Assert::count(3, $this->userDao->findAll());
		Assert::count(1, $this->facebookDao->findAll());
		Assert::count(1, $this->twitterDao->findAll());
		Assert::count(1, $this->pageConfigSettingsDao->findAll());
		Assert::count(1, $this->pageDesignSettingsDao->findAll());

		$this->userFacade->deleteById(self::ID_NEW);
		$this->userDao->clear();

		Assert::count(6, $this->roleDao->findAll());
		Assert::count(2, $this->userDao->findAll());
		Assert::count(0, $this->facebookDao->findAll());
		Assert::count(0, $this->twitterDao->findAll());
		Assert::count(0, $this->pageConfigSettingsDao->findAll());
		Assert::count(0, $this->pageDesignSettingsDao->findAll());
	}

	public function testDeleteLastCompanyAdmin()
	{
		$this->importDbDataFromFile(__DIR__ . '/sql/add_company.sql');
		Assert::exception(function () {
			$this->userFacade->deleteById(self::ID_COMPANY_USER);
		}, CantDeleteUserException::class);
	}

	public function testDeleteCompanyUsers()
	{
		$this->importDbDataFromFile(__DIR__ . '/sql/add_company.sql');
		Assert::count(1, $this->companyPermissionDao->findAll());

		$companyUser1 = $this->createCompanyUser('company1@domain.com', CompanyRole::ADMIN);
		$companyUser2 = $this->createCompanyUser('company2@domain.com', CompanyRole::MANAGER);
		$companyUser3 = $this->createCompanyUser('company3@domain.com', CompanyRole::EDITOR);
		Assert::count(4, $this->companyPermissionDao->findAll());

		$this->userFacade->delete($companyUser1);
		Assert::count(3, $this->companyPermissionDao->findAll());
		$this->userFacade->delete($companyUser2);
		Assert::count(2, $this->companyPermissionDao->findAll());
		$this->userFacade->delete($companyUser3);
		Assert::count(1, $this->companyPermissionDao->findAll());
	}

	private function createCompanyUser($mail, $companyRole)
	{
		$role = $this->roleFacade->findByName(Role::COMPANY);
		$user = $this->userFacade->create($mail, 'company', $role);
		$company = $this->companyDao->find(self::ID_COMPANY);
		$this->companyFacade->addPermission($company, $user, [$companyRole]);

		return $user;
	}

}

$test = new UserFacadeDeleteTest($container);
$test->run();
