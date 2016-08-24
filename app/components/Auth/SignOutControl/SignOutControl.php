<?php

namespace App\Components\Auth;

use App\Components\BaseControl;

class SignOutControl extends BaseControl
{

	const REDIRECT_AFTER_LOGOUT = ':Front:Homepage:';

	public function handleSignOut()
	{
		$this->presenter->user->logout();
		$message = $this->translator->translate('You have been successfuly signed out.');
		$this->presenter->flashMessage($message, 'success');
		$this->presenter->redirect(self::REDIRECT_AFTER_LOGOUT);
	}

}

interface ISignOutControlFactory
{

	/** @return SignOutControl */
	function create();
}
