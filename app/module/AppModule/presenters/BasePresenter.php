<?php

namespace App\AppModule\Presenters;

use App\BaseModule\Presenters\BasePresenter as BaseBasePresenter;
use App\Model\Entity\Role;

abstract class BasePresenter extends BaseBasePresenter
{

	protected function startup()
	{
		parent::startup();
		$this->checkUncompleteAccount();
	}

	protected function beforeRender()
	{
		parent::beforeRender();
		$this->template->isCompleteAccount = !$this->isUncompleteAccount();
		$this->template->allowedLanguages = $this->languageService->allowedLanguages;
	}

	/**
	 * If only role is SIGNED, then redirect to complete account
	 */
	private function checkUncompleteAccount()
	{
		if ($this->isUncompleteAccount() && $this->name !== 'App:CompleteAccount') {
			$this->redirect(':App:CompleteAccount:');
		}
	}

	/**
	 * Check if user account is uncomplete
	 * @return bool
	 */
	private function isUncompleteAccount()
	{
		return $this->user->isInRole(Role::SIGNED) && count($this->user->roles) === 1;
	}

}
