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
	/** @var \App\Model\Facade\RegistrationFacade*/
	private $registrationFacade;

	/** @var App\Model\Entity\Registration */
	private $registration;
	
	/** @var Kdyby\Doctrine\EntityManager */
	private $em;


	public function __construct(\App\Model\Facade\RegistrationFacade $registrationFacade, \Kdyby\Doctrine\EntityManager $em)
	{
		$this->registrationFacade = $registrationFacade;
		$this->em = $em;
		
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
	
	public function testFindByKey()
	{
		
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
		
		$auth = $this->registrationFacade->findByKey(\App\Model\Entity\Auth::SOURCE_APP, $this->registration->key);
		$user = $auth->user;
		
		Assert::same('John Doe', $user->name);
		Assert::same('john.doe@domain.com', $user->mail);
		Assert::true(Nette\Security\Passwords::verify('mySecretPassword_007', $auth->hash));
	
	}	
}

$test = new RegistrationFacadeTest(
		$container->getByType('App\Model\Facade\RegistrationFacade'),
		$container->getByType('Kdyby\Doctrine\EntityManager'));
$test->run();
