<?php

namespace Test\Model\Facade;

use Nette,
	Tester,
	Tester\Assert;

use App\Model\Entity,
	Kdyby\Doctrine\EntityDao;

$container = require __DIR__ . '/../../bootstrap.php';

/**
 * TEST: UserFacade
 *
 * @testCase
 * @phpVersion 5.4
 */
class UserFacadeTest extends BaseFacade
{

	const SOURCE = \App\Model\Entity\Auth::SOURCE_APP;
	const MAIL = 'tomas.jedno@seznam.cz';
	const PASSWORD = 'tomik1985';
	const EXPIRED_TOKEN = 'expiredToken';
	const VALID_TOKEN = 'validToken';
	const ACCESS_TOKEN = 'accessToken';

	/** @var EntityDao */
	public $userDao;

	/** @var EntityDao */
	public $authDao;

	/** @var EntityDao */
	public $roleDao;

	/** @var Entity\User */
	private $user;

	public function __construct(Nette\DI\Container $container)
	{
		parent::__construct($container);
		$this->authDao = $this->em->getDao(Entity\Auth::getClassName());
		$this->userDao = $this->em->getDao(Entity\User::getClassName());
		$this->roleDao = $this->em->getDao(Entity\Role::getClassName());
	}

	public function setUp()
	{
		parent::setUp();
		$role = $this->roleFacade->create(Entity\Role::ROLE_CANDIDATE);
		$this->user = $this->userFacade->create(self::MAIL, 'heslo', $role);
	}

	public function testCreate()
	{
		$mail = 'ringo@beatles.com';
		$password = 'yellowSubmarine';

		$role = $this->roleFacade->findByName(Entity\Role::ROLE_CANDIDATE);
		Assert::null($this->userFacade->create(self::MAIL, self::PASSWORD, $role));

		$user = $this->userFacade->create($mail, $password, $role);
		Assert::type(Entity\User::getClassName(), $user);
		Assert::same($user->mail, $mail);

		$auth = $this->authFacade->findByMail($mail);
		Assert::same($mail, $auth->key);
		Assert::same(Entity\Auth::SOURCE_APP, $auth->source);
		Assert::true(Nette\Security\Passwords::verify($password, $auth->hash));

		Assert::true(in_array(Entity\Role::ROLE_CANDIDATE, $user->getRolesPairs()));

		$this->userFacade->delete($user);
	}

	public function testDelete()
	{
		$this->userFacade->delete($this->user);

		Assert::count(1, $this->roleDao->findAll());
		Assert::count(0, $this->authDao->findAll());
		Assert::count(0, $this->userDao->findAll());
	}

	public function delete() // ToDo: Nevím jak zjistit, že se smazaly všechny napojené entity, asi nijak.
	{
		$role = $this->roleFacade->findByName(Entity\Role::ROLE_CANDIDATE);
		$user = $this->userFacade->create('user@delete.de', 'AuRevoir!', $role);
		$id = $user->id;

		$this->userFacade->delete($user);
		Assert::null($this->userDao->find($id));
	}

	public function testFindByMail()
	{
		$user = $this->userFacade->findByMail(self::MAIL);

		Assert::type(Entity\User::getClassName(), $user);
		Assert::same(self::MAIL, $user->mail);
	}

	public function testIsUnique()
	{
		Assert::false($this->userFacade->isUnique(self::MAIL));
		Assert::true($this->userFacade->isUnique('not@unique.com'));
	}

	public function testSetAppPassword()
	{
		$newPassword = 'newPassword2014';

		foreach ($this->user->auths as $auth) {
			$this->em->remove($auth);
		}

		$this->em->flush();

		// Test when no application Auth (create new Auth)
		$this->userFacade->setAppPassword($this->user, self::PASSWORD);
		$auth = $this->authFacade->findByMail(self::MAIL);
		Assert::true(\Nette\Security\Passwords::verify(self::PASSWORD, $auth->hash));

		// Test when application Auth exists (set new password)
		$this->userFacade->setAppPassword($this->user, $newPassword);
		$auth = $this->authFacade->findByMail(self::MAIL);
		Assert::true(\Nette\Security\Passwords::verify($newPassword, $auth->hash));
	}

	public function testSetRecovery()
	{
		$this->user = $this->userFacade->setRecovery($this->user);

		/* @var $user Entity\User */
		$user = $this->userDao->find($this->user->id);
		Assert::same($this->user->recoveryToken, $user->recoveryToken);
		Assert::equal($this->user->recoveryExpiration, $user->recoveryExpiration);
	}

	public function testHardDelete()
	{
		$id = $this->user->id;

		$this->userFacade->hardDelete($id);

		Assert::count(0, $this->authFacade->findByUser($this->user));
		Assert::null($this->userDao->find($id));
	}

}

$test = new UserFacadeTest($container);
$test->run();
