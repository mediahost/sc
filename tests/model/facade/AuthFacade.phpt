<?php

namespace Test\Model\Facade;

use Nette,
	Tester,
	Tester\Assert;
use App\Model\Entity;

$container = require __DIR__ . '/../../bootstrap.php';

/**
 * TEST: AuthFacade
 *
 * @skip
 * @testCase
 * @phpVersion 5.4
 */
class AuthFacadeTest extends Tester\TestCase
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

	/** @var Entity\User */
	private $user;

	/** @var \Doctrine\ORM\Tools\SchemaTool */
	public $schemaTool;

	function __construct(Nette\DI\Container $container)
	{
		$this->container = $container;
		$this->container->callInjects($this);

		$this->schemaTool = new \Doctrine\ORM\Tools\SchemaTool($this->em);

		// DAOs
		$this->userDao = $this->em->getDao(\App\Model\Entity\User::getClassName());

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

	/**
	 * findByKey($key, $source)
	 * ToDo: Otestovat, zda vrátí požadovanou entitu Auth toho Usera snad ani není potřeba testovat.
	 * Teoreticky by stačilo vložit pouze Auth a otestovat ji.
	 */
	public function testFindByKey()
	{
		$auth = $this->authFacade->findByKey(self::SOURCE, self::MAIL);

		Assert::type('\App\Model\Entity\Auth', $auth);
		Assert::same(self::SOURCE, $auth->source);
		Assert::same(self::MAIL, $auth->key);
	}

	/**
	 * ToDo: Opět asi není potřeba testovat víc než typ a odpovídající property.
	 */
	public function testFindByMail()
	{
		$auth = $this->authFacade->findByMail(self::MAIL);

		Assert::type('\App\Model\Entity\Auth', $auth);
		Assert::same(self::MAIL, $auth->key);
	}

	public function testFindByRecoveryToken()
	{
		// Expired token
		$this->user->setRecovery(self::EXPIRED_TOKEN, 'now - 1 day');
		$this->userDao->save($this->user);

		Assert::null($this->authFacade->findByRecoveryToken(self::EXPIRED_TOKEN));

		/* @var $user Entity\User */
		$user = $this->userDao->find($this->user->id);
		Assert::null($user->recoveryExpiration);
		Assert::null($user->recoveryToken);

		// Valid token
		$this->user->setRecovery(self::VALID_TOKEN, 'now + 1 day');
		$this->userDao->save($this->user);

		$auth = $this->authFacade->findByRecoveryToken(self::VALID_TOKEN);
		Assert::type('\App\Model\Entity\Auth', $auth);
		Assert::same(self::VALID_TOKEN, $auth->user->recoveryToken);
	}

	public function testRecoveryPassword()
	{
		$this->user->setRecovery(self::VALID_TOKEN, 'now + 1 day');
		$this->userDao->save($this->user);

		$auth = $this->authFacade->findByRecoveryToken(self::VALID_TOKEN);
		$auth = $this->authFacade->recoveryPassword($auth, self::PASSWORD);

		Assert::type('\App\Model\Entity\Auth', $auth);
		Assert::true(Nette\Security\Passwords::verify(self::PASSWORD, $auth->hash));
	}

	public function testUpdateAccessToken()
	{
		$auth = $this->authFacade->findByKey(Entity\Auth::SOURCE_APP, self::MAIL);
		$auth = $this->authFacade->updateAccessToken($auth, self::ACCESS_TOKEN);

		Assert::type('\App\Model\Entity\Auth', $auth);
		Assert::same(self::ACCESS_TOKEN, $auth->token);
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

$test = new AuthFacadeTest($container);
$test->run();
