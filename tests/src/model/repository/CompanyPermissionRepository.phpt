<?php

namespace Test\Model\Facade;

use App\Model\Entity\Company;
use App\Model\Entity\CompanyPermission;
use App\Model\Entity\CompanyRole;
use App\Model\Repository\CompanyPermissionRepository;
use Nette\DI\Container;
use Tester\Assert;
use Tracy\Debugger;

$container = require __DIR__ . '/../../bootstrap.php';

/**
 * TEST: CompanyPermissionRepository
 *
 * @testCase
 * @phpVersion 5.4
 */
class CompanyPermissionRepositoryTest extends BaseRepository
{

	/** @var CompanyPermissionRepository */
	protected $repository;

	function __construct(Container $container = NULL)
	{
		parent::__construct($container);
		$this->repository = $this->em->getDao(CompanyPermission::getClassName());
	}

	protected function setUp()
	{
		parent::setUp();
		$this->importDbDataFromFile(__DIR__ . '/sql/init_company_permissions.sql');
	}

	public function testFindByCompanyAndRoleId()
	{
		$companyDao = $this->em->getDao(Company::getClassName());
		$company1 = $companyDao->find(1);
		$company2 = $companyDao->find(2);
		$company3 = $companyDao->find(3);

		$companyRoleDao = $this->em->getDao(CompanyRole::getClassName());
		$roleEditor = $companyRoleDao->find(1);
		$roleManager = $companyRoleDao->find(2);
		$roleAdmin = $companyRoleDao->find(3);

		$company1AdminsIds = $this->repository->findByCompanyAndRoleId($company1, $roleAdmin->id, TRUE);
		Assert::same([[1 => '3']], $company1AdminsIds);

		$company1Admins = $this->repository->findByCompanyAndRoleId($company1, $roleAdmin->id);
		$company2Admins = $this->repository->findByCompanyAndRoleId($company2, $roleAdmin->id);
		$company3Admins = $this->repository->findByCompanyAndRoleId($company3, $roleAdmin->id);
		$company1Managers = $this->repository->findByCompanyAndRoleId($company1, $roleManager->id);
		$company2Managers = $this->repository->findByCompanyAndRoleId($company2, $roleManager->id);
		$company3Managers = $this->repository->findByCompanyAndRoleId($company3, $roleManager->id);
		$company1Editors = $this->repository->findByCompanyAndRoleId($company1, $roleEditor->id);
		$company2Editors = $this->repository->findByCompanyAndRoleId($company2, $roleEditor->id);
		$company3Editors = $this->repository->findByCompanyAndRoleId($company3, $roleEditor->id);

		Assert::count(1, $company1Admins);
		Assert::count(1, $company2Admins);
		Assert::count(2, $company3Admins);

		Assert::count(2, $company1Managers);
		Assert::count(2, $company2Managers);
		Assert::count(3, $company3Managers);

		Assert::count(1, $company1Editors);
		Assert::count(1, $company2Editors);
		Assert::count(4, $company3Editors);
	}

}

$test = new CompanyPermissionRepositoryTest($container);
$test->run();
