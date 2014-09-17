<?php

namespace Test\Model\Entity;

use Nette,
	Tester,
	Tester\Assert;

use App\Model\Entity;

$container = require __DIR__ . '/../../bootstrap.php';

/**
 * TEST: User entity
 *
 * @testCase
 * @phpVersion 5.4
 */
class UserTest extends Tester\TestCase
{
	const U_MAIL = 'jack@sg1.sg.gov';
	const U_NAME = "Jack O'Neill";
	const U_RECOVERY_TOKEN = 'recov3Rytoken';

	/** @var \App\Model\Entity\User */
	private $user;

	public function setUp()
	{
		$this->user = new Entity\User();
	}

	public function tearDown()
	{
		unset($this->user);
	}
	
	public function testAddAuth()
	{
		Assert::count(0, $this->user->auths);
		
		$auth = new Entity\Auth();
		$this->user->addAuth($auth);
		
		Assert::type(Entity\Auth::getClassName(), $this->user->auths[0]);
	}
	
	public function testAddRole()
	{
		$roleA = (new Entity\Role())->setName('Role A');
		$roleB = (new Entity\Role())->setName('Role B');
		$roleC = (new Entity\Role())->setName('Role C');
		
		Assert::count(0, $this->user->roles); // No roles
		
		$this->user->addRole($roleA); // Add first role
		Assert::count(1, $this->user->roles);
		
		$this->user->addRole($roleA); // Add the same role
		Assert::count(1, $this->user->roles);
		
		$this->user->addRole($roleB); // Add another role
		Assert::count(2, $this->user->roles);
		
		$this->user->addRole($roleC, TRUE); // Clear roles and add new one
		Assert::count(1, $this->user->roles);
		Assert::same('Role C', $this->user->roles[0]->name);
		
		$this->user->addRole([$roleA, $roleC]); // Add array with duplicit roles
		Assert::count(2, $this->user->roles);
		
		$this->user->clearRoles();
		Assert::count(0, $this->user->roles);
		
	}
	
	public function testGetRolesKeys()
	{
		$roleA = (new Entity\Role())->setName('Role A');
		$roleB = (new Entity\Role())->setName('Role B');
		$roleC = (new Entity\Role())->setName('Role C');
		
		$this->user->addRole([$roleB, $roleA, $roleC]);
		Assert::same([NULL, NULL, NULL], $this->user->getRolesKeys());
	}
	
	public function testGetRolesPairs()
	{
		$roleA = (new Entity\Role())->setName('Role A');
		$roleB = (new Entity\Role())->setName('Role B');
		$roleC = (new Entity\Role())->setName('Role C');
		
		$this->user->addRole([$roleB, $roleA, $roleC]);
		Assert::same([NULL => 'Role C'], $this->user->getRolesPairs());
	}

	public function testRemoveRole()
	{
		$roleA = (new Entity\Role())->setName('Role A');
		$roleB = (new Entity\Role())->setName('Role B');
		
		$this->user->addRole($roleA);
		Assert::count(1, $this->user->roles);
		$this->user->addRole($roleB);
		Assert::count(2, $this->user->roles);
		$this->user->removeRole($roleA);
		Assert::count(1, $this->user->roles);
		$this->user->removeRole($roleB);
		Assert::count(0, $this->user->roles);
	}
	
	public function testToArray()
	{
		$this->user->mail = self::U_MAIL;	
		$this->user->name = self::U_NAME;		
		Assert::true(TRUE);
	}

	public function testSetAndGet()
	{
		Assert::type('array', $this->user->auths);
		Assert::type('array', $this->user->roles);

		$this->user->mail = self::U_MAIL;
		Assert::same(self::U_MAIL, $this->user->mail);
		
		$this->user->name = self::U_NAME;
		Assert::same(self::U_NAME, $this->user->name);
		
		$this->user->recoveryToken = self::U_RECOVERY_TOKEN;
		Assert::same(self::U_RECOVERY_TOKEN, $this->user->recoveryToken);

		$tomorrow = new \DateTime('now + 1 day');
		$this->user->recoveryExpiration = $tomorrow;
		Assert::equal($tomorrow, $this->user->recoveryExpiration);
	}
	
	public function testToString()
	{
		$this->user->mail = self::U_MAIL;
		Assert::same(self::U_MAIL, (string) $this->user);
	}

	public function testSetRecovery()
	{
		$expiration = new \DateTime('now + 3 hours');

		$this->user->setRecovery(self::U_RECOVERY_TOKEN, $expiration);

		Assert::same(self::U_RECOVERY_TOKEN, $this->user->recoveryToken);
		Assert::equal($expiration, $this->user->recoveryExpiration);
	}

	public function testUnsetRecovery()
	{
		$token = Nette\Utils\Strings::random(32);
		$expiration = new \DateTime();
		$this->user->setRecovery($token, $expiration);
		$this->user->unsetRecovery();

		Assert::null($this->user->recoveryToken);
		Assert::null($this->user->recoveryExpiration);
	}

}

$test = new UserTest();
$test->run();
