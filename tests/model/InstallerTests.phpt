<?php

namespace Test\Model;

use Nette,
	Tester,
	Tester\Assert;

$container = require __DIR__ . '/../bootstrap.php';

/**
 * TEST: Installer Testing
 *
 * @skip
 * @testCase
 * @phpVersion 5.4
 */
class InstallerTest extends Tester\TestCase
{

	/** @var Nette\DI\Container */
	private $container;

	/** @var \Doctrine\ORM\EntityManager @inject */
	public $em;

	/** @var \Doctrine\ORM\Tools\SchemaTool */
	public $schemaTool;

	/** @var \App\Model\Facade\RoleFacade @inject */
	public $roleFacade;

	/** @var \App\Model\Facade\UserFacade @inject */
	public $userFacade;

	/** @var Nette\Security\IAuthorizator @inject */
	public $permissions;

	function __construct(Nette\DI\Container $container)
	{
		$this->container = $container;
		$this->container->callInjects($this);
		$this->schemaTool = new \Doctrine\ORM\Tools\SchemaTool($this->em);
		\Tester\Environment::lock('db', $this->container->getParameters()['tempDir']);
	}

	private function getClasses()
	{
		return [
			$this->em->getClassMetadata('App\Model\Entity\User'),
			$this->em->getClassMetadata('App\Model\Entity\Role'),
			$this->em->getClassMetadata('App\Model\Entity\Auth'),
			$this->em->getClassMetadata('App\Model\Entity\Registration'),
		];
	}

	public function testInstallerFiles()
	{
		$installer = new \App\Model\Installer\Installer();
		$dir = $this->container->getParameters()['tempDir'];
		
		Assert::true($installer->installAdminer($dir));
		
		$file = Tester\FileMock::create('');
		$installer->installAdminer(NULL, $file);
		file_put_contents($file, 'test');
		Assert::same('test', file_get_contents($file));
	}

	public function testInstallerDb()
	{
		$installer = new \App\Model\Installer\Installer();

		Assert::exception(function() {
			$this->userFacade->find(1);
		}, 'Kdyby\Doctrine\DBALException');
		Assert::exception(function() {
			$this->roleFacade->find(1);
		}, 'Kdyby\Doctrine\DBALException');

		Assert::true($installer->installDoctrine($this->em));

		Assert::null($this->userFacade->find(1));
		Assert::count(0, $this->userFacade->findAll());
		Assert::null($this->roleFacade->find(1));
		Assert::count(0, $this->roleFacade->findAll());

		$roles1 = [];
		$return1 = $installer->installRoles($roles1, $this->roleFacade);
		$dbRoles1 = $this->roleFacade->findPairs('name');
		Assert::true($return1);
		Assert::same($roles1, $dbRoles1);

		$roles2 = $this->permissions->getRoles();
		$return2 = $installer->installRoles($roles2, $this->roleFacade);
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

		Assert::true($installer->installUsers([], $this->roleFacade, $this->userFacade));
		Assert::same([], $this->userFacade->findPairs('mail'));

		Assert::exception(function() use ($installer, $wrongUsers1) {
			$installer->installUsers($wrongUsers1, $this->roleFacade, $this->userFacade);
		}, 'Nette\InvalidArgumentException');

		Assert::exception(function() use ($installer, $wrongUsers2) {
			$installer->installUsers($wrongUsers2, $this->roleFacade, $this->userFacade);
		}, 'Nette\InvalidArgumentException');

		Assert::exception(function() use ($installer, $wrongUsers3) {
			$installer->installUsers($wrongUsers3, $this->roleFacade, $this->userFacade);
		}, 'Nette\InvalidArgumentException');

		Assert::exception(function() use ($installer, $wrongUsers4) {
			$installer->installUsers($wrongUsers4, $this->roleFacade, $this->userFacade);
		}, 'Nette\InvalidArgumentException');

		Assert::true($installer->installUsers($rightUsers1, $this->roleFacade, $this->userFacade));
		Assert::equal(array_keys($rightUsers1), array_values($this->userFacade->findPairs('mail')));

		$user0 = $this->userFacade->findByMail('username0');
		Assert::same('username0', $user0->mail);
		Assert::same([1 => 'guest'], $user0->rolesPairs);

		Assert::count(count($rightUsers1), $this->userFacade->findAll());
		
		$this->schemaTool->dropSchema($this->getClasses());
	}

}

$test = new InstallerTest($container);
$test->run();
