<?php

namespace Test\Model\Entity;

use App\Model\Entity\Twitter;
use Tester\Assert;
use Tester\TestCase;

$container = require __DIR__ . '/../../bootstrap.php';

/**
 * TEST: Twitter entity
 *
 * @testCase
 * @phpVersion 5.4
 */
class TwitterTest extends TestCase
{

	const ID = '123456789';
	const ACCESS_TOKEN = 'veryLongAndCompicatedToken';

	public function testSetAndGet()
	{
		$twitter = new Twitter();

		$twitter->id = self::ID;
		Assert::same(self::ID, $twitter->id);
		
		$twitter->accessToken = self::ACCESS_TOKEN;
		Assert::same(self::ACCESS_TOKEN, $twitter->accessToken);
	}

}

$test = new TwitterTest();
$test->run();
