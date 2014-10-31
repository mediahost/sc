<?php

namespace Test\Model\Entity;

use App\Model\Entity\Facebook;
use Tester\Assert;
use Tester\TestCase;

$container = require __DIR__ . '/../../bootstrap.php';

/**
 * TEST: Facebook entity
 *
 * @testCase
 * @phpVersion 5.4
 */
class FacebookTest extends TestCase
{

	const ID = '123456789';
	const ACCESS_TOKEN = 'veryLongAndCompicatedToken';

	public function testSetAndGet()
	{
		$fb = new Facebook();

		$fb->id = self::ID;
		Assert::same(self::ID, $fb->id);
		
		$fb->accessToken = self::ACCESS_TOKEN;
		Assert::same(self::ACCESS_TOKEN, $fb->accessToken);
	}

}

$test = new FacebookTest();
$test->run();
