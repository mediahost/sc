<?php

namespace Test\Presenters\AppModule;

use Test\Presenters\BasePresenter as ParentBasePresenter;

/**
 * Parent presenter for app presenters
 */
abstract class BasePresenter extends ParentBasePresenter
{

	protected function setUp()
	{
		parent::setUp();
		$this->updateSchema();
		$this->installer->install();
	}

	protected function tearDown()
	{
		parent::tearDown();
		$this->logout();
		$this->dropSchema();
	}

}
