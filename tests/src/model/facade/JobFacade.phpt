<?php

namespace Test\Model\Facade;

use Nette\DI\Container;
use Tester\Assert;

$container = require __DIR__ . '/../../bootstrap.php';

/**
 * TEST: JobFacade
 *
 * @testCase
 * @phpVersion 5.4
 */
class JobFacadeTest extends BaseFacade
{

	public function __construct(Container $container)
	{
		parent::__construct($container);
	}

	public function testFindCvs()
	{
		// TODO: DO IT!!!
		Assert::same(TRUE, TRUE);
	}

}

$test = new JobFacadeTest($container);
$test->run();
