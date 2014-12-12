<?php

namespace Test\Model\Facade;

use App\Model\Entity\Role;
use Kdyby\Doctrine\EntityDao;
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
	/** @var EntityDao */
	private $roleDao;

	public function __construct(Container $container)
	{
		parent::__construct($container);
		$this->roleDao = $this->em->getDao(Role::getClassName());
	}

	// <editor-fold defaultstate="expanded" desc="tests">
	
	public function testCreate()
	{
		$role = $this->roleFacade->create(Role::GUEST);
		Assert::type(Role::getClassName(), $role);
		Assert::same(Role::GUEST, $role->name);
		Assert::null($this->roleFacade->create(Role::GUEST));
	}
	
	private function createAllRoles()
	{
		$this->roleFacade->create(Role::GUEST);
		$this->roleFacade->create(Role::SIGNED);
		$this->roleFacade->create(Role::CANDIDATE);
		$this->roleFacade->create(Role::COMPANY);
		$this->roleFacade->create(Role::ADMIN);
		$this->roleFacade->create(Role::SUPERADMIN);
		$this->roleDao->clear();
	}
	
	public function testIsUnique()
	{
		$this->roleFacade->create(Role::CANDIDATE);
		$this->roleDao->clear();
		Assert::false($this->roleFacade->isUnique(Role::CANDIDATE));
		Assert::true($this->roleFacade->isUnique(Role::GUEST));
	}
	
	public function testGetRoles()
	{
		$this->createAllRoles();
		$roles = $this->roleFacade->getRoles();
		Assert::type('array', $roles);
		Assert::count(6, $roles);
		Assert::same(Role::GUEST, $roles[1]);
		Assert::same(Role::SIGNED, $roles[2]);
		Assert::same(Role::CANDIDATE, $roles[3]);
		Assert::same(Role::COMPANY, $roles[4]);
		Assert::same(Role::ADMIN, $roles[5]);
		Assert::same(Role::SUPERADMIN, $roles[6]);
	}

	public function testFinds()
	{
		$this->createAllRoles();
		
		$role = $this->roleFacade->findByName(Role::CANDIDATE);
		Assert::same(Role::CANDIDATE, $role->name);

		$roles = [Role::CANDIDATE, Role::COMPANY, Role::ADMIN];
		$lowers = [1 => Role::GUEST, Role::SIGNED, Role::CANDIDATE, Role::COMPANY];
		Assert::same($lowers, $this->roleFacade->findLowerRoles($roles));
		$lowers[] = Role::ADMIN;
		Assert::same($lowers, $this->roleFacade->findLowerRoles($roles, TRUE));
	}

	public function testIsRegistrable()
	{
		$this->roleFacade->create(Role::CANDIDATE);
		$this->roleFacade->create(Role::COMPANY);
		$this->roleDao->clear();
		
		$modules = ['registrableRole' => TRUE];
		$settings = ['registrableRole' => ['roles' => [Role::CANDIDATE, Role::COMPANY]]];
		$this->defaultSettings->setModules($modules, $settings);

		$registerableRole1 = $this->roleFacade->isRegistrable(Role::CANDIDATE);
		Assert::type(Role::getClassName(), $registerableRole1);
		Assert::same(Role::CANDIDATE, $registerableRole1->name);

		$registerableRole2 = $this->roleFacade->isRegistrable(Role::COMPANY);
		Assert::type(Role::getClassName(), $registerableRole2);
		Assert::same(Role::COMPANY, $registerableRole2->name);

		Assert::false($this->roleFacade->isRegistrable(Role::ADMIN));
	}

	// </editor-fold>
}

$test = new RoleFacadeTest($container);
$test->run();
