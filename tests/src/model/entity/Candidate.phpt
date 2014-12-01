<?php

namespace Test\Model\Entity;

use App\Model\Entity\Candidate;
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
		$address = 'Silniční 123, Město nad Řekou';

		$entity = new Candidate();
		$entity->name = $name;
		$entity->address = $address;

		Assert::null($entity->id);
		Assert::same($name, $entity->name);
		Assert::same($name, (string) $entity);
		Assert::same($address, $entity->address);

		Assert::exception(function() use ($entity) {
			$entity->id = 123;
		}, 'Kdyby\Doctrine\MemberAccessException');
	}

}

$test = new CandidateTest();
$test->run();
