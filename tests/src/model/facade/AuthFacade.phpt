<?php

namespace Test\Model\Facade;

use App\Model\Entity;
use App\Model\Entity\Auth;
use App\Model\Entity\User;
use Kdyby\Doctrine\EntityDao;
use Nette\DI\Container;
use Nette\Security\Passwords;
use Tester\Assert;

$container = require __DIR__ . '/../../bootstrap.php';

/**
 * TEST: Auth facade
 *
 * @testCase
 * @phpVersion 5.4
 */
class AuthFacadeTest extends BaseFacade
{

	const SOURCE = Auth::SOURCE_APP;
	const MAIL = 'tomas.jedno@seznam.cz';
	const PASSWORD = 'tomik1985';
	const EXPIRED_TOKEN = 'expiredToken';
	const VALID_TOKEN = 'validToken';
	const ACCESS_TOKEN = 'accessToken';

	/** @var EntityDao */
	public $userDao;

	/** @var User */
	private $user;

	public function __construct(Container $container)
	{
		parent::__construct($container);
		$this->userDao = $this->em->getDao(User::getClassName());
	}

	public function setUp()
	{
		parent::setUp();
		$role = $this->roleFacade->create('boss');
		$this->user = $this->userFacade->create(self::MAIL, 'heslo', $role);
	}

	// <editor-fold defaultstate="expanded" desc="tests">

	public function testFindByKey()
	{
		$auth = $this->authFacade->findByKey(self::SOURCE, self::MAIL);

		Assert::type(Auth::getClassName(), $auth);
		Assert::same(self::SOURCE, $auth->source);
		Assert::same(self::MAIL, $auth->key);
	}

	public function testFindByMail()
	{
		$auth = $this->authFacade->findByMail(self::MAIL);

		Assert::type(Auth::getClassName(), $auth);
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
		Assert::type(Auth::getClassName(), $auth);
		Assert::same(self::VALID_TOKEN, $auth->user->recoveryToken);
	}

	public function testFindByUser()
	{
		$auths = $this->authFacade->findByUser($this->user);
		Assert::type(Auth::getClassName(), $auths[0]);
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

		$authFinded = $this->authFacade->findByRecoveryToken(self::VALID_TOKEN);
		$auth = $this->authFacade->recoveryPassword($authFinded, self::PASSWORD);

		Assert::type(Auth::getClassName(), $auth);
		Assert::true(Passwords::verify(self::PASSWORD, $auth->hash));
	}

	public function testUpdateAccessToken()
	{
		$authFinded = $this->authFacade->findByKey(Auth::SOURCE_APP, self::MAIL);
		$auth = $this->authFacade->updateAccessToken($authFinded, self::ACCESS_TOKEN);

		Assert::type(Auth::getClassName(), $auth);
		Assert::same(self::ACCESS_TOKEN, $auth->token);
	}
	
	// </editor-fold>

}

$test = new AuthFacadeTest($container);
$test->run();
