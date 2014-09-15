<?php

namespace Test\Model\Authenticator;

use Nette,
	Tester,
	Tester\Assert;

use Doctrine\ORM\Tools\SchemaTool,
	App\Model\Facade,
	Nette\Security\IAuthenticator,
	App\Model\Entity;

$container = require __DIR__ . '/../../bootstrap.php';

/**
 * TEST: Authenticator
 *
 * @testCase
 * @phpVersion 5.4
 */
class AuthenticatorTest extends Tester\TestCase
{
	const U_MAIL = 'mulder@fbi.gov';
	const U_PASSWORD = 'IveNeverF**kScully';
	const R_NAME = 'agent';

	/** @var Nette\DI\Container */
	private $container;

	/** @var \Kdyby\Doctrine\EntityManager @inject */
	public $em;

	/** @var SchemaTool */
	public $schemaTool;
	
	/** @var Facade\UserFacade @inject */
	public $userFacade;
	
	/** @var Facade\RoleFacade @inject */
	public $roleFacade;

	/** @var \Kdyby\Doctrine\EntityDao */
	public $userDao;
	
	/** @var \Nette\Security\IAuthenticator @inject */
	public $authenticator;

	function __construct(Nette\DI\Container $container)
	{
		$this->container = $container;
		$this->container->callInjects($this);
		$this->schemaTool = new SchemaTool($this->em);
		$this->userDao = $this->em->getDao(Entity\User::getClassName());
		\Tester\Environment::lock('db', LOCK_DIR);
	}
	
	public function setUp()
	{
		$this->schemaTool->updateSchema($this->getClasses());
	}
	
	public function tearDown()
	{
		$this->schemaTool->dropSchema($this->getClasses());
	}
	
	public function testAuthenticate()
	{
		$role = $this->roleFacade->create(self::R_NAME);
		$user = $this->userFacade->create(self::U_MAIL, self::U_PASSWORD, $role);
		
		Assert::exception(function() {
					$this->authenticator->authenticate(['unknown@email.com', self::U_PASSWORD]);
				},
				'\Nette\Security\AuthenticationException',
				NULL,
				IAuthenticator::IDENTITY_NOT_FOUND);
				
		Assert::exception(function() {
					$this->authenticator->authenticate([self::U_MAIL, 'incorrectPassword']);
				},
				'\Nette\Security\AuthenticationException',
				NULL,
				IAuthenticator::INVALID_CREDENTIAL);
				
		$this->userFacade->setRecovery($user);
				
		$identity = $this->authenticator->authenticate([self::U_MAIL, self::U_PASSWORD]);
		Assert::type('\Nette\Security\Identity', $identity);
		Assert::same($user->id, $identity->id);
		Assert::type('array', $identity->roles);
		Assert::type('array', $identity->data);
		
		/* @var $user Entity\User */
		$user = $this->userDao->find($identity->id);
		Assert::null($user->recoveryExpiration);
		Assert::null($user->recoveryToken);
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

$test = new AuthenticatorTest($container);
$test->run();
