<?php

namespace Test\Model\Facade;

use Nette,
	Tester,
	Tester\Assert;
use App\Model\Entity;

$container = require __DIR__ . '/../../bootstrap.php';

/**
 * TEST: UserFacade
 *
 * @skip
 * @testCase
 * @phpVersion 5.4
 */
class UserFacadeTest extends Tester\TestCase
{

	const SOURCE = \App\Model\Entity\Auth::SOURCE_APP;
	const MAIL = 'tomas.jedno@seznam.cz';
	const PASSWORD = 'tomik1985';
	const EXPIRED_TOKEN = 'expiredToken';
	const VALID_TOKEN = 'validToken';
	const ACCESS_TOKEN = 'accessToken';

	/** @var \App\Model\Facade\RegistrationFacade @inject */
	public $registrationFacade;

	/** @var \App\Model\Facade\UserFacade @inject */
	public $userFacade;

	/** @var \App\Model\Facade\AuthFacade @inject */
	public $authFacade;

	/** @var \App\Model\Facade\RoleFacade @inject */
	public $roleFacade;

	/** @var \App\Model\Entity\Registration */
	private $registration;

	/** @var \Kdyby\Doctrine\EntityManager @inject */
	public $em;

	/** @var \Kdyby\Doctrine\EntityDao */
	public $userDao;

	/** @var \Kdyby\Doctrine\EntityDao */
	public $roleDao;

	/** @var Entity\User */
	private $user;

	/** @var \Doctrine\ORM\Tools\SchemaTool */
	public $schemaTool;

	public function __construct(Nette\DI\Container $container)
	{
		$this->container = $container;
		$this->container->callInjects($this);

		$this->schemaTool = new \Doctrine\ORM\Tools\SchemaTool($this->em);

		// DAOs
		$this->userDao = $this->em->getDao(\App\Model\Entity\User::getClassName());
		$this->roleDao = $this->em->getDao(\App\Model\Entity\Role::getClassName());

		// Create test user, with application account and role
		if (!$this->user = $this->userFacade->findByMail(self::MAIL)) {
			$role = $this->roleFacade->findByName('candidate');
			$this->user = $this->userFacade->create(self::MAIL, 'heslo', $role);
		}

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

	public function testCreate()
	{
		$mail = 'ringo@beatles.com';
		$password = 'yellowSubmarine';

		$role = $this->roleFacade->findByName('candidate');
		Assert::null($this->userFacade->create(self::MAIL, self::PASSWORD, $role));

		$user = $this->userFacade->create($mail, $password, $role);
		Assert::type('\App\Model\Entity\User', $user);
		Assert::same($user->mail, $mail);

		$auth = $this->authFacade->findByMail($mail);
		Assert::same($mail, $auth->key);
		Assert::same(Entity\Auth::SOURCE_APP, $auth->source);
		Assert::true(Nette\Security\Passwords::verify($password, $auth->hash));

		Assert::true(in_array(Entity\Role::ROLE_CANDIDATE, $user->getRolesPairs()));

		$this->userFacade->delete($user);
	}

	public function testDelete() // ToDo: Nevím jak zjistit, že se smazaly všechny napojené entity, asi nijak.
	{
		$role = $this->roleFacade->findByName('candidate');
		$user = $this->userFacade->create('user@delete.de', 'AuRevoir!', $role);
		$id = $user->id;

		$this->userFacade->delete($user);
		Assert::null($this->userDao->find($id));
	}

	public function testFindByMail()
	{
		$user = $this->userFacade->findByMail(self::MAIL);

		Assert::type('\App\Model\Entity\User', $user);
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

$test = new UserFacadeTest($container);
$test->run();
