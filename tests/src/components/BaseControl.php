<?php

namespace Test\Components;

use Test\ParentTestCase;

abstract class BaseControl extends ParentTestCase
{

	protected function setUp()
	{
		parent::setUp();
		$this->updateSchema();
	}

	protected function tearDown()
	{
		parent::tearDown();
		$this->dropSchema();
	}

}
