<?php

namespace Test\Presenters\FrontModule;

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

	/** @var Presenter */
	protected $tester;

	/** @var User */
	protected $identity;

	public function __construct(Container $container)
	{
		parent::__construct($container);
		$this->tester = new Presenter($container);
		$this->identity = $this->container->getByType('Nette\Security\User');
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

	public function logout()
	{
		$this->identity->logout();
	}

}
