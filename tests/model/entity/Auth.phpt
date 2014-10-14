<?php

namespace Test\Model\Entity;

use Nette,
	Tester,
	Tester\Assert;

use App\Model\Entity,
	Nette\Security\Passwords;

$container = require __DIR__ . '/../../bootstrap.php';

/**
 * TEST: Auth entity
 *
 * @testCase
 * @phpVersion 5.4
 */
class AuthTest extends Tester\TestCase
{

	const A_KEY = 'neo@matrix.com';
	const A_SOURCE = 'matrix';
	const A_TOKEN = 't0k3N';
	const A_HASH = 'SomethingLikeHash';
	const A_PASSWORD = 'myS3cr3tPass';

	public function testSetAndGet()
	{
		$auth = new Entity\Auth();

		$auth->user = new Entity\User();
		Assert::type(Entity\User::getClassName(), $auth->user);

		$auth->key = self::A_KEY;
		Assert::same(self::A_KEY, $auth->key);

		$auth->source = self::A_SOURCE;
		Assert::same(self::A_SOURCE, $auth->source);

		$auth->token = self::A_TOKEN;
		Assert::same(self::A_TOKEN, $auth->token);

		$auth->hash = self::A_HASH;
		Assert::same(self::A_HASH, $auth->hash);

		$auth->password = self::A_PASSWORD;
		Assert::true(Passwords::verify(self::A_PASSWORD, $auth->hash));
	}

	public function testVerifyPassword()
	{
		$auth = new Entity\Auth();
		$auth->password = self::A_PASSWORD;
		Assert::true($auth->verifyPassword(self::A_PASSWORD));
	}

}

$test = new AuthTest();
$test->run();
