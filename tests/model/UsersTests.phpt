<?php

namespace Test\Model;

use Nette,
	Tester,
	Tester\Assert;

$container = require __DIR__ . '/../bootstrap.php';

/**
 * TEST: Model Users Testing
 *
 * @testCase
 * @phpVersion 5.4
 */
class UsersTest extends Tester\TestCase
{

	/** @var Nette\DI\Container */
	private $container;

	/** @var \Doctrine\ORM\EntityManager @inject */
	public $em;

	/** @var \Doctrine\ORM\Tools\SchemaTool */
	public $schemaTool;

	function __construct(Nette\DI\Container $container)
	{
		$this->container = $container;
		$this->container->callInjects($this);
		$this->schemaTool = new \Doctrine\ORM\Tools\SchemaTool($this->em);
		\Tester\Environment::lock('db', $container->getParameters()['tempDir']);
	}

	private function getClasses()
	{
		return [
			$this->em->getClassMetadata('App\Model\Entity\User'),
			$this->em->getClassMetadata('App\Model\Entity\Role'),
			$this->em->getClassMetadata('App\Model\Entity\Auth'),
			$this->em->getClassMetadata('App\Model\Entity\Registration'),
		];
	}

	public function setUp()
	{
		$this->schemaTool->updateSchema($this->getClasses());
	}

	public function tearDown()
	{
		$this->schemaTool->dropSchema($this->getClasses());
	}

	public function testInit()
	{
		Assert::same('a', 'a');
	}

}

$test = new UsersTest($container);
$test->run();
