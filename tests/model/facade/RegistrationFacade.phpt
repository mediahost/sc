<?php

namespace Test\Model\Entity;

use Nette,
	Tester,
	Tester\Assert;

$container = require __DIR__ . '/../../bootstrap.php';

/**
 * TEST: RegistrationFacade test
 *
 * @testCase
 * @phpVersion 5.4
 */

class RegistrationFacadeTest extends Tester\TestCase
{
	/** @var \App\Model\Facade\RegistrationFacade @inject*/
	public $registrationFacade;

	/** @var \App\Model\Facade\UserFacade @inject*/
	public $userFacade;
	
	/** @var \App\Model\Facade\AuthFacade @inject*/
	public $authFacade;
	
	/** @var \App\Model\Facade\RoleFacade @inject*/
	public $roleFacade;

	/** @var \App\Model\Entity\Registration */
	private $registration;
	
	/** @var \Kdyby\Doctrine\EntityManager @inject */
	public $em;

	
	function __construct(Nette\DI\Container $container)
	{
		$this->container = $container;
		$this->container->callInjects($this);
		
		$this->registration = new \App\Model\Entity\Registration();
		$this->registration->setMail('john.doe@domain.com')
				->setName('John Doe')
				->setKey('john.doe@domain.com')
				->setSource(\App\Model\Entity\Auth::SOURCE_APP)
				->setHash(Nette\Security\Passwords::hash('mySecretPassword_007'));
	}

	public function setUp()
	{
		
	}

	public function tearDown()
	{
		# Úklid
	}
	
	public function testFindByKey() // ToDo: Přesunout do testů AuthFacade
	{
		$role = $this->roleFacade->findByName('candidate');
		$this->userFacade->create('joe.doe@gmail.com', 'heslo', $role);
		$auth = $this->authFacade->findByKey(\App\Model\Entity\Auth::SOURCE_APP, 'joe.doe@gmail.com');
		
		Assert::same('joe.doe@gmail.com', $auth->mail);
		Assert::same('joe.doe@gmail.com', $auth->key);
		
	}

	/**
	 * 
	 */
	public function testRegisterTemporarily()
	{
		$this->registrationFacade->registerTemporarily($this->registration);
		$all = $this->em->getDao(\App\Model\Entity\Registration::getClassName())
				->findAll();
		
		Assert::type('array', $all);
		Assert::count(1, $all);		
		
		Assert::type('\App\Model\Entity\User', $this->registrationFacade->verify($this->registration->verificationToken));
		
		$auth = $this->authFacade->findByKey(\App\Model\Entity\Auth::SOURCE_APP, $this->registration->key);
		$user = $auth->user;
		
		Assert::same('John Doe', $user->name);
		Assert::same('john.doe@domain.com', $user->mail);
		Assert::true(Nette\Security\Passwords::verify('mySecretPassword_007', $auth->hash));
	
	}	
}

$test = new RegistrationFacadeTest($container);
$test->run();
