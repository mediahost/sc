<?php

namespace App\Listeners;

use App\Model\Facade\UserFacade;
use Kdyby\Events\Subscriber;
use Nette\Http\Session;
use Nette\Object;
use Nette\Security;
use Tracy\Debugger;

class LoggedListener extends Object implements Subscriber
{

	/** @var UserFacade @inject */
	public $userFacade;

	/** @var Session @inject */
	public $session;

	public function getSubscribedEvents()
	{
		return array(
			'Nette\Security\User::onLoggedIn' => 'userLoggedIn',
			'Nette\Security\User::onLoggedOut' => 'userLoggedOut',
		);
	}

	public function userLoggedIn(Security\User $user)
	{
		$wpLogin = $this->session->getSection('wp_login');
		$wpLogin->data = $user->getIdentity()->getData();
	}

	public function userLoggedOut(Security\User $user)
	{
		$wpLogin = $this->session->getSection('wp_login');
		unset($wpLogin->data);
	}

}
