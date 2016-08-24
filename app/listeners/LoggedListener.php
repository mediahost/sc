<?php

namespace App\Listeners;

use App\Model\Facade\UserFacade;
use Kdyby\Events\Subscriber;
use Nette\Object;
use Nette\Security;

class LoggedListener extends Object implements Subscriber
{

	/** @var UserFacade @inject */
	public $userFacade;

	public function getSubscribedEvents()
	{
		return array(
			'Nette\Security\User::onLoggedIn' => 'userLoggedIn',
			'Nette\Security\User::onLoggedOut' => 'userLoggedOut',
		);
	}

	public function userLoggedIn(Security\User $identity)
	{

	}

	public function userLoggedOut(Security\User $user)
	{

	}

}
