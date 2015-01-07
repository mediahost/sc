<?php

namespace Test\Presenters\AppModule;

use Test\Presenters\BasePresenter as ParentBasePresenter;
use Tester\Environment;

/**
 * Parent presenter
 */
abstract class BasePresenter extends ParentBasePresenter
{

	public function setUp()
	{
		parent::setUp();
		$this->updateSchema();
		$this->installer->install();
		Environment::lock('session', LOCK_DIR);
	}

	public function tearDown()
	{
		$this->logout();
		$this->dropSchema();
	}

}
