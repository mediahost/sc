<?php

namespace Test\Model\Entity;

use App\Model\Entity\Candidate;
use App\Model\Entity\User;
use Tester\Assert;
use Tester\TestCase;

$container = require __DIR__ . '/../../bootstrap.php';

/**
 * TEST: Candidate entity
 *
 * @testCase
 * @phpVersion 5.4
 */
class CandidateTest extends TestCase
{

	public function testSetAndGet()
	{
		$name = 'Testovací kandidát';
		$birthday = '30.2.1920';

		$user = new User;
		$user->mail = 'user@mail.com';
		
		$entity = new Candidate;
		$entity->name = $name;
		$entity->birthday = $birthday;
		$entity->user = $user;

		Assert::null($entity->id);
		Assert::same($name, $entity->name);
		Assert::same($name, (string) $entity);
		Assert::same($birthday, $entity->birthday);
		Assert::same($user, $entity->user);
		Assert::same($user->mail, $entity->user->mail);

		Assert::exception(function() use ($entity) {
			$entity->id = 123;
		}, 'Kdyby\Doctrine\MemberAccessException');
	}

}

$test = new CandidateTest();
$test->run();
