<?php

namespace Test\Model\Entity;

use Nette,
	Tester,
	Tester\Assert;

use App\Model\Entity,
	Nette\Security\Passwords;

$container = require __DIR__ . '/../../bootstrap.php';

/**
 * TEST: Registration entity
 *
 * @testCase
 * @phpVersion 5.4
 */

class RegistrationTest extends Tester\TestCase
{

	const R_MAIL = 'anakin@skywalker.com';
	const R_KEY = 'keyOfTheService';
	const R_SOURCE = 'tatooine';
	const R_TOKEN = 't0k3N';
	const R_HASH = 'thisShouldBeSomeHash';
	const R_PASSWORD = 'iAmDarthWader';
	const R_NAME = 'Anakin Skywalker';
	const R_VERIFICATION_TOKEN = 'verificationToken';

	public function testSetAndGet()
	{
		$reg = new Entity\Registration();

		$reg->mail = self::R_MAIL;
		Assert::same(self::R_MAIL, $reg->mail);
		
		$reg->key = self::R_KEY;
		Assert::same(self::R_KEY, $reg->key);
		
		$reg->source = self::R_SOURCE;
		Assert::same(self::R_SOURCE, $reg->source);
		
		$reg->token = self::R_TOKEN;
		Assert::same(self::R_TOKEN, $reg->token);
		
		$reg->hash = self::R_HASH;
		Assert::same(self::R_HASH, $reg->hash);
		
		$reg->name = self::R_NAME;
		Assert::same(self::R_NAME, $reg->name);
		
		$reg->verificationToken = self::R_VERIFICATION_TOKEN;
		Assert::same(self::R_VERIFICATION_TOKEN, $reg->verificationToken);
		
		$tomorrow = new \DateTime('now + 1 day');
		$reg->verificationExpiration = $tomorrow;
		Assert::equal($tomorrow, $reg->verificationExpiration);

		$reg->password = self::R_PASSWORD;
		Assert::true(Passwords::verify(self::R_PASSWORD, $reg->hash));
	}

}

$test = new RegistrationTest();
$test->run();
