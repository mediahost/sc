<?php

namespace App\Components\Sign;

use App\Components\Control;

/**
 * SignOutControl
 */
class SignOutControl extends \App\Components\BaseControl
{

	public function render()
	{
		$template = $this->getTemplate();
		$template->icon = NULL;
		$template->setFile(__DIR__ . '/default.latte');
		$template->render();
	}

	public function renderIcon()
	{
		$template = $this->getTemplate();
		$template->icon = true;
		$template->setFile(__DIR__ . '/default.latte');
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
