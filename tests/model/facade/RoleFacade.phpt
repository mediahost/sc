<?php

namespace Test\Model\Facade;

use Nette,
	Tester,
	Tester\Assert;
use App\Model\Entity;

$container = require __DIR__ . '/../../bootstrap.php';

/**
 * TEST: RoleFacade
 *
 * @skip
 * @testCase
 * @phpVersion 5.4
 */
class RoleFacadeTest extends Tester\TestCase
{

	/** @var \App\Model\Facade\RoleFacade @inject */
	public $roleFacade;

	/** @var \Kdyby\Doctrine\EntityManager @inject */
	public $em;

	/** @var \Kdyby\Doctrine\EntityDao */
	public $roleDao;

	/** @var \Doctrine\ORM\Tools\SchemaTool */
	public $schemaTool;

	function __construct(Nette\DI\Container $container)
	{
		$this->container = $container;
		$this->container->callInjects($this);

		$this->schemaTool = new \Doctrine\ORM\Tools\SchemaTool($this->em);

		// DAOs
		$this->roleDao = $this->em->getDao(\App\Model\Entity\Role::getClassName());

		\Tester\Environment::lock('db', $container->getParameters()['tempDir']);
	}

	public function setUp()
	{
//		$this->schemaTool->createSchema($this->getClasses());
	}

	public function tearDown()
	{
//		$this->schemaTool->dropSchema($this->getClasses());
	}

//	public function testGetRoles()
//	{
//		
//	}

	public function testFindByName()
	{
		$role = $this->roleDao->findOneBy(['name' => Entity\Role::ROLE_CANDIDATE]);
		Assert::same(Entity\Role::ROLE_CANDIDATE, $role->name);
	}

	public function testIsUnique()
	{
		Assert::false($this->roleFacade->isUnique(Entity\Role::ROLE_CANDIDATE));
		Assert::true($this->roleFacade->isUnique('galactic_emperor'));
	}

	public function testCreate()
	{
		Assert::null($this->roleFacade->create(Entity\Role::ROLE_CANDIDATE));

		$role = $this->roleFacade->create('plumber');
		
		Assert::type('\App\Model\Entity\Role', $role);
		Assert::same('plumber', $role->name);
		
		$this->em->remove($role);
		$this->em->flush();
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

}

$test = new RoleFacadeTest($container);
$test->run();
