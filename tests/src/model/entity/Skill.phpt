<?php

namespace Test\Model\Entity;

use App\Model\Entity\Skill;
use App\Model\Entity\SkillCategory;
use Test\ParentTestCase;
use Tester\Assert;

$container = require __DIR__ . '/../../bootstrap.php';

/**
 * TEST: Skill entity
 *
 * @testCase
 */
class SkillTest extends ParentTestCase
{
	
	public function testSetAndGet()
	{
		$name = 'foo';
		
		$entity = new Skill($name);
		Assert::same($name, $entity->name);
		
		$category = new SkillCategory;
		$entity->category = $category;
		Assert::same($category, $entity->category);
	}
	
}

$test = new SkillTest($container);
$test->run();