<?php

namespace App\Components\Sign;

use Nette\Application\UI\Control;

/**
 * 
 */
class SignOutControl extends Control
{

	public function render()
	{
		$template = $this->template;
		$template->icon = NULL;
		$template->setFile(__DIR__ . '/SignOutControl.latte');
		$template->render();
	}

	public function renderIcon()
	{
		$template = $this->template;
		$template->icon = true;
		$template->setFile(__DIR__ . '/SignOutControl.latte');
		$template->render();
	}

	public function handleSignOut()
	{
		$this->presenter->getUser()->logout();
		$this->presenter->flashMessage('You have been signed out.');
		$this->presenter->redirect(':Front:Sign:in');
	}

}

interface ISignOutControlFactory
{

	/** @return SignOutControl */
	function create();
}
