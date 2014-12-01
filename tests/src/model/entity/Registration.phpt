<?php

namespace Test\Model\Entity;

use App\Model\Entity\Registration;
use App\Model\Entity\Role;
use DateTime;
use Nette\Security\Passwords;
use Tester\Assert;
use Tester\TestCase;

$container = require __DIR__ . '/../../bootstrap.php';

/**
 * TEST: Registration entity
 *
 * @testCase
 * @phpVersion 5.4
 */
class RegistrationTest extends TestCase
{

	public function testSetAndGet()
	{
		$mail = 'anakin@skywalker.com';
		$role = new Role('guest');
		$hash = 'I4M4H4SHbitch!';
		$password = 'iAmDarthWaderL0053R5';
		$facebookId = 'FacebookIfThisIsIntCanBeLOngerThan32bit';
		$facebookAccessToken = 'facebookAccessTokenCanBeToooooooooLongOrLonger';
		$twitterId = 'TwitterIfThisIsIntCanBeLOngerThan32bit';
		$twitterAccessToken = 'twitterAccessTokenCanBeLongAsFacebookAccessToken';
		$verificationToken = 'verificationToken';

		$entity = new Registration();
		Assert::null($entity->id);
		Assert::exception(function() use ($entity) {
			$entity->id = 123;
		}, 'Kdyby\Doctrine\MemberAccessException');

		$entity->mail = $mail;
		Assert::same($mail, $entity->mail);

		$entity->hash = $hash;
		Assert::same($hash, $entity->hash);

		$entity->setPassword($password);
		Assert::true(Passwords::verify($password, $entity->hash));

		$entity->role = $role;
		Assert::type(Role::getClassName(), $entity->role);
		Assert::same($role->name, $entity->role->name);

		$entity->facebookId = $facebookId;
		Assert::same($facebookId, $entity->facebookId);

		$entity->facebookAccessToken = $facebookAccessToken;
		Assert::same($facebookAccessToken, $entity->facebookAccessToken);

		$entity->twitterId = $twitterId;
		Assert::same($twitterId, $entity->twitterId);

		$entity->twitterAccessToken = $twitterAccessToken;
		Assert::same($twitterAccessToken, $entity->twitterAccessToken);

		$entity->verificationToken = $verificationToken;
		Assert::same($verificationToken, $entity->verificationToken);

		$tomorrow = new DateTime('now + 1 day');
		$entity->verificationExpiration = $tomorrow;
		Assert::equal($tomorrow, $entity->verificationExpiration);
	}

}

$test = new RegistrationTest();
$test->run();
