<?php

namespace Test\Components\Installer;

use Nette,
	Tester,
	Tester\Assert;

$container = require __DIR__ . '/../../bootstrap.php';

/**
 * TEST: Installer Testing
 *
 * @testCase
 * @phpVersion 5.4
 */
class InstallerTest extends Tester\TestCase
{

	/** @var Nette\DI\Container */
	private $container;

	/** @var string */
	private $installDir;

	// <editor-fold defaultstate="collapsed" desc="injects">

	/** @var \Doctrine\ORM\EntityManager @inject */
	public $em;

	/** @var \Doctrine\ORM\Tools\SchemaTool */
	public $schemaTool;

	/** @var \App\Model\Facade\UserFacade @inject */
	public $userFacade;

	/** @var \Nette\Security\IAuthorizator @inject */
	public $permissions;

	/** @var \App\Components\Installer @inject */
	public $installer;

	// </editor-fold>

	function __construct(Nette\DI\Container $container)
	{
		$this->container = $container;
		$this->container->callInjects($this);
		$this->schemaTool = new \Doctrine\ORM\Tools\SchemaTool($this->em);
		\Tester\Environment::lock('db', LOCK_DIR);
		$this->installDir = $this->container->getParameters()['tempDir'] . 'install/';
	}


	// <editor-fold defaultstate="expanded" desc="tests">

	public function testInstaller()
	{
		// install empty values
		$messages1 = $this->installer->setPathes(NULL, NULL, NULL, NULL)
				->setLock(FALSE)
				->setInstallAdminer(FALSE)
				->setInstallComposer(FALSE)
				->setInstallDoctrine(FALSE)
				->setInitUsers([])
				->install();
		Assert::count(2, $messages1);
		Assert::same(['DB_Roles', 'DB_Users'], array_keys($messages1));
		Assert::same([[0 => TRUE], [0 => TRUE]], array_values($messages1));
		
		// install all (empty) with lock
		$messages2 = $this->installer->setPathes(NULL, NULL, NULL, $this->installDir)
				->setLock(TRUE)
				->setInstallAdminer(TRUE)
				->setInstallComposer(TRUE)
				->setInstallDoctrine(TRUE)
				->setInitUsers([])
				->install();
		Assert::count(5, $messages2);
		Assert::same(['DB_Roles', 'DB_Users', 'Composer', 'Adminer', 'DB_Doctrine'], array_keys($messages2));
		Assert::true($messages2['DB_Roles'][0]);
		Assert::true($messages2['DB_Users'][0]);
		Assert::true($messages2['Composer'][0]);
		Assert::true($messages2['Adminer'][0]);
		Assert::true($messages2['DB_Doctrine'][0]);
		
		// install after lock
		$messages3 = $this->installer->setPathes(NULL, NULL, NULL, $this->installDir)
				->setLock(TRUE)
				->setInstallAdminer(TRUE)
				->setInstallComposer(TRUE)
				->setInstallDoctrine(TRUE)
				->setInitUsers([])
				->install();
		Assert::count(5, $messages3);
		Assert::same(['DB_Roles', 'DB_Users', 'Composer', 'Adminer', 'DB_Doctrine'], array_keys($messages3));
		Assert::false($messages3['DB_Roles'][0]);
		Assert::false($messages3['DB_Users'][0]);
		Assert::false($messages3['Composer'][0]);
		Assert::false($messages3['Adminer'][0]);
		Assert::false($messages3['DB_Doctrine'][0]);
		
		// clear lock
		\App\Helpers::delTree($this->installDir);
		
		$messages4 = $this->installer->setPathes(NULL, NULL, NULL, $this->installDir)
				->setLock(FALSE)
				->setInstallAdminer(FALSE)
				->setInstallComposer(FALSE)
				->setInstallDoctrine(FALSE)
				->setInitUsers([
					'user1' => ['password', 'guest'],
					'user2' => ['password', 'guest'],
					])
				->install();
		Assert::count(5, $messages4);
		Assert::same(['DB_Roles', 'DB_Users', 'Composer', 'Adminer', 'DB_Doctrine'], array_keys($messages4));
		Assert::true($messages4['DB_Roles'][0]);
		Assert::true($messages4['DB_Users'][0]);
		Assert::false($messages4['Composer'][0]);
		Assert::false($messages4['Adminer'][0]);
		Assert::false($messages4['DB_Doctrine'][0]);
		
		$user = $this->userFacade->findByMail('user');
		Assert::null($user);
		$user1 = $this->userFacade->findByMail('user1');
		Assert::same('user1', $user1->mail);
		Assert::same([1 => 'guest'], $user1->rolesPairs);
		Assert::count(2, $this->userFacade->findAll());
	}

	// </editor-fold>

	public function setUp()
	{
		$this->schemaTool->updateSchema($this->getClasses());
		mkdir($this->installDir);
	}

	public function tearDown()
	{
		$this->schemaTool->dropSchema($this->getClasses());
		\App\Helpers::delTree($this->installDir);
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

$test = new InstallerTest($container);
$test->run();
