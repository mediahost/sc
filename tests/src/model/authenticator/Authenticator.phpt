<?php

namespace Test\Model\Authenticator;

use App\Model\Entity\User;
use App\Model\Facade\RoleFacade;
use App\Model\Facade\UserFacade;
use Kdyby\Doctrine\EntityDao;
use Nette\DI\Container;
use Nette\Security\IAuthenticator;
use Test\ParentTestCase;
use Tester\Assert;
use Tester\Environment;

$container = require __DIR__ . '/../../bootstrap.php';

/**
 * TEST: Authenticator
 *
 * @testCase
 * @phpVersion 5.4
 */
class AuthenticatorTest extends ParentTestCase
{

	const U_MAIL = 'mulder@fbi.gov';
	const U_PASSWORD = 'IveNeverF**kScully';
	const R_NAME = 'agent';

	/** @var UserFacade @inject */
	public $userFacade;

	/** @var RoleFacade @inject */
	public $roleFacade;

	/** @var EntityDao */
	public $userDao;

	/** @var IAuthenticator @inject */
	public $authenticator;

	public function __construct(Container $container)
	{
		parent::__construct($container);
		Environment::lock('db', LOCK_DIR);
		$this->userDao = $this->em->getDao(User::getClassName());
	}

	public function setUp()
	{
		$this->updateSchema();
	}

	public function tearDown()
	{
		$this->dropSchema();
	}

	public function testAuthenticate()
	{
		$role = $this->roleFacade->create(self::R_NAME);
		$user = $this->userFacade->create(self::U_MAIL, self::U_PASSWORD, $role);

		Assert::exception(function() {
			$this->authenticator->authenticate(['unknown@email.com', self::U_PASSWORD]);
		}, '\Nette\Security\AuthenticationException', NULL, IAuthenticator::IDENTITY_NOT_FOUND);

		Assert::exception(function() {
			$this->authenticator->authenticate([self::U_MAIL, 'incorrectPassword']);
		}, '\Nette\Security\AuthenticationException', NULL, IAuthenticator::INVALID_CREDENTIAL);

		$this->userFacade->setRecovery($user);

		$identity = $this->authenticator->authenticate([self::U_MAIL, self::U_PASSWORD]);
		Assert::type('\Nette\Security\Identity', $identity);
		Assert::same($user->id, $identity->id);
		Assert::type('array', $identity->roles);
		Assert::type('array', $identity->data);

		/* @var $user User */
		$userFinded = $this->userDao->find($identity->id);
		Assert::null($userFinded->recoveryExpiration);
		Assert::null($userFinded->recoveryToken);
	}

}

$test = new AuthenticatorTest($container);
$test->run();
