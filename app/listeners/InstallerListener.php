<?php

namespace App\Listeners;

use App\Components\Installer;
use Kdyby\Events\Subscriber;
use Nette\Object;
use Tracy\Debugger;

class InstallerListener extends Object implements Subscriber
{

	public function getSubscribedEvents()
	{
		return [
			'App\Components\Installer::onSuccessInstall' => 'successInstall',
			'App\Components\Installer::onLockedInstall' => 'lockedInstall',
		];
	}

	public function successInstall(Installer $installer, $type)
	{
		Debugger::log($type . ' was installed', Debugger::INFO);
	}

	public function lockedInstall(Installer $installer, $type)
	{
		Debugger::log($type . ' wasn\'t installed - LOCKED', Debugger::WARNING);
	}

}
