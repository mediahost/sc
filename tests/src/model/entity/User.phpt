<?php

namespace Test\Model\Entity;

use App\Model\Entity\Facebook;
use App\Model\Entity\PageConfigSettings;
use App\Model\Entity\PageDesignSettings;
use App\Model\Entity\Role;
use App\Model\Entity\Twitter;
use App\Model\Entity\User;
use DateTime;
use Kdyby\Doctrine\EntityDao;
use Nette\DI\Container;
use Nette\Security\Passwords;
use Nette\Utils\Strings;
use Test\ParentTestCase;
use Tester\Assert;

$container = require __DIR__ . '/../../bootstrap.php';

/**
 * TEST: User entity
 *
 * @testCase
 * @phpVersion 5.4
 */
class UserTest extends ParentTestCase
{

	const MAIL = 'jack@sg1.sg.gov';
	const HASH = 'SomethingLikeHash';
	const PASSWORD = 'ThorIsMyFri3nd';
	const RECOVERY_TOKEN = 'recov3Rytoken';

	/** @var User */
	private $user;

	/** @var EntityDao */
	private $roleDao;

	/** @var EntityDao */
	private $userDao;

	public function __construct(Container $container)
	{
		parent::__construct($container);

		$this->roleDao = $this->em->getDao(Role::getClassName());
		$this->userDao = $this->em->getDao(User::getClassName());
	}

	public function setUp()
	{
		$this->user = new User();
	}

	public function tearDown()
	{
		unset($this->user);
	}

	public function testSetAndGet()
	{
		Assert::type('array', $this->user->roles);

		$this->user->mail = self::MAIL;
		Assert::same(self::MAIL, $this->user->mail);

		$this->user->hash = self::HASH;
		Assert::same(self::HASH, $this->user->hash);
		$this->user->clearHash();
		Assert::null($this->user->hash);
		
		$this->user->password = self::PASSWORD;
		Assert::true(Passwords::verify(self::PASSWORD, $this->user->hash));
		
		$this->user->pageConfigSettings = new PageConfigSettings;
		Assert::type(PageConfigSettings::getClassName(), $this->user->pageConfigSettings);
		$pageConfigSettings = $this->user->pageConfigSettings;
		Assert::type(User::getClassName(), $pageConfigSettings->user);
		
		$this->user->pageDesignSettings = new PageDesignSettings;
		Assert::type(PageDesignSettings::getClassName(), $this->user->pageDesignSettings);
		$pageDesignSettings = $this->user->pageDesignSettings;
		Assert::type(User::getClassName(), $pageDesignSettings->user);

		$this->user->facebook = new Facebook();
		Assert::type(Facebook::getClassName(), $this->user->facebook);
		$facebook = $this->user->facebook;
		Assert::type(User::getClassName(), $facebook->user);
		$this->user->clearFacebook();
		Assert::null($this->user->facebook);
		
		$this->user->twitter = new Twitter();
		Assert::type(Twitter::getClassName(), $this->user->twitter);
		$twitter = $this->user->twitter;
		Assert::type(User::getClassName(), $twitter->user);
		$this->user->clearTwitter();
		Assert::null($this->user->twitter);

		$this->user->recoveryToken = self::RECOVERY_TOKEN;
		Assert::same(self::RECOVERY_TOKEN, $this->user->recoveryToken);

		$tomorrow = new DateTime('now + 1 day');
		$this->user->recoveryExpiration = $tomorrow;
		Assert::equal($tomorrow, $this->user->recoveryExpiration);
		
		$requiredRole = new Role('required');
		$this->user->requiredRole = $requiredRole;
		Assert::type(Role::getClassName(), $this->user->requiredRole);
		Assert::same($requiredRole->name, $this->user->requiredRole->name);
	}

	public function testToArray()
	{
		$this->updateSchema();

		$roleA = $this->roleDao->save(new Role('Role A'));
		$roleB = $this->roleDao->save(new Role('Role B'));

		$this->user->mail = self::MAIL;
		$this->user->addRole([$roleB, $roleA]);

		$user = $this->userDao->save($this->user);
		$array = $user->toArray();

		Assert::same($user->id, $array['id']);
		Assert::same(self::MAIL, $array['mail']);
		Assert::type('array', $array['role']);
		Assert::type(Role::getClassName(), $array['role'][0]);
		Assert::same('Role B', $array['role'][0]->name);
		Assert::type(Role::getClassName(), $array['role'][1]);
		Assert::same('Role A', $array['role'][1]->name);

		$this->dropSchema();
	}

	public function testToString()
	{
		$this->user->mail = self::MAIL;
		Assert::same(self::MAIL, (string) $this->user);
	}

	public function testVerifyPassword()
	{
		$this->user->password = self::PASSWORD;
		Assert::true($this->user->verifyPassword(self::PASSWORD));
	}

