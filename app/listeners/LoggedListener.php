<?php

namespace App\Listeners;

use App\Model\Storage\GuestSettingsStorage;
use Kdyby\Events\Subscriber;
use Nette\Object;
use Nette\Security\User;

class LoggedListener extends Object implements Subscriber
{

	/** @var GuestSettingsStorage @inject */
	public $guestStorage;

	public function getSubscribedEvents()
	{

		return array(
			'Nette\Security\User::onLoggedIn' => 'userLoggedIn',
			'Nette\Security\User::onLoggedOut' => 'userLoggedOut',
		);
	}

	public function userLoggedIn(User $user)
	{
		$this->guestStorage
				->save($user->id)
				->wipe();
	}

	public function userLoggedOut(User $user)
	{
		
	}

}
