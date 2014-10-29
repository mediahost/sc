<?php

namespace Test\Components\Installer\Model;

use Nette,
	Tester,
	Tester\Assert;

$container = require __DIR__ . '/../../../bootstrap.php';

/**
 * TEST: Installer Model Testing
 *
 * @testCase
 * @phpVersion 5.4
 */
class InstallerModelTest extends Tester\TestCase
{

	/** @var Nette\DI\Container */
	private $container;

	// <editor-fold defaultstate="collapsed" desc="injects">

	/** @var \Doctrine\ORM\EntityManager @inject */
	public $em;

	/** @var \Doctrine\ORM\Tools\SchemaTool */
	public $schemaTool;

	/** @var \App\Model\Facade\RoleFacade @inject */
	public $roleFacade;

	/** @var \App\Model\Facade\UserFacade @inject */
	public $userFacade;

	/** @var \Nette\Security\IAuthorizator @inject */
	public $permissions;

	/** @var \App\Components\Installer\Model\InstallerModel @inject */
	public $installerModel;

	// </editor-fold>

	function __construct(Nette\DI\Container $container)
	{
		$this->container = $container;
		$this->container->callInjects($this);
		$this->schemaTool = new \Doctrine\ORM\Tools\SchemaTool($this->em);
		\Tester\Environment::lock('db', LOCK_DIR);
	}

	// <editor-fold defaultstate="expanded" desc="tests">

	public function testInstallerFiles()
	{
		$installer = $this->installerModel;
		$dir = $this->container->getParameters()['tempDir'];

		// test '/adminer/database.sql'
		$adminerPath = $dir . 'adminer/';
		$adminerFile = $adminerPath . 'database.sql';
		mkdir($adminerPath);
		file_put_contents($adminerFile, 'test');
		Assert::true($installer->installAdminer($dir));
		unlink($adminerFile);
		rmdir($adminerPath);

		// test without existing file
		Assert::true($installer->installAdminer($dir));

		// testing allow writing (chmod 777) in mock file
		$file = Tester\FileMock::create('', 'sql');
		Assert::true($installer->installAdminer(NULL, $file));
		file_put_contents($file, 'test');
		Assert::same('test', file_get_contents($file));
	}

	public function testInstallerDb()
	{
		$installer = $this->installerModel;

		Assert::exception(function() {
			$this->userFacade->find(1);
		}, 'Kdyby\Doctrine\DBALException');
		Assert::exception(function() {
			$this->roleFacade->find(1);
		}, 'Kdyby\Doctrine\DBALException');

		Assert::true($installer->installDoctrine());

		Assert::null($this->userFacade->find(1));
		Assert::count(0, $this->userFacade->findAll());
		Assert::null($this->roleFacade->find(1));
		Assert::count(0, $this->roleFacade->findAll());

		$roles1 = [];
		$return1 = $installer->installRoles($roles1);
		$dbRoles1 = $this->roleFacade->findPairs('name');
		Assert::true($return1);
		Assert::same($roles1, $dbRoles1);

		$roles2 = $this->permissions->getRoles();
		$return2 = $installer->installRoles($roles2);
		$dbRoles2 = $this->roleFacade->findPairs('name');
		Assert::true($return2);
		Assert::equal(array_values($roles2), array_values($dbRoles2));

		// ROLES ARE INSTALLED

		$wrongUsers1 = [ // wrong array format
			'username1', 'password', 'role'
		];
		$wrongUsers2 = [ // wrond inner array format
			'username1' => ['password' => 'role'],
		];
		$wrongUsers3 = [ // not inserted role
			'username1' => ['password'],
		];
		$wrongUsers4 = [ // non-existing role
			'username1' => ['password', 'non_existing_role'],
		];
		$rightUsers1 = [
			'username0' => ['password', 'guest'],
			'username1' => ['password', 'signed'],
			'username2' => ['password', 'candidate'],
			'username3' => ['password', 'company'],
			'username4' => ['password', 'admin'],
			'username5' => ['password', 'superadmin'],
		];

		Assert::true($installer->installUsers([]));
		Assert::same([], $this->userFacade->findPairs('mail'));

		Assert::exception(function() use ($installer, $wrongUsers1) {
			$installer->installUsers($wrongUsers1);
		}, 'Nette\InvalidArgumentException');

		Assert::exception(function() use ($installer, $wrongUsers2) {
			$installer->installUsers($wrongUsers2);
		}, 'Nette\InvalidArgumentException');

		Assert::exception(function() use ($installer, $wrongUsers3) {
			$installer->installUsers($wrongUsers3);
		}, 'Nette\InvalidArgumentException');

		Assert::exception(function() use ($installer, $wrongUsers4) {
			$installer->installUsers($wrongUsers4);
		}, 'Nette\InvalidArgumentException');

		Assert::true($installer->installUsers($rightUsers1));
		Assert::equal(array_keys($rightUsers1), array_values($this->userFacade->findPairs('mail')));

		$user0 = $this->userFacade->findByMail('username0');
		Assert::same('username0', $user0->mail);
		Assert::same([1 => 'guest'], $user0->rolesPairs);

		Assert::count(count($rightUsers1), $this->userFacade->findAll());

		$this->schemaTool->dropSchema($this->getClasses());
	}

	// </editor-fold>

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

$test = new InstallerModelTest($container);
$test->run();
