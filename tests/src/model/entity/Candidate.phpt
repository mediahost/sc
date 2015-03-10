<?php

namespace Test\Model\Entity;

use App\Model\Entity\Candidate;
use Kdyby\Doctrine\MemberAccessException;
use Test\BaseTestCase;
use Tester\Assert;

$container = require __DIR__ . '/../../bootstrap.php';

/**
 * TEST: Candidate entity
 *
 * @testCase
 * @phpVersion 5.4
 */
class CandidateTest extends BaseTestCase
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
		Assert::same($birthday, $entity->birthday);

		Assert::same($name, (string) $entity);

		Assert::exception(function () use ($entity) {
			$entity->id = 123;
		}, MemberAccessException::class);
	}

}

$test = new CandidateTest($container);
$test->run();