	public function testAddRole()
	{
		$roleA = (new Role())->setName('Role A');
		$roleB = (new Role())->setName('Role B');
		$roleC = (new Role())->setName('Role C');

		Assert::count(0, $this->user->roles); // No roles

		$this->user->addRole($roleA); // Add first role
		Assert::count(1, $this->user->roles);

		$this->user->addRole($roleA); // Add the same role
		Assert::count(1, $this->user->roles);

		$this->user->addRole($roleB); // Add another role
		Assert::count(2, $this->user->roles);

		$this->user->addRole($roleC, TRUE); // Clear roles and add new one
		Assert::count(1, $this->user->roles);
		Assert::same('Role C', $this->user->roles[0]->name);

		$this->user->addRole([$roleA, $roleC]); // Add array with duplicit roles
		Assert::count(2, $this->user->roles);

		$this->user->clearRoles();
		Assert::count(0, $this->user->roles);
	}

	public function testGetRoles()
	{
		$this->updateSchema();

		$roleA = $this->roleDao->save(new Role(Role::GUEST));
		$roleB = $this->roleDao->save(new Role(Role::SIGNED));
		$roleC = $this->roleDao->save(new Role(Role::CANDIDATE));
		$roleD = $this->roleDao->save(new Role(Role::COMPANY));
		$roleE = $this->roleDao->save(new Role(Role::ADMIN));
		$roleF = $this->roleDao->save(new Role(Role::SUPERADMIN));

		$this->user->addRole([$roleB, $roleC, $roleB, $roleA, $roleA, $roleC]);
		Assert::same([$roleB->id, $roleC->id, $roleA->id], $this->user->getRolesKeys());
		
		$this->user->addRole([$roleB, $roleA, $roleC], TRUE);
		Assert::count(3, $this->user->getRolesPairs());
		Assert::same([2 => Role::SIGNED, 1 => Role::GUEST, 3 => Role::CANDIDATE], $this->user->getRolesPairs());
		
		$this->user->addRole([$roleD, $roleE, $roleF], TRUE);
		Assert::type(Role::getClassName(), $this->user->maxRole);
		Assert::same($roleF, $this->user->maxRole);

		$this->dropSchema();
	}

	public function testRemoveRole()
	{
		$roleA = new Role('Role A');
		$roleB = new Role('Role B');

		$this->user->addRole($roleA);
		Assert::count(1, $this->user->roles);
		$this->user->addRole($roleB);
		Assert::count(2, $this->user->roles);
		$this->user->removeRole($roleA);
		Assert::count(1, $this->user->roles);
		$this->user->removeRole($roleB);
		Assert::count(0, $this->user->roles);
	}

	public function testSetRecovery()
	{
		$expiration = new DateTime('now + 3 hours');

		$this->user->setRecovery(self::RECOVERY_TOKEN, $expiration);

		Assert::same(self::RECOVERY_TOKEN, $this->user->recoveryToken);
		Assert::equal($expiration, $this->user->recoveryExpiration);
	}

	public function testRemoveRecovery()
	{
		$token = Strings::random(32);
		$expiration = new DateTime();
		$this->user->setRecovery($token, $expiration);
		$this->user->removeRecovery();

		Assert::null($this->user->recoveryToken);
		Assert::null($this->user->recoveryExpiration);
	}
	
	public function testSocialConnection()
	{
		Assert::null($this->user->socialName);
		Assert::false($this->user->hasSocialConnection(User::SOCIAL_CONNECTION_APP));
		Assert::false($this->user->hasSocialConnection(User::SOCIAL_CONNECTION_TWITTER));
		Assert::false($this->user->hasSocialConnection(User::SOCIAL_CONNECTION_FACEBOOK));
		Assert::same(0, $this->user->connectionCount);
		
		$tw = new Twitter('12345');
		$tw->name = 'TW social name';
		
		$this->user->twitter = $tw;
		Assert::same($tw->name, $this->user->socialName);
		Assert::null($this->user->socialBirthday);
		Assert::true($this->user->hasSocialConnection(User::SOCIAL_CONNECTION_TWITTER));
		Assert::same(1, $this->user->connectionCount);
		
		$fb = new Facebook('12345');
		$fb->name = 'FB social name';
		$fb->birthday = '30.2.1920';
		
		$this->user->facebook = $fb;
		Assert::same($fb->name, $this->user->socialName);
		Assert::same($fb->birthday, $this->user->socialBirthday);
		Assert::true($this->user->hasSocialConnection(User::SOCIAL_CONNECTION_FACEBOOK));
		Assert::same(2, $this->user->connectionCount);
		
		$this->user->setPassword(self::PASSWORD);
		Assert::true($this->user->hasSocialConnection(User::SOCIAL_CONNECTION_APP));
		Assert::same(3, $this->user->connectionCount);
		
		Assert::false($this->user->hasSocialConnection('unknown'));
	}

	protected function getClasses()
	{
		return [
			$this->em->getClassMetadata(User::getClassName()),
			$this->em->getClassMetadata(Role::getClassName()),
		];
	}

}

$test = new UserTest($container);
$test->run();
