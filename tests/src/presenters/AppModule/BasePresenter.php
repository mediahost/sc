<?php

namespace Test\Presenters\AppModule;

use Test\Presenters\BasePresenter as ParentBasePresenter;
use Tester\Environment;

/**
 * Parent presenter
 */
abstract class BasePresenter extends ParentBasePresenter
{

	protected function setUp()
	{
		parent::setUp();
		$this->updateSchema();
		$this->installer->install();
		Environment::lock('session', LOCK_DIR);
	}

	protected function tearDown()
	{
		$this->logout();
		$this->dropSchema();
	}

}
