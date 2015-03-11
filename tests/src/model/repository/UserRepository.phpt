<?php

namespace Test\Model\Facade;

use App\Model\Entity\User;
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
		// TODO: DO IT!!!
		Assert::same(TRUE, TRUE);
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
