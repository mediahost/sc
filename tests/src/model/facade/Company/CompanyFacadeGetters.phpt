<?php

namespace Test\Model\Facade;

use App\Model\Entity\Company;
use App\Model\Entity\CompanyRole;
use Tester\Assert;

$container = require __DIR__ . '/../../../bootstrap.php';

/**
 * TEST: CompanyFacade Getters
 *
 * @testCase
 * @phpVersion 5.4
 */
class CompanyFacadeGettersTest extends CompanyFacade
{

	public function testGetCompaniesNames()
	{
		$companies = $this->companyFacade->getCompaniesNames();
		Assert::same([
				1 => 'company1',
				2 => 'company2',
				3 => 'company3',
				4 => 'company4',
				5 => 'company5',
		], $companies);
	}

	public function testGetRolesNames()
	{
		$roles = $this->companyFacade->getRolesNames();
		Assert::same([
				1 => CompanyRole::EDITOR,
				2 => CompanyRole::MANAGER,
				3 => CompanyRole::ADMIN,
		], $roles);
	}

}

$test = new CompanyFacadeGettersTest($container);
$test->run();
