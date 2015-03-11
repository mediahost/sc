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
		// TODO: DO IT!!!
		Assert::same(TRUE, TRUE);
	}

}

$test = new SkillLevelRepositoryTest($container);
$test->run();
