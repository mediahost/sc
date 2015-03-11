<?php

namespace Test\Model\Facade;

use Nette\DI\Container;
use Tester\Assert;

$container = require __DIR__ . '/../../bootstrap.php';

/**
 * TEST: CvFacade
 *
 * @testCase
 * @phpVersion 5.4
 */
class CvFacadeTest extends BaseFacade
{

	public function __construct(Container $container)
	{
		parent::__construct($container);
	}

	public function testFindJobs()
	{
		// TODO: DO IT!!!
		Assert::same(TRUE, TRUE);
	}

	public function testCreate()
	{
		// TODO: DO IT!!!
		Assert::same(TRUE, TRUE);
	}

	public function testSetAsDefault()
	{
		// TODO: DO IT!!!
		Assert::same(TRUE, TRUE);
	}

	public function testGetDefault()
	{
		// TODO: DO IT!!!
		Assert::same(TRUE, TRUE);
	}

}

$test = new CvFacadeTest($container);
$test->run();
