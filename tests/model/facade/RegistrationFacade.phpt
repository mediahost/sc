<?php

namespace Test\Model\Entity;

use Nette,
	Tester,
	Tester\Assert;

$container = require __DIR__ . '../../../bootstrap.php';

/**
 * TEST: UserFacade test
 *
 * @testCase
 * @phpVersion 5.4
 */

class RegistrationFacadeTest extends Tester\TestCase
{
	/** @var \App\Model\Facade\RegistrationFacade*/
	private $registrationFacade;
	
	
	public function __construct(\App\Model\Facade\RegistrationFacade $registrationFacade)
	{
		$this->registrationFacade = $registrationFacade;
	}

	public function setUp()
	{
		# Příprava
	}

	public function tearDown()
	{
		# Úklid
	}
	
	public function registerTemporarilyTest()
	{
		
		
		$this->registrationFacade->registerTemporarily($registration);
	}

}

$test = new RegistrationFacadeTest();
$test->run();
