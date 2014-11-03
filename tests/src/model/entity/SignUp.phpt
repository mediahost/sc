<?php

namespace Test\Model\Entity;

use App\Model\Entity\Role;
use App\Model\Entity\SignUp;
use DateTime;
use Nette\Security\Passwords;
use Tester\Assert;
use Tester\TestCase;

$container = require __DIR__ . '/../../bootstrap.php';

/**
 * TEST: Sign up entity
 *
 * @testCase
 * @phpVersion 5.4
 */
class SignUpTest extends TestCase
{

	const MAIL = 'anakin@skywalker.com';
	const NAME = 'Anakin Skywalker';
	const HASH = 'I4M4H4SHbitch!';
	const ROLE = 'DarkJedi';
	const PASSWORD = 'iAmDarthWaderL0053R5';
	const FACEBOOK_ID = 'FacebookIfThisIsIntCanBeLOngerThan32bit';
	const FACEBOOK_ACCESS_TOKEN = 'facebookAccessTokenCanBeToooooooooLongOrLonger';
	const TWITTER_ID = 'TwitterIfThisIsIntCanBeLOngerThan32bit';
	const TWITTER_ACCESS_TOKEN = 'twitterAccessTokenCanBeLongAsFacebookAccessToken';
	const VERIFICATION_TOKEN = 'verificationToken';

	public function testSetAndGet()
	{
		$signUp = new SignUp();

		$signUp->mail = self::MAIL;
		Assert::same(self::MAIL, $signUp->mail);

		$signUp->name = self::NAME;
		Assert::same(self::NAME, $signUp->name);

		$signUp->hash = self::HASH;
		Assert::same(self::HASH, $signUp->hash);

		$signUp->role = new Role();
		Assert::type(Role::getClassName(), $signUp->role);

		$signUp->facebookId = self::FACEBOOK_ID;
		Assert::same(self::FACEBOOK_ID, $signUp->facebookId);

		$signUp->facebookAccessToken = self::FACEBOOK_ACCESS_TOKEN;
		Assert::same(self::FACEBOOK_ACCESS_TOKEN, $signUp->facebookAccessToken);

		$signUp->twitterId = self::TWITTER_ID;
		Assert::same(self::TWITTER_ID, $signUp->twitterId);

		$signUp->twitterAccessToken = self::TWITTER_ACCESS_TOKEN;
		Assert::same(self::TWITTER_ACCESS_TOKEN, $signUp->twitterAccessToken);

		$signUp->verificationToken = self::VERIFICATION_TOKEN;
		Assert::same(self::VERIFICATION_TOKEN, $signUp->verificationToken);

		$tomorrow = new DateTime('now + 1 day');
		$signUp->verificationExpiration = $tomorrow;
		Assert::equal($tomorrow, $signUp->verificationExpiration);

		$signUp->setPassword(self::PASSWORD);
		Assert::true(Passwords::verify(self::PASSWORD, $signUp->hash));
	}

}

$test = new SignUpTest();
$test->run();
