<?php

namespace Test\Model\Entity;

use Nette,
	Tester,
	Tester\Assert,
	Nette\Security\Passwords;

$container = require __DIR__ . '/../../bootstrap.php';

/**
 * TEST: Model Users Testing
 *
 * @skip
 * @testCase
 * @phpVersion 5.4
 */

class RegistrationTest extends Tester\TestCase
{
	/** @var \App\Model\Entity\Registration */
	private $registration;

	public function setUp()
	{
		# Příprava
		$this->registration = new \App\Model\Entity\Registration();
		$this->registration->password = 'mySecretPassword_007';
	}

	public function tearDown()
	{
		# Úklid
		unset($this->registration);
	}

	public function testSetPassword()
	{
		Assert::true(Passwords::verify('mySecretPassword_007', $this->registration->hash));
	}
}

$test = new RegistrationTest();
$test->run();
