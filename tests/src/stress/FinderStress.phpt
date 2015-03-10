<?php

namespace Test\Model\Entity;

use App\Model\Entity\Registration;
use App\Model\Entity\Role;
use App\Model\Entity\User;
use DateTime;
use Kdyby\Doctrine\MemberAccessException;
use Nette\Security\Passwords;
use Test\StressTestCase;
use Tester\Assert;
use Tester\TestCase;

$container = require __DIR__ . '/../bootstrap.php';

/**
 * TEST: Finder stress
 *
 * @testCase
 * @phpVersion 5.4
 */
class FinderStressTest extends StressTestCase
{

	protected function setUp()
	{
		$this->updateSchema();
		$this->importDbDataFromFile(__DIR__ . '/../sql/users.sql');
		parent::setUp();
	}

	protected function tearDown()
	{
		parent::tearDown();
		$this->dropSchema();
	}

	public function testFinder()
	{
		$this->setLimitTime(2);

		$userDao = $this->em->getDao(User::class);
		$finded = $userDao->find(1);
		Assert::type(User::class, $finded);
	}

}

$test = new FinderStressTest($container);
$test->run();
