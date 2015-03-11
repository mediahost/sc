<?php

namespace Test\Model\Facade;

use App\Model\Entity\Registration;
use App\Model\Repository\RegistrationRepository;
use Nette\DI\Container;
use Tester\Assert;

$container = require __DIR__ . '/../../bootstrap.php';

/**
 * TEST: RegistrationRepository
 *
 * @testCase
 * @phpVersion 5.4
 */
class RegistrationRepositoryTest extends BaseRepository
{

	/** @var RegistrationRepository */
	protected $repository;

	function __construct(Container $container = NULL)
	{
		parent::__construct($container);
		$this->repository = $this->em->getDao(Registration::getClassName());
	}

	protected function setUp()
	{
		parent::setUp();
		$this->importDbDataFromFile(__DIR__ . '/sql/init_registrations.sql');
	}

	public function testDeleteByMail()
	{
		// TODO: DO IT!!!
		Assert::same(TRUE, TRUE);
	}

}

$test = new RegistrationRepositoryTest($container);
$test->run();
