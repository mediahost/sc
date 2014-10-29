<?php

namespace Test\Model\Facade;

use App\Model\Entity\Auth;
use App\Model\Entity\Registration;
use App\Model\Entity\Role;
use App\Model\Entity\User;
use DateTime;
use Nette\DI\Container;
use Nette\Security\Passwords;
use Tester\Assert;

$container = require __DIR__ . '/../../bootstrap.php';

/**
 * TEST: RegistrationFacade
 *
 * @testCase
 * @phpVersion 5.4
 */
class RegistrationFacadeTest extends BaseFacade
{

	const U_MAIL = 'ferda@sekora.cz';
	const U_NAME = 'Ferda Mravenec';
	const A_KEY = 'mravececF124B';
	const A_SOURCE = 'mraveniste';
	const A_TOKEN = 't0k3N';
	const A_PASSWORD = 'beruska123';
	const B_KEY = 'hmyzM144575';
	const B_SOURCE = 'les';
	const B_TOKEN = 't0k3N';

	/** @var Registrationstration;

	  /** @var EntityDao */
	public $registrationDao;

	public function __construct(Container $container)
	{
		parent::__construct($container);
		$this->registrationDao = $this->em->getDao(Registration::getClassName());

		$this->registration = new Registration();
		$this->registration->setMail(self::U_MAIL)
				->setName(self::U_NAME)
				->setKey(self::A_KEY)
				->setSource(self::A_SOURCE)
				->setToken(self::A_TOKEN)
				->setHash(Passwords::hash(self::A_PASSWORD));
	}

	public function setUp()
	{
		parent::setUp();
		$role = $this->roleFacade->create(Role::ROLE_CANDIDATE);
		$this->registration
				->setRole($role);
	}

	// <editor-fold defaultstate="expanded" desc="tests">

	public function testRegisterAndMerge()
	{
		$this->roleFacade->create(Role::ROLE_COMPANY);

		$user = (new User())
				->setMail(self::U_MAIL)
				->setName(self::U_NAME);

		$authA = (new Auth())
				->setKey(self::A_KEY)
				->setSource(self::A_SOURCE)
				->setToken(self::A_TOKEN);

		$authB = (new Auth())
				->setKey(self::B_KEY)
				->setSource(self::B_SOURCE)
				->setToken(self::B_TOKEN);

		$saved = $this->registrationFacade->register($user, $authA);

		Assert::count(1, $saved->auths);
		$auth = $saved->auths[0];
		Assert::type(Auth::getClassName(), $auth);
		Assert::same(self::A_KEY, $auth->key);
		Assert::same(self::A_SOURCE, $auth->source);

		Assert::count(1, $saved->roles);
		$role = $saved->roles[0];
		Assert::type(Role::getClassName(), $role);
		Assert::same(Role::ROLE_COMPANY, $role->name);

		$this->registrationFacade->merge($saved, $authB);
		Assert::count(2, $saved->auths);
	}

	public function testFindByVerificationToken()
	{
		$expReg = $this->registrationFacade->registerTemporarily($this->registration);
		$expReg->verificationExpiration = new DateTime('now - 1 day');
		$this->registrationDao->save($expReg);

		Assert::null($this->registrationFacade->findByVerificationToken($expReg->verificationToken));
		Assert::count(0, $this->registrationDao->findAll());

		$reg = $this->registrationFacade->registerTemporarily($this->registration);
		$reg = $this->registrationFacade->findByVerificationToken($reg->verificationToken);
		Assert::type(Registration::getClassName(), $reg);
	}

	public function testRegisterTemporarily()
	{
		$this->registrationFacade->registerTemporarily($this->registration);
		$this->registrationFacade->registerTemporarily($this->registration);
		$this->registrationFacade->registerTemporarily($this->registration);
		$this->registrationFacade->registerTemporarily($this->registration);
		$old = $this->registrationFacade->registerTemporarily($this->registration);
		Assert::count(1, $this->registrationDao->findAll());

		$reg = $this->registrationFacade->findByVerificationToken($old->verificationToken);

		Assert::type(Registration::getClassName(), $reg);
		Assert::type('string', $reg->verificationToken);
		Assert::type('\DateTime', $reg->verificationExpiration);
	}

	public function testVerify()
	{
		$this->roleFacade->create(Role::ROLE_CANDIDATE);
		$reg = $this->registrationFacade->registerTemporarily($this->registration);

		Assert::null($this->registrationFacade->verify('invalidToken'));

		$user = $this->registrationFacade->verify($reg->verificationToken);
		Assert::type(User::getClassName(), $user);
		Assert::same(self::U_MAIL, $user->mail);
		Assert::same(self::U_NAME, $user->name);

		$auth = $this->authFacade->findByKey(self::A_SOURCE, self::A_KEY);
		Assert::same(self::A_KEY, $auth->key);
		Assert::same(self::A_SOURCE, $auth->source);
		Assert::same(self::A_TOKEN, $auth->token);
		Assert::true(Passwords::verify(self::A_PASSWORD, $auth->hash));
	}

	// </editor-fold>
}

$test = new RegistrationFacadeTest($container);
$test->run();
