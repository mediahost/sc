<?php

namespace Test\Model\Entity;

use App\Model\Entity\OAuth;
use Tester\Assert;
use Tester\TestCase;

$container = require __DIR__ . '/../../bootstrap.php';

/**
 * TEST: OAuth entity
 *
 * @testCase
 * @phpVersion 5.4
 */
class OAuthTest extends TestCase
{

	public function testSetAndGet()
	{
		$entity = new OAuth;

		Assert::null($entity->id);

		Assert::exception(function() use ($entity) {
			$entity->id = '123456789';
		}, 'Kdyby\Doctrine\MemberAccessException');
	}

}

$test = new OAuthTest();
$test->run();
