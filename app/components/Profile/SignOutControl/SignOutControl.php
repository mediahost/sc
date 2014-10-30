<?php

namespace App\Components\Profile;

use App\Components\BaseControl;

class SignOutControl extends BaseControl
{

	public function handleSignOut()
	{
		$this->presenter->user->logout();
		$this->presenter->flashMessage('You have been successfuly signed out.', 'success');
		$this->presenter->redirect(':Front:NewSign:in');
	}

}

interface ISignOutControlFactory
{

	/** @return SignOutControl */
	function create();
}
