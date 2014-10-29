<?php

namespace Test\Model\Entity;

use App\Model\Entity\Auth;
use App\Model\Entity\Role;
use App\Model\Entity\User;
use DateTime;
use Kdyby\Doctrine\EntityDao;
use Nette\DI\Container;
use Nette\Utils\Strings;
use Test\ParentTestCase;
use Tester\Assert;
use Tester\Environment;

$container = require __DIR__ . '/../../bootstrap.php';

/**
 * TEST: User entity
 *
 * @testCase
 * @phpVersion 5.4
 */
class UserTest extends ParentTestCase
{

	const U_MAIL = 'jack@sg1.sg.gov';
	const U_NAME = "Jack O'Neill";
	const U_RECOVERY_TOKEN = 'recov3Rytoken';

	/** @var User */
	private $user;

	/** @var EntityDao */
	private $roleDao;

	/** @var EntityDao */
	private $userDao;

	public function __construct(Container $container)
	{
		parent::__construct($container);
		Environment::lock('db', LOCK_DIR);

		$this->roleDao = $this->em->getDao(Role::getClassName());
		$this->userDao = $this->em->getDao(User::getClassName());
	}

	public function setUp()
	{
		$this->user = new User();
	}

	public function tearDown()
	{
		unset($this->user);
	}

	// <editor-fold defaultstate="collapsed" desc="tests">

	public function testAddAuth()
	{
		Assert::count(0, $this->user->auths);

		$auth = new Auth();
		$this->user->addAuth($auth);

		Assert::type(Auth::getClassName(), $this->user->auths[0]);
	}

	public function testAddRole()
	{
		$roleA = (new Role())->setName('Role A');
		$roleB = (new Role())->setName('Role B');
		$roleC = (new Role())->setName('Role C');

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
		$this->updateSchema();

		$roleA = $this->roleDao->save((new Role())->setName('Role A'));
		$roleB = $this->roleDao->save((new Role())->setName('Role B'));
		$roleC = $this->roleDao->save((new Role())->setName('Role C'));

		$this->user->addRole([$roleB, $roleC, $roleA, $roleA, $roleC]);
		Assert::same([$roleB->id, $roleC->id, $roleA->id], $this->user->getRolesKeys());

		$this->dropSchema();
	}

	public function testGetRolesPairs()
	{
		$roleA = (new Role())->setName('Role A');
		$roleB = (new Role())->setName('Role B');
		$roleC = (new Role())->setName('Role C');

		$this->user->addRole([$roleB, $roleA, $roleC]);
		Assert::same([NULL => 'Role C'], $this->user->getRolesPairs());
	}

	public function testRemoveRole()
	{
		$roleA = (new Role())->setName('Role A');
		$roleB = (new Role())->setName('Role B');

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
		$this->updateSchema();

		$roleA = $this->roleDao->save((new Role())->setName('Role A'));
		$roleB = $this->roleDao->save((new Role())->setName('Role B'));

		$this->user->mail = self::U_MAIL;
		$this->user->name = self::U_NAME;
		$this->user->addRole([$roleB, $roleA]);

		$user = $this->userDao->save($this->user);
		$array = $user->toArray();

		Assert::same($user->id, $array['id']);
		Assert::same(self::U_MAIL, $array['mail']);
		Assert::same(self::U_NAME, $array['name']);
		Assert::type('array', $array['role']);
		Assert::type(Role::getClassName(), $array['role'][0]);
		Assert::same('Role B', $array['role'][0]->name);
		Assert::type(Role::getClassName(), $array['role'][1]);
		Assert::same('Role A', $array['role'][1]->name);

		$this->dropSchema();
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

		$tomorrow = new DateTime('now + 1 day');
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
		$expiration = new DateTime('now + 3 hours');

		$this->user->setRecovery(self::U_RECOVERY_TOKEN, $expiration);

		Assert::same(self::U_RECOVERY_TOKEN, $this->user->recoveryToken);
		Assert::equal($expiration, $this->user->recoveryExpiration);
	}

	public function testUnsetRecovery()
	{
		$token = Strings::random(32);
		$expiration = new DateTime();
		$this->user->setRecovery($token, $expiration);
		$this->user->unsetRecovery();

		Assert::null($this->user->recoveryToken);
		Assert::null($this->user->recoveryExpiration);
	}

	// </editor-fold>

	protected function getClasses()
	{
		return [
			$this->em->getClassMetadata(User::getClassName()),
			$this->em->getClassMetadata(Role::getClassName()),
		];
	}

}

$test = new UserTest($container);
$test->run();
