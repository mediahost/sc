<?php

namespace App\Listeners;

use App\Model\Storage\UserSettingsStorage;
use Kdyby\Events\Subscriber;
use Nette\Object;
use Nette\Security\User;

class LoggedListener extends Object implements Subscriber
{

	/** @var UserSettingsStorage @inject */
	public $settingsStorage;

	public function getSubscribedEvents()
	{

		return array(
			'Nette\Security\User::onLoggedIn' => 'userLoggedIn',
			'Nette\Security\User::onLoggedOut' => 'userLoggedOut',
		);
	}

	public function userLoggedIn(User $user)
	{
//		$this->settingsStorage->load($user->id);
	}

	public function userLoggedOut(User $user)
	{
//		$this->settingsStorage->wipe();
	}
}
