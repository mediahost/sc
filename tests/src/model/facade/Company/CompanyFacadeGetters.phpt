<?php

namespace Test\Model\Facade;

use App\Model\Entity\Company;
use App\Model\Entity\CompanyRole;
use Tester\Assert;

$container = require __DIR__ . '/../../../bootstrap.php';

/**
 * TEST: CompanyFacade Getters
 * TODO: for Revision!
 * @skip
 *
 * @testCase
 * @phpVersion 5.4
 */
class CompanyFacadeGettersTest extends CompanyFacade
{

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

}

$test = new CompanyFacadeGettersTest($container);
$test->run();
