<?php

namespace Test\Model\Facade;

use App\Model\Entity\Facebook;
use App\Model\Entity\PageConfigSettings;
use App\Model\Entity\PageDesignSettings;
use App\Model\Entity\Registration;
use App\Model\Entity\Role;
use App\Model\Entity\Twitter;
use App\Model\Entity\User;
use Kdyby\Doctrine\EntityDao;
use Nette\DI\Container;
use Nette\Security\Passwords;
use Nette\Utils\DateTime;
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

	const MAIL = 'user.mail@domain.com';
	const PASSWORD = 'password123456';
	const EXPIRED_TOKEN = 'expiredToken';
	const VALID_TOKEN = 'validToken';
	const TWITTER_ID = 'tw123456789';
	const FACEBOOK_ID = 'fb123456789';

	/** @var EntityDao */
	private $userDao;

	/** @var EntityDao */
	private $roleDao;

	/** @var EntityDao */
	private $registrationDao;

	/** @var EntityDao */
	private $facebookDao;

	/** @var EntityDao */
	private $twitterDao;

	/** @var EntityDao */
	private $pageConfigSettingsDao;

	/** @var EntityDao */
	private $pageDesignSettingsDao;

	/** @var User */
	private $user;

	public function __construct(Container $container)
	{
		parent::__construct($container);
		$this->userDao = $this->em->getDao(User::getClassName());
		$this->roleDao = $this->em->getDao(Role::getClassName());
		$this->registrationDao = $this->em->getDao(Registration::getClassName());
		$this->facebookDao = $this->em->getDao(Facebook::getClassName());
		$this->twitterDao = $this->em->getDao(Twitter::getClassName());
		$this->pageConfigSettingsDao = $this->em->getDao(PageConfigSettings::getClassName());
		$this->pageDesignSettingsDao = $this->em->getDao(PageDesignSettings::getClassName());
	}

	public function setUp()
	{
		parent::setUp();
		$role = $this->roleFacade->create(Role::CANDIDATE);
		$this->user = $this->userFacade->create(self::MAIL, 'password', $role);
		$this->user->facebook = new Facebook(self::FACEBOOK_ID);
		$this->user->twitter = new Twitter(self::TWITTER_ID);

		$this->userDao->save($this->user);
	}

	public function testCreate()
	{
		$mail = 'second.user@domain.com';
		$password = 'password654321';
		$role = $this->roleFacade->findByName(Role::CANDIDATE);

		Assert::null($this->userFacade->create(self::MAIL, self::PASSWORD, $role)); // Create user with existing e-mail

		$user = $this->userFacade->create($mail, $password, $role);
		Assert::type(User::getClassName(), $user);
		Assert::same($user->mail, $mail);
		Assert::true(Passwords::verify($password, $user->hash));

		Assert::true(in_array(Role::CANDIDATE, $user->getRolesPairs()));

		Assert::same(1, $this->user->id);
		Assert::same(2, $user->id);

		$this->userDao->delete($user);
		Assert::null($user->id);
	}

	public function testDelete()
	{
		$this->userFacade->deleteById($this->user->id);

		Assert::count(1, $this->roleDao->findAll());
		Assert::count(0, $this->userDao->findAll());
		Assert::count(0, $this->facebookDao->findAll());
		Assert::count(0, $this->twitterDao->findAll());
		Assert::count(0, $this->pageConfigSettingsDao->findAll());
		Assert::count(0, $this->pageDesignSettingsDao->findAll());
	}

	public function testFindBy()
	{
		$user1 = $this->userFacade->findByMail(self::MAIL);
		Assert::type(User::getClassName(), $user1);
		Assert::same(self::MAIL, $user1->mail);

		$user2 = $this->userFacade->findByFacebookId(self::FACEBOOK_ID);
		Assert::type(User::getClassName(), $user2);
		Assert::same(self::FACEBOOK_ID, $user2->facebook->id);

		$user3 = $this->userFacade->findByTwitterId(self::TWITTER_ID);
		Assert::type(User::getClassName(), $user3);
		Assert::same(self::TWITTER_ID, $user3->twitter->id);
	}

	public function testRecoveryToken()
	{
		// Expired token
		$this->user->setRecovery(self::EXPIRED_TOKEN, 'now - 1 day');
		$this->userDao->save($this->user);

		Assert::null($this->userFacade->findByRecoveryToken(self::EXPIRED_TOKEN));

		/* @var $user1 User */
		$user1 = $this->userDao->find($this->user->id);
		Assert::null($user1->recoveryExpiration);
		Assert::null($user1->recoveryToken);

		// Valid token
		$this->user->setRecovery(self::VALID_TOKEN, 'now + 1 day');
		$this->userDao->save($this->user);

		/* @var $user2 User */
		$user2 = $this->userFacade->findByRecoveryToken(self::VALID_TOKEN);
		Assert::type(User::getClassName(), $user2);
		Assert::same(self::VALID_TOKEN, $user2->recoveryToken);
	}

	public function testSetRecovery()
	{
		$this->user = $this->userFacade->setRecovery($this->user);

		/* @var $user User */
		$user = $this->userDao->find($this->user->id);
		Assert::same($this->user->recoveryToken, $user->recoveryToken);
		Assert::equal($this->user->recoveryExpiration, $user->recoveryExpiration);
	}

	public function testIsUnique()
	{
		Assert::false($this->userFacade->isUnique(self::MAIL));
		Assert::true($this->userFacade->isUnique('not@unique.com'));
	}

	public function testRegistration()
	{
		$this->user->requiredRole = $this->roleDao->find(1);
		Assert::count(0, $this->registrationDao->findAll());
		$this->userFacade->createRegistration($this->user);
		Assert::count(1, $this->registrationDao->findAll());

		/* @var $registration Registration */
		$registration = $this->registrationDao->find(1);
		Assert::same($this->user->mail, $registration->mail);
		Assert::same($this->user->hash, $registration->hash);
		Assert::same($this->user->requiredRole->id, $registration->role->id);
		Assert::same($this->user->facebook->id, $registration->facebookId);
		Assert::same($this->user->twitter->id, $registration->twitterId);

		// clear previous with same mail
		$this->userFacade->createRegistration($this->user);
		Assert::count(1, $this->registrationDao->findAll());

		$this->user->mail = 'another.user@domain.com';
		$this->userFacade->createRegistration($this->user);
		Assert::count(2, $this->registrationDao->findAll());
	}

	public function testCreateFromRegistration()
	{
		$user = new User;
		$user->setMail('new@user.com')
				->setPassword('password')
				->setFacebook(new Facebook('facebookID'))
				->setTwitter(new Twitter('twitterID'))
				->setRequiredRole($this->roleDao->find(1));
		$registration = $this->userFacade->createRegistration($user);
		Assert::count(1, $this->registrationDao->findAll());

		$initRole = $this->roleFacade->create(Role::SIGNED);
		$this->userFacade->createFromRegistration($registration, $initRole);
		Assert::count(2, $this->userDao->findAll());

		$newUser = $this->userFacade->findByMail($user->mail);
		Assert::type(User::getClassName(), $newUser);
		Assert::same($user->mail, $newUser->mail);
		Assert::same($user->hash, $newUser->hash);
		Assert::same($initRole->id, $newUser->getMaxRole()->id);
		Assert::same($user->requiredRole->id, $newUser->requiredRole->id);
		Assert::same($user->facebook->id, $newUser->facebook->id);
		Assert::same($user->twitter->id, $newUser->twitter->id);
	}

	public function testVerificationToken()
	{
		$role = $this->roleFacade->create(Role::COMPANY);
		
		$registration1 = new Registration;
		$registration1->mail = 'user1@mail.com';
		$registration1->role = $role;
		$registration1->verificationToken = 'verificationToken1';
		$registration1->verificationExpiration = DateTime::from('now +1 hour');
		$this->registrationDao->save($registration1);
		Assert::count(1, $this->registrationDao->findAll());
		
		$findedRegistration1 = $this->userFacade->findByVerificationToken($registration1->verificationToken);
		Assert::type(Registration::getClassName(), $findedRegistration1);
		Assert::same($registration1->mail, $findedRegistration1->mail);
		
		$registration2 = new Registration;
		$registration2->mail = 'user2@mail.com';
		$registration2->role = $role;
		$registration2->verificationToken = 'verificationToken2';
		$registration2->verificationExpiration = DateTime::from('now -1 hour');
		$this->registrationDao->save($registration2);
		Assert::count(2, $this->registrationDao->findAll());
		
		$findedRegistration2 = $this->userFacade->findByVerificationToken($registration2->verificationToken);
		Assert::null($findedRegistration2);
		Assert::count(1, $this->registrationDao->findAll());
		
		Assert::null($this->userFacade->findByVerificationToken('unknown token'));
	}

	public function testAddRole()
	{
		$roleA = $this->roleFacade->create(Role::COMPANY);
		$roleB = $this->roleFacade->create(Role::ADMIN);
		$this->userFacade->addRole($this->user, Role::COMPANY);
		Assert::count(2, $this->user->roles);
		$this->user->removeRole($roleA);
		$this->userFacade->addRole($this->user, [Role::COMPANY, Role::ADMIN]);
		Assert::count(3, $this->user->roles);
	}

}

$test = new UserFacadeTest($container);
$test->run();
