<?php

namespace Test\Model\Facade;

use App\Model\Entity\Facebook;
use App\Model\Entity\Role;
use App\Model\Entity\Twitter;
use App\Model\Entity\User;
use Kdyby\Doctrine\EntityDao;
use Nette\DI\Container;
use Nette\Security\Passwords;
use Tester\Assert;

$container = require __DIR__ . '/../../bootstrap.php';

/**
 * TEST: UserFacade
 *
 * @testCase
 * @phpVersion 5.4
 */
class UserFacadeTest extends BaseFacade
{

	const MAIL = 'tomas.jedno@seznam.cz';
	const PASSWORD = 'tomik1985';
	const EXPIRED_TOKEN = 'expiredToken';
	const VALID_TOKEN = 'validToken';
	const TWITTER_ID = 'tw123456789';
	const FACEBOOK_ID = 'fb123456789';
	
	/** @var EntityDao */
	private $facebookDao;

	/** @var EntityDao */
	private $roleDao;

	/** @var EntityDao */
	private $twitterDao;
	
	/** @var EntityDao */
	private $userDao;

	/** @var User */
	private $user;

	public function __construct(Container $container)
	{
		parent::__construct($container);
		$this->facebookDao = $this->em->getDao(Facebook::getClassName());
		$this->roleDao = $this->em->getDao(Role::getClassName());
		$this->twitterDao = $this->em->getDao(Twitter::getClassName());
		$this->userDao = $this->em->getDao(User::getClassName());

	}

	public function setUp()
	{
		parent::setUp();
		$role = $this->roleFacade->create(Role::ROLE_CANDIDATE);
		$this->user = $this->userFacade->create(self::MAIL, 'heslo', $role);
		$this->user->facebook = (new Facebook())->setId(self::FACEBOOK_ID);
		$this->user->twitter = (new Twitter())->setId(self::TWITTER_ID);
		
		$this->userDao->save($this->user);
	}

	public function testCreate()
	{
		$mail = 'ringo@beatles.com';
		$password = 'yellowSubmarine';
		$role = $this->roleFacade->findByName(Role::ROLE_CANDIDATE);
		
		Assert::null($this->userFacade->create(self::MAIL, self::PASSWORD, $role)); // Create user with wxisting e-mail

		$user = $this->userFacade->create($mail, $password, $role);
		Assert::type(User::getClassName(), $user);
		Assert::same($user->mail, $mail);
		Assert::true(Passwords::verify($password, $user->hash));

		Assert::true(in_array(Role::ROLE_CANDIDATE, $user->getRolesPairs()));

		$this->userFacade->delete($user);
	}

	public function testDelete()
	{	
		$this->userFacade->delete($this->user);

		Assert::count(1, $this->roleDao->findAll());
		Assert::count(0, $this->userDao->findAll());
		Assert::count(0, $this->facebookDao->findAll());
		Assert::count(0, $this->twitterDao->findAll());
	}

	public function testFindByFacebookId()
	{
		$user = $this->userFacade->findByFacebookId(self::FACEBOOK_ID);
		
		Assert::type(User::getClassName(), $user);
		Assert::same(self::FACEBOOK_ID, $user->facebook->id);
	}

	public function testFindByTwitterId()
	{
		$user = $this->userFacade->findByTwitterId(self::TWITTER_ID);
		
		Assert::type(User::getClassName(), $user);
		Assert::same(self::TWITTER_ID, $user->twitter->id);
	}
	
	public function testFindByMail()
	{
		$user = $this->userFacade->findByMail(self::MAIL);

		Assert::type(User::getClassName(), $user);
		Assert::same(self::MAIL, $user->mail);
	}

	public function testIsUnique()
	{
		Assert::false($this->userFacade->isUnique(self::MAIL));
		Assert::true($this->userFacade->isUnique('not@unique.com'));
	}

	public function testSetRecovery()
	{
		$this->user = $this->userFacade->setRecovery($this->user);

		/* @var $user User */
		$user = $this->userDao->find($this->user->id);
		Assert::same($this->user->recoveryToken, $user->recoveryToken);
		Assert::equal($this->user->recoveryExpiration, $user->recoveryExpiration);
	}

	public function testHardDelete()
	{
		$id = $this->user->id;

		$this->userFacade->hardDelete($id);

		Assert::count(0, $this->facebookDao->findAll());
		Assert::count(0, $this->twitterDao->findAll());
		Assert::null($this->userDao->find($id));
	}

	public function testFindByRecoveryToken()
	{
		// Expired token
		$this->user->setRecovery(self::EXPIRED_TOKEN, 'now - 1 day');
		$this->userDao->save($this->user);

		Assert::null($this->userFacade->findByRecoveryToken(self::EXPIRED_TOKEN));

		/* @var $user Entity\User */
		$user = $this->userDao->find($this->user->id);
		Assert::null($user->recoveryExpiration);
		Assert::null($user->recoveryToken);

		// Valid token
		$this->user->setRecovery(self::VALID_TOKEN, 'now + 1 day');
		$this->userDao->save($this->user);

		$user = $this->userFacade->findByRecoveryToken(self::VALID_TOKEN);
		Assert::type(User::getClassName(), $user);
		Assert::same(self::VALID_TOKEN, $user->recoveryToken);
	}
	
}

$test = new UserFacadeTest($container);
$test->run();
