<?php

namespace App\Listeners;

class UserListener extends \Nette\Object implements \Kdyby\Events\Subscriber
{

	// <editor-fold defaultstate="collapsed" desc="constants & variables">
	/** @var \App\Model\Storage\UserSettingsStorage @inject */
	public $settingsStorage;

	// </editor-fold>
	// <editor-fold defaultstate="collapsed" desc="public">
	public function getSubscribedEvents()
	{

		return array(
			'Nette\Security\User::onLoggedIn' => 'userLoggedIn',
			'Nette\Security\User::onLoggedOut' => 'userLoggedOut',
		);
	}

	public function userLoggedIn(\Nette\Security\User $user)
	{
		$this->settingsStorage->load($user->id);
	}

	public function userLoggedOut(\Nette\Security\User $user)
	{
		$this->settingsStorage->wipe();
	}

	// </editor-fold>
}
