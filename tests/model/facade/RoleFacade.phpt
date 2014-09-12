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
 * @testCase
 * @phpVersion 5.4
 */
class RoleFacadeTest extends BaseFacade
{

	function __construct(Nette\DI\Container $container)
	{
		parent::__construct($container);

		// DAOs
		$this->roleDao = $this->em->getDao(\App\Model\Entity\Role::getClassName());
	}
	
	public function setUp()
	{
		parent::setUp();
		$this->roleFacade->create(Entity\Role::ROLE_CANDIDATE);
	}

	public function testGetRoles()
	{
		$this->roleFacade->create('role1');
		$this->roleFacade->create('role2');
		
		$roles = $this->roleFacade->getRoles();
		Assert::type('array', $roles);
		Assert::same($roles[1], Entity\Role::ROLE_CANDIDATE);
		Assert::same($roles[2], 'role1');
		Assert::same($roles[3], 'role2');
	}

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

}

$test = new RoleFacadeTest($container);
$test->run();
