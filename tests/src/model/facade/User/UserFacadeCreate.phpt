<?php

namespace Test\Model\Facade;

use App\Model\Entity\Facebook;
use App\Model\Entity\PageConfigSettings;
use App\Model\Entity\PageDesignSettings;
use App\Model\Entity\Registration;
use App\Model\Entity\Role;
use App\Model\Entity\Twitter;
use App\Model\Entity\User;
use Tester\Assert;

$container = require __DIR__ . '/../../../bootstrap.php';

/**
 * TEST: UserFacade Create
 *
 * @testCase
 * @phpVersion 5.4
 */
class UserFacadeCreateTest extends UserFacade
{

	public function testCreate()
	{
		$mail = 'second.user@domain.com';
		$password = 'password654321';
		$role = $this->roleFacade->findByName(Role::CANDIDATE);

		Assert::count(3, $this->userDao->findAll());

		Assert::null($this->userFacade->create(self::MAIL, self::PASSWORD, $role)); // Create user with existing e-mail

		$user = $this->userFacade->create($mail, $password, $role);
		Assert::type(User::getClassName(), $user);
		Assert::same($user->mail, $mail);
		Assert::true($user->verifyPassword($password));

		Assert::true(in_array(Role::CANDIDATE, $user->getRoles()));

		Assert::same(self::ID_NEW + 1, $user->id);

		Assert::count(4, $this->userDao->findAll());
	}

	public function testCreateRegistration()
	{
		$mail = 'second.user@domain.com';
		$password = 'password654321';
		$role = $this->roleFacade->findByName(Role::CANDIDATE);

		$user = new User($mail);
		$user->password = $password;
		$user->facebook = new Facebook('fb22');
		$user->twitter = new Twitter('tw22');
		$user->pageConfigSettings = new PageConfigSettings();
		$user->pageDesignSettings = new PageDesignSettings();
		$user->requiredRole = $role;

		Assert::count(0, $this->registrationDao->findAll());
		$this->userFacade->createRegistration($user);
		Assert::count(1, $this->registrationDao->findAll());

		/* @var $registration Registration */
		$registration = $this->registrationDao->find(1);
		Assert::same($mail, $registration->mail);
		Assert::same($role->id, $registration->role->id);
		Assert::same($user->facebook->id, $registration->facebookId);
		Assert::same($user->twitter->id, $registration->twitterId);

		// clear previous with same mail
		$this->userFacade->createRegistration($user);
		$this->registrationDao->clear();
		Assert::count(1, $this->registrationDao->findAll());

		$user->mail = 'another.user@domain.com';
		$this->userFacade->createRegistration($user);
		$this->registrationDao->clear();
		Assert::count(2, $this->registrationDao->findAll());
	}

	public function testCreateUserFromRegistration()
	{
		$password = 'password';
		$user = new User('new@user.com');
		$user->setPassword($password)
				->setFacebook(new Facebook('facebookID'))
				->setTwitter(new Twitter('twitterID'))
				->setRequiredRole($this->roleFacade->findByName(Role::CANDIDATE));
		$registration = $this->userFacade->createRegistration($user);
		$this->registrationDao->clear();
		Assert::count(1, $this->registrationDao->findAll());
		Assert::count(3, $this->userDao->findAll());

		$initRole = $this->roleFacade->findByName(Role::CANDIDATE);
		$findedRegistration = $this->registrationDao->find($registration->id);
		$this->userFacade->createFromRegistration($findedRegistration, $initRole);
		$this->userDao->clear();
		Assert::count(4, $this->userDao->findAll());

		$newUser = $this->userFacade->findByMail($user->mail);
		Assert::type(User::getClassName(), $newUser);
		Assert::same($user->mail, $newUser->mail);
		Assert::true($newUser->verifyPassword($password));
		Assert::same($initRole->id, $newUser->getMaxRole()->id);
		Assert::same($user->requiredRole->id, $newUser->requiredRole->id);
		Assert::same($user->facebook->id, $newUser->facebook->id);
		Assert::same($user->twitter->id, $newUser->twitter->id);
	}

}

$test = new UserFacadeCreateTest($container);
$test->run();
