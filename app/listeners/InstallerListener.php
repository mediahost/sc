<?php

namespace App\Listeners;

use App\Extensions\Installer;
use Kdyby\Events\Subscriber;
use Nette\Object;
use Tracy\Debugger;

class InstallerListener extends Object implements Subscriber
{

	public function getSubscribedEvents()
	{
		return [
			'App\Extensions\Installer::onSuccessInstall' => 'successInstall',
			'App\Extensions\Installer::onLockedInstall' => 'lockedInstall',
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
