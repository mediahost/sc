<?php

namespace Test\Model\Facade;

use App\Model\Entity\CompanyPermission;
use App\Model\Repository\CompanyPermissionRepository;
use Nette\DI\Container;
use Tester\Assert;

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
		// TODO: DO IT!!!
		Assert::same(TRUE, TRUE);
	}

}

$test = new CompanyPermissionRepositoryTest($container);
$test->run();
