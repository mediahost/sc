<?php

namespace Test\Model\Facade;

use App\Extensions\Settings\Model\Storage\DefaultSettingsStorage;
use App\Model\Facade\CompanyFacade;
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

	/** @var CompanyFacade @inject */
	public $companyFacade;

	/** @var DefaultSettingsStorage @inject */
	public $defaultSettings;

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
