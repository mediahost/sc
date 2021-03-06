<?php

namespace Test\Model\Facade;

use App\Model\Entity\Role;
use App\Model\Entity\User;
use App\Model\Facade\RoleFacade;
use App\Model\Repository\RepositoryException;
use App\Model\Repository\UserRepository;
use Nette\DI\Container;
use Tester\Assert;

$container = require __DIR__ . '/../../bootstrap.php';

/**
 * TEST: UserRepository
 *
 * @testCase
 * @phpVersion 5.4
 */
class UserRepositoryTest extends BaseRepository
{

	/** @var RoleFacade @inject */
	public $roleFacade;

	/** @var UserRepository */
	protected $repository;

	function __construct(Container $container = NULL)
	{
		parent::__construct($container);
		$this->repository = $this->em->getDao(User::getClassName());
	}

	protected function setUp()
	{
		parent::setUp();
		$this->importDbDataFromFile(__DIR__ . '/sql/init_users.sql');
	}

	public function testFindPairsByRoleId()
	{
		$roleSigned = $this->roleFacade->findByName(Role::SIGNED);
		$roleCandidate = $this->roleFacade->findByName(Role::CANDIDATE);
		$roleCompany = $this->roleFacade->findByName(Role::COMPANY);
		$roleAdmin = $this->roleFacade->findByName(Role::ADMIN);
		$roleSuperadmin = $this->roleFacade->findByName(Role::SUPERADMIN);

		$signedMails = $this->repository->findPairsByRoleId($roleSigned->id, 'mail');
		$candidatesMails = $this->repository->findPairsByRoleId($roleCandidate->id, 'mail');
		$companiesMails = $this->repository->findPairsByRoleId($roleCompany->id, 'mail');
		$adminMails = $this->repository->findPairsByRoleId($roleAdmin->id, 'mail');
		$superadminMails = $this->repository->findPairsByRoleId($roleSuperadmin->id, 'mail');

		Assert::same([], $signedMails);
		Assert::same([
				3 => 'candidate1@domain.com',
				4 => 'candidate2@domain.com',
				5 => 'candidate3@domain.com',
				6 => 'candidate4@domain.com',
				7 => 'candidate5@domain.com',
				8 => 'candidate6@domain.com',
		], $candidatesMails);
		Assert::same([
				9 => 'company1@domain.com',
				10 => 'company2@domain.com',
				11 => 'company3@domain.com',
		], $companiesMails);
		Assert::same([
				1 => 'admin',
				12 => 'admin1@domain.com',
				13 => 'admin2@domain.com',
		], $adminMails);
		Assert::same([
				2 => 'superadmin',
		], $superadminMails);
	}

	public function testDelete()
	{
		Assert::exception(function () {
			$user = new User();
			$this->repository->delete($user);
		}, RepositoryException::class);
	}

}

$test = new UserRepositoryTest($container);
$test->run();
