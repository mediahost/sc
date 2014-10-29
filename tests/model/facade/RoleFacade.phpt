<?php

namespace Test\Model\Facade;

use App\Model\Entity\Role;
use Nette\DI\Container;
use Tester\Assert;

$container = require __DIR__ . '/../../bootstrap.php';

/**
 * TEST: RoleFacade
 *
 * @testCase
 * @phpVersion 5.4
 */
class RoleFacadeTest extends BaseFacade
{

	public function __construct(Container $container)
	{
		parent::__construct($container);
		$this->roleDao = $this->em->getDao(Role::getClassName());
	}

	public function setUp()
	{
		parent::setUp();
		$this->roleFacade->create(Role::ROLE_CANDIDATE);
	}

	// <editor-fold defaultstate="expanded" desc="tests">

	public function testGetRoles()
	{
		$this->roleFacade->create('role1');
		$this->roleFacade->create('role2');

		$roles = $this->roleFacade->getRoles();
		Assert::type('array', $roles);
		Assert::same($roles[1], Role::ROLE_CANDIDATE);
		Assert::same($roles[2], 'role1');
		Assert::same($roles[3], 'role2');
	}

	public function testFindByName()
	{
		$role = $this->roleDao->findOneBy(['name' => Role::ROLE_CANDIDATE]);
		Assert::same(Role::ROLE_CANDIDATE, $role->name);
	}

	public function testIsUnique()
	{
		Assert::false($this->roleFacade->isUnique(Role::ROLE_CANDIDATE));
		Assert::true($this->roleFacade->isUnique('galactic_emperor'));
	}

	public function testCreate()
	{
		Assert::null($this->roleFacade->create(Role::ROLE_CANDIDATE));

		$role = $this->roleFacade->create('plumber');

		Assert::type('\App\Model\Entity\Role', $role);
		Assert::same('plumber', $role->name);

		$this->em->remove($role);
		$this->em->flush();
	}

	// </editor-fold>

}

$test = new RoleFacadeTest($container);
$test->run();
