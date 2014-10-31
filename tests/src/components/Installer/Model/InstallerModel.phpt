<?php

namespace Test\Components\Installer\Model;

use App\Components\Installer\Model\InstallerModel;
use App\Model\Facade\RoleFacade;
use App\Model\Facade\UserFacade;
use Nette\DI\Container;
use Nette\Security\IAuthorizator;
use Test\ParentTestCase;
use Tester\Assert;
use Tester\Environment;
use Tester\FileMock;

$container = require __DIR__ . '/../../../bootstrap.php';

/**
 * TEST: Installer Model Testing
 *
 * @testCase
 * @phpVersion 5.4
 */
class InstallerModelTest extends ParentTestCase
{
	// <editor-fold defaultstate="collapsed" desc="injects">

	/** @var RoleFacade @inject */
	public $roleFacade;

	/** @var UserFacade @inject */
	public $userFacade;

	/** @var IAuthorizator @inject */
	public $permissions;

	/** @var InstallerModel @inject */
	public $installerModel;

	// </editor-fold>

	public function __construct(Container $container)
	{
		parent::__construct($container);
		Environment::lock('db', LOCK_DIR);
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

		// test without existing file
		Assert::true($installer->installAdminer($dir));
		unlink($adminerFile);
		rmdir($adminerPath); 

		// testing allow writing (chmod 777) in mock file
		$file = FileMock::create('', 'sql');
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

		// TODO: update schema nahradit zprovozněným testem installDoctrine()
		$this->updateSchema();
//		Assert::true($installer->installDoctrine());

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

		$this->dropSchema();
	}

	// </editor-fold>
}

$test = new InstallerModelTest($container);
$test->run();
