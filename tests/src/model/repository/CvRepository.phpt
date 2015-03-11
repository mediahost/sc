<?php

namespace Test\Model\Facade;

use App\Model\Entity\Cv;
use App\Model\Repository\CvRepository;
use Nette\DI\Container;
use Tester\Assert;

$container = require __DIR__ . '/../../bootstrap.php';

/**
 * TEST: CvRepository
 *
 * @testCase
 * @phpVersion 5.4
 */
class CvRepositoryTest extends BaseRepository
{

	/** @var CvRepository */
	protected $repository;

	function __construct(Container $container = NULL)
	{
		parent::__construct($container);
		$this->repository = $this->em->getDao(Cv::getClassName());
	}

	protected function setUp()
	{
		parent::setUp();
		$this->importDbDataFromFile(__DIR__ . '/sql/init_cvs.sql');
	}

	public function testFindBySkillRequests()
	{
		// TODO: DO IT!!!
		Assert::same(TRUE, TRUE);
	}

}

$test = new CvRepositoryTest($container);
$test->run();
