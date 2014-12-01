<?php

namespace Test\Model\Facade;

use App\Model\Facade\RoleFacade;
use App\Model\Facade\UserFacade;
use Test\ParentTestCase;

/**
 * Parent of facades' tests
 */
abstract class BaseFacade extends ParentTestCase
{

	/** @var RoleFacade @inject */
	public $roleFacade;

	/** @var UserFacade @inject */
	public $userFacade;

	public function setUp()
	{
		$this->updateSchema();
	}

	public function tearDown()
	{
		$this->dropSchema();
	}

}
