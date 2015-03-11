<?php

namespace Test\Model\Facade;

use Nette\DI\Container;
use Tester\Assert;

$container = require __DIR__ . '/../../bootstrap.php';

/**
 * TEST: SkillFacade
 *
 * @testCase
 * @phpVersion 5.4
 */
class SkillFacadeTest extends BaseFacade
{

	public function __construct(Container $container)
	{
		parent::__construct($container);
	}

	public function testGetTopCategories()
	{
		// TODO: DO IT!!!
		Assert::same(TRUE, TRUE);
	}

}

$test = new SkillFacadeTest($container);
$test->run();
