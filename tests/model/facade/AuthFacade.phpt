<?php

namespace Test\Model\Facade;

use Nette,
	Tester,
	Tester\Assert;

use App\Model\Entity;

$container = require __DIR__ . '/../../bootstrap.php';

/**
 * TEST: Auth facade
 *
 * @testCase
 * @phpVersion 5.4
 */
class AuthFacadeTest extends BaseFacade
{

	const SOURCE = \App\Model\Entity\Auth::SOURCE_APP;
	const MAIL = 'tomas.jedno@seznam.cz';
	const PASSWORD = 'tomik1985';
	const EXPIRED_TOKEN = 'expiredToken';
	const VALID_TOKEN = 'validToken';
	const ACCESS_TOKEN = 'accessToken';

	/** @var \Kdyby\Doctrine\EntityDao */
	public $userDao;

	/** @var Entity\User */
	private $user;

	function __construct(Nette\DI\Container $container)
	{
		parent::__construct($container);
		$this->userDao = $this->em->getDao(\App\Model\Entity\User::getClassName());
	}

	public function setUp()
	{
		parent::setUp();
		$role = $this->roleFacade->create('boss');
		$this->user = $this->userFacade->create(self::MAIL, 'heslo', $role);
	}

	public function testFindByKey()
	{
		$auth = $this->authFacade->findByKey(self::SOURCE, self::MAIL);

		Assert::type(Entity\Auth::getClassName(), $auth);
		Assert::same(self::SOURCE, $auth->source);
		Assert::same(self::MAIL, $auth->key);
	}

	public function testFindByMail()
	{
		$auth = $this->authFacade->findByMail(self::MAIL);

		Assert::type(Entity\Auth::getClassName(), $auth);
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
		Assert::type(Entity\Auth::getClassName(), $auth);
		Assert::same(self::VALID_TOKEN, $auth->user->recoveryToken);
	}

	public function testFindByUser()
	{
		$auths = $this->authFacade->findByUser($this->user);
		Assert::type(Entity\Auth::getClassName(), $auths[0]);
		Assert::same($auths[0]->key, self::MAIL);
	}

	public function testIsUnique()
	{
		Assert::false($this->authFacade->isUnique(self::MAIL, self::SOURCE));
		Assert::true($this->authFacade->isUnique('earth@solarsystem.mw', 'space'));
	}

	public function testRecoveryPassword()
	{
		$this->user->setRecovery(self::VALID_TOKEN, 'now + 1 day');
		$this->userDao->save($this->user);

		$auth = $this->authFacade->findByRecoveryToken(self::VALID_TOKEN);
		$auth = $this->authFacade->recoveryPassword($auth, self::PASSWORD);

		Assert::type(Entity\Auth::getClassName(), $auth);
		Assert::true(Nette\Security\Passwords::verify(self::PASSWORD, $auth->hash));
	}

	public function testUpdateAccessToken()
	{
		$auth = $this->authFacade->findByKey(Entity\Auth::SOURCE_APP, self::MAIL);
		$auth = $this->authFacade->updateAccessToken($auth, self::ACCESS_TOKEN);

		Assert::type(Entity\Auth::getClassName(), $auth);
		Assert::same(self::ACCESS_TOKEN, $auth->token);
	}

}

$test = new AuthFacadeTest($container);
$test->run();
