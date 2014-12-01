<?php

namespace Test\Model\Entity;

use App\Model\Entity\Company;
use Tester\Assert;
use Tester\TestCase;

$container = require __DIR__ . '/../../bootstrap.php';

/**
 * TEST: Company entity
 *
 * @testCase
 * @phpVersion 5.4
 */
class CompanyTest extends TestCase
{

	public function testSetAndGet()
	{
		$name = 'Testovací společnost';
		$comapnyId = '123-345-567 99';
		$address = 'Silniční 123, Město nad Řekou';

		$entity = new Company();

		$entity->name = $name;
		$entity->companyId = $comapnyId;
		$entity->address = $address;

		Assert::null($entity->id);
		Assert::same($name, $entity->name);
		Assert::same($name, (string) $entity);
		Assert::same($comapnyId, $entity->companyId);
		Assert::same($address, $entity->address);

		Assert::exception(function() use ($entity) {
			$entity->id = 123;
		}, 'Kdyby\Doctrine\MemberAccessException');
	}

}

$test = new CompanyTest();
$test->run();
