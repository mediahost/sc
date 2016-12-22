<?php

namespace App\Components\Auth;

use App\Components\BaseControl;

class SignOut extends BaseControl
{

	const REDIRECT_AFTER_LOGOUT = ':Front:Homepage:';

	public function handleSignOut($redirectUrl = NULL)
	{
		$this->presenter->user->logout();
		$message = $this->translator->translate('You have been successfuly signed out.');
		$this->presenter->flashMessage($message, 'success');
		if ($redirectUrl) {
			$this->presenter->redirectUrl($redirectUrl);
		}
		$this->presenter->redirect(self::REDIRECT_AFTER_LOGOUT);
	}

}

interface ISignOutFactory
{

	/** @return SignOut */
	function create();
}
