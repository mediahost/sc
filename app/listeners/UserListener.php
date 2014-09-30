<?php

namespace App\Listeners;

class UserListener extends \Nette\Object implements \Kdyby\Events\Subscriber
{
	
	/** @var \App\Model\Storage\UserSettingsStorage @inject */
	public $settingsStorage;
	
	public function getSubscribedEvents()
	{
		
        return array(
            'Nette\Security\User::onLoggedIn'  => 'userLoggedIn',
            'Nette\Security\User::onLoggedOut' => 'userLoggedOut',
			'App\Components\User\SettingsControl::onSave' => 'settingsSave'
        );
	}
	
    public function userLoggedIn(\Nette\Security\User $user)
    {
		$this->settingsStorage->loadSettings($user->id);
    }

    public function userLoggedOut(\Nette\Security\User $user)
    {
		$this->settingsStorage->wipe();
        \Tracy\Debugger::barDump('Uživatel ' . $user->getIdentity()->name . 'byl odhlášen.');
    }

	public function settingsSave($userId, \App\Model\Entity\UserSettings $settings)
	{
		\Tracy\Debugger::barDump('Uživatel ' . $user->getIdentity()->name . 'změnil svá osobní nastavení.');
		$this->settingsStorage->save($userId, $settings);
	}
}
