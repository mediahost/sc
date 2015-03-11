<?php

namespace Test\Model\Facade;

use App\Model\Entity\Job;
use App\Model\Repository\JobRepository;
use Nette\DI\Container;
use Tester\Assert;

$container = require __DIR__ . '/../../bootstrap.php';

/**
 * TEST: JobRepository
 *
 * @testCase
 * @phpVersion 5.4
 */
class JobRepositoryTest extends BaseRepository
{

	/** @var JobRepository */
	protected $repository;

	function __construct(Container $container = NULL)
	{
		parent::__construct($container);
		$this->repository = $this->em->getDao(Job::getClassName());
	}

	protected function setUp()
	{
		parent::setUp();
		$this->importDbDataFromFile(__DIR__ . '/sql/init_jobs.sql');
	}

	public function testFindBySkillKnows()
	{
		// TODO: DO IT!!!
		Assert::same(TRUE, TRUE);
	}

}

$test = new JobRepositoryTest($container);
$test->run();
