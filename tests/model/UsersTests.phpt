<?php

namespace Test\Model;

use Nette,
	Tester,
	Tester\Assert;

$container = require __DIR__ . '/../bootstrap.php';

/**
 * TEST: Model Users Testing
 *
 * @testCase
 * @phpVersion 5.4
 */

class UsersTest extends Tester\TestCase
{

	public function setUp()
	{
		# Příprava
	}

	public function tearDown()
	{
		# Úklid
	}

	public function testInit()
	{
		Assert::same('a', 'a');
	}

}

$test = new UsersTest();
$test->run();
