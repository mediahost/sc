<?php

namespace Test\Model\Facade;

use App\Model\Entity\SkillLevel;
use App\Model\Repository\SkillLevelRepository;
use Nette\DI\Container;
use Tester\Assert;

$container = require __DIR__ . '/../../bootstrap.php';

/**
 * TEST: SkillLevelRepository
 *
 * @testCase
 * @phpVersion 5.4
 */
class SkillLevelRepositoryTest extends BaseRepository
{

	/** @var SkillLevelRepository */
	protected $repository;

	function __construct(Container $container = NULL)
	{
		parent::__construct($container);
		$this->repository = $this->em->getDao(SkillLevel::getClassName());
	}

	protected function setUp()
	{
		parent::setUp();
		$this->importDbDataFromFile(__DIR__ . '/sql/init_skill_levels.sql');
	}

	public function testFindPairsName()
	{
		$orderAsc = $this->repository->findPairsName();
		$orderDesc = $this->repository->findPairsName(FALSE);

		Assert::same([
				1 => 'N/A',
				2 => 'Basic',
				3 => 'Intermediate',
				4 => 'Advanced',
				5 => 'Expert',
		], $orderAsc);
		Assert::same([
				5 => 'Expert',
				4 => 'Advanced',
				3 => 'Intermediate',
				2 => 'Basic',
				1 => 'N/A',
		], $orderDesc);
	}

}

$test = new SkillLevelRepositoryTest($container);
$test->run();
