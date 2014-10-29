<?php

namespace Test\Model\Facade;

use App\Model\Facade;
use Nette;
use Test\ParentTestCase;

/**
 * Parent of test facades
 */
abstract class BaseFacade extends ParentTestCase
{

	/** @var Facade\AuthFacade @inject */
	public $authFacade;

	/** @var Facade\RegistrationFacade @inject */
	public $registrationFacade;

	/** @var Facade\RoleFacade @inject */
	public $roleFacade;

	/** @var Facade\UserFacade @inject */
	public $userFacade;

	public function __construct(Nette\DI\Container $container)
	{
		parent::__construct($container);
		\Tester\Environment::lock('db', LOCK_DIR);
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
