<?php

namespace Test\Model\Entity;

use Nette,
	Tester,
	Tester\Assert;

$container = require __DIR__ . '/../../bootstrap.php';

/**
 * TEST: Auth entity
 *
 * @skip
 * @testCase
 * @phpVersion 5.4
 */

class AuthTest extends Tester\TestCase
{
	/** @var \App\Model\Entity\Auth */
	private $auth;

	public function setUp()
	{
//		$this->user = new \App\Model\Entity\User();
//		$this->user->setMail('john.doe@domain.com');
	}

	public function tearDown()
	{
		unset($this->user);
	}
	
//	public function testSetters()
//	{
//		
//	}
}

$test = new AuthTest();
$test->run();
