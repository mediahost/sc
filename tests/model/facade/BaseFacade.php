<?php

namespace Test\Model\Facade;

use Nette,
	Tester;

use Doctrine\ORM\Tools\SchemaTool,
	App\Model\Facade;

/**
 *
 */
abstract class BaseFacade extends Tester\TestCase
{

	/** @var Nette\DI\Container */
	protected $container;

	/** @var \Doctrine\ORM\EntityManager @inject */
	public $em;

	/** @var SchemaTool */
	protected $schemaTool;

	/** @var Facade\AuthFacade @inject */
	public $authFacade;

	/** @var Facade\RegistrationFacade @inject */
	public $registrationFacade;

	/** @var Facade\RoleFacade @inject */
	public $roleFacade;

	/** @var Facade\UserFacade @inject */
	public $userFacade;

	function __construct(Nette\DI\Container $container)
	{
		$this->container = $container;
		$this->container->callInjects($this);

		$this->schemaTool = new SchemaTool($this->em);

		\Tester\Environment::lock('db', LOCK_DIR);
	}

	public function setUp()
	{
		$this->schemaTool->updateSchema($this->getClasses());
	}

	public function tearDown()
	{
		$this->schemaTool->dropSchema($this->getClasses());
	}


	private function getClasses()
	{
		return [
			$this->em->getClassMetadata(\App\Model\Entity\User::getClassName()),
			$this->em->getClassMetadata(\App\Model\Entity\UserSettings::getClassName()),
			$this->em->getClassMetadata(\App\Model\Entity\Role::getClassName()),
			$this->em->getClassMetadata(\App\Model\Entity\Auth::getClassName()),
			$this->em->getClassMetadata(\App\Model\Entity\Registration::getClassName()),
		];
	}
}
