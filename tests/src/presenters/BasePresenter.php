<?php

namespace Test\Presenters;

use App\Extensions\Installer;
use App\Model\Facade\UserFacade;
use Nette\DI\Container;
use Nette\Security\IIdentity;
use Nette\Security\User;
use Test\ParentTestCase;
use Test\Presenters\Presenter;
use Tester\Assert;

/**
 * Parent presenter
 */
abstract class BasePresenter extends ParentTestCase
{

	/** @var UserFacade @inject */
	public $userFacade;

	/** @var Installer @inject */
	public $installer;

	/** @var Presenter */
	protected $tester;

	/** @var User */
	protected $identity;

	public function __construct(Container $container)
	{
		parent::__construct($container);
		$this->identity = $container->getByType('Nette\Security\User');
		$this->tester = new Presenter($container);
	}

	public function login(IIdentity $user)
	{
		Assert::false($this->identity->loggedIn);
		$this->identity->login($user);
		Assert::true($this->identity->loggedIn);
	}

	public function loginAdmin()
	{
		$this->login($this->userFacade->findByMail('admin'));
	}

	public function loginSuperadmin()
	{
		$this->login($this->userFacade->findByMail('superadmin'));
	}

	public function loginCandidate()
	{
		$this->login($this->userFacade->findByMail('candidate'));
	}

	public function loginCompany()
	{
		$this->login($this->userFacade->findByMail('company'));
	}

	public function loginSigned()
	{
		$this->login($this->userFacade->findByMail('signed'));
	}

	public function logout()
	{
		$this->identity->logout();
	}

}
