<?php

namespace Test\Model\Entity;

use Nette,
	Tester,
	Tester\Assert;

$container = require __DIR__ . '/../../bootstrap.php';

/**
 * TEST: Model Users Testing
 *
 * @skip
 * @testCase
 * @phpVersion 5.4
 */

class UserTest extends Tester\TestCase
{
	/** @var \App\Model\Entity\User */
	private $user;

	public function setUp()
	{
		# Příprava
		$this->user = new \App\Model\Entity\User();
		$this->user->setMail('john.doe@domain.com');
	}

	public function tearDown()
	{
		# Úklid
		unset($this->user);
	}
	
//	public function testSetters()
//	{
//		
//	}

	public function testToString()
	{
		Assert::same('john.doe@domain.com', (string) $this->user);
	}
	
	/**
	 *
	 */
	public function testSetRecovery()
	{
		$token = Nette\Utils\Strings::random(32);
		$expiration = new \DateTime('now + 3 hours');
		
		$this->user->setRecovery($token, $expiration);
		
		Assert::same($token, $this->user->recoveryToken);
		Assert::equal((new \DateTime)->add(\DateInterval::createFromDateString('3 hours')), $this->user->recoveryExpiration);
	}
	
	/**
	 * 
	 */
	public function testUnsetRecovery()
	{
		$token = Nette\Utils\Strings::random(32);
		$expiration = new \DateTime();
		$this->user->setRecovery($token, $expiration);
		$this->user->unsetRecovery();
		
		Assert::null($this->user->recoveryToken);
		Assert::null($this->user->recoveryExpiration);
	}

}

$test = new UserTest();
$test->run();
