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
		
		$entity = new Candidate;
		$entity->name = $name;
		$entity->birthday = $birthday;

		Assert::null($entity->id);
		Assert::same($name, $entity->name);
		Assert::same($name, (string) $entity);
		Assert::same($birthday, $entity->birthday);

		Assert::exception(function() use ($entity) {
			$entity->id = 123;
		}, 'Kdyby\Doctrine\MemberAccessException');
	}

}

$test = new CandidateTest();
$test->run();
