<?php

namespace Test\Model\Facade;

use App\Model\Facade\RoleFacade;
use App\Model\Facade\UserFacade;
use Nette\DI\Container;
use Test\ParentTestCase;
use Tester\Environment;

/**
 * Parent of facades' tests
 */
abstract class BaseFacade extends ParentTestCase
{

	/** @var RoleFacade @inject */
	public $roleFacade;

	/** @var UserFacade @inject */
	public $userFacade;

	public function __construct(Container $container)
	{
		parent::__construct($container);
		Environment::lock('db', LOCK_DIR);
	}

	public function setUp()
	{
		$this->updateSchema();
	}

	public function tearDown()
	{
		$this->dropSchema();
	}

}
