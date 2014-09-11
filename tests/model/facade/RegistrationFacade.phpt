<?php

namespace Test\Model\Entity;

use Nette,
	Tester,
	Tester\Assert;

$container = require __DIR__ . '/../../bootstrap.php';

/**
 * TEST: RegistrationFacade
 * 
 * @skip
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

	/** @var \Kdyby\Doctrine\EntityDao */
	public $registrationDao;

	
	function __construct(Nette\DI\Container $container)
	{
		$this->container = $container;
		$this->container->callInjects($this);
		
		$this->registrationDao = $this->em->getDao(\App\Model\Entity\Registration::getClassName());
		
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

	public function testFindByVerificationToken()
	{
		$qb = $this->em->createQueryBuilder();
		$qb->delete('\App\Model\Entity\Registration')->getQuery()->execute();
		
		$this->registrationFacade->registerTemporarily($this->registration);
		$this->registrationFacade->registerTemporarily($this->registration);

		
		Assert::count(3, $this->registrationDao->findAll());
		
//		$qb = $this->em->createQueryBuilder();
//		$qb->delete('\App\Model\Entity\Registration')->getQuery()->execute();
		
		// Expired token
//		$registration = new \App\Model\Entity\Registration();
//		$registration->se
		
//		$this->registrationFacade->registerTemporarily($registration);
//		
//		$this->user->setRecovery(self::EXPIRED_TOKEN, 'now - 1 day');
//		$this->userDao->save($this->user);
//
//		Assert::null($this->authFacade->findByRecoveryToken(self::EXPIRED_TOKEN));
//
//		/* @var $user Entity\User */
//		$user = $this->userDao->find($this->user->id);
//		Assert::null($user->recoveryExpiration);
//		Assert::null($user->recoveryToken);
//
//		// Valid token
//		$this->user->setRecovery(self::VALID_TOKEN, 'now + 1 day');
//		$this->userDao->save($this->user);
//
//		$auth = $this->authFacade->findByRecoveryToken(self::VALID_TOKEN);
//		Assert::type('\App\Model\Entity\Auth', $auth);
//		Assert::same(self::VALID_TOKEN, $auth->user->recoveryToken);
	}

//	public function testRegisterTemporarily()
//	{
//		$this->registrationFacade->registerTemporarily($this->registration);
//		$all = $this->em->getDao(\App\Model\Entity\Registration::getClassName())
//				->findAll();
//		
//		Assert::type('array', $all);	
//		
//		Assert::type('\App\Model\Entity\User', $this->registrationFacade->verify($this->registration->verificationToken));
//		
//		$auth = $this->authFacade->findByKey(\App\Model\Entity\Auth::SOURCE_APP, $this->registration->key);
//		$user = $auth->user;
//		
//		Assert::same('John Doe', $user->name);
//		Assert::same('john.doe@domain.com', $user->mail);
//		Assert::true(Nette\Security\Passwords::verify('mySecretPassword_007', $auth->hash));
//	
//	}	
}

$test = new RegistrationFacadeTest($container);
$test->run();
