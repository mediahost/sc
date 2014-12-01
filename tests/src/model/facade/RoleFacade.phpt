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

	// <editor-fold defaultstate="expanded" desc="tests">

	public function testRoles()
	{
		$this->checkCreate();
		$this->checkIsUnique();
		$this->checkGetRoles();
		$this->checkFinds();
		$this->checkIsRegistrable();
	}

	private function checkCreate()
	{
		$role = $this->roleFacade->create(Role::GUEST);
		Assert::type(Role::getClassName(), $role);
		Assert::same(Role::GUEST, $role->name);
		Assert::null($this->roleFacade->create(Role::GUEST));

		$this->roleFacade->create(Role::SIGNED);
		$this->roleFacade->create(Role::CANDIDATE);
		$this->roleFacade->create(Role::COMPANY);
		$this->roleFacade->create(Role::ADMIN);
		$this->roleFacade->create(Role::SUPERADMIN);
	}

	private function checkIsUnique()
	{
		Assert::false($this->roleFacade->isUnique(Role::CANDIDATE));
		Assert::true($this->roleFacade->isUnique('undefined role'));
	}

	private function checkGetRoles()
	{
		$roles = $this->roleFacade->getRoles();
		Assert::type('array', $roles);
		Assert::count(6, $roles);
		Assert::same($roles[1], Role::GUEST);
		Assert::same($roles[2], Role::SIGNED);
		Assert::same($roles[3], Role::CANDIDATE);
		Assert::same($roles[4], Role::COMPANY);
		Assert::same($roles[5], Role::ADMIN);
		Assert::same($roles[6], Role::SUPERADMIN);
	}

	private function checkFinds()
	{
		$role = $this->roleFacade->findByName(Role::CANDIDATE);
		Assert::same(Role::CANDIDATE, $role->name);

		$roles = [Role::CANDIDATE, Role::COMPANY, Role::ADMIN];
		$lowers = [1 => Role::GUEST, Role::SIGNED, Role::CANDIDATE, Role::COMPANY];
		Assert::same($lowers, $this->roleFacade->findLowerRoles($roles));
		$lowers[] = Role::ADMIN;
		Assert::same($lowers, $this->roleFacade->findLowerRoles($roles, TRUE));
	}

	public function checkIsRegistrable()
	{
		$modules = ['registrableRole' => TRUE];
		$settings = ['registrableRole' => ['roles' => [Role::CANDIDATE, Role::COMPANY]]];
		$this->roleFacade->settings->setModules($modules, $settings);

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
