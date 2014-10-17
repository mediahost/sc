<?php

namespace App\Components\Sign;

use App\Components;

/**
 * SignOutControl
 */
class SignOutControl extends Components\BaseControl
{

	// <editor-fold defaultstate="collapsed" desc="constants & variables">
	// </editor-fold>
	// <editor-fold defaultstate="collapsed" desc="setters">
	// </editor-fold>
	// <editor-fold defaultstate="collapsed" desc="getters">
	// </editor-fold>
	// <editor-fold defaultstate="collapsed" desc="renderers">
	
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

	// </editor-fold>
	// <editor-fold defaultstate="collapsed" desc="handlers">

	public function handleSignOut()
	{
		$this->presenter->getUser()->logout();
		$this->presenter->flashMessage('You have been signed out.');
		$this->presenter->redirect(':Front:Sign:in');
	}

	// </editor-fold>
}

interface ISignOutControlFactory
{

	/** @return SignOutControl */
	function create();
}
