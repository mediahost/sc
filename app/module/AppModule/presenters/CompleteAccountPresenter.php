<?php

namespace App\AppModule\Presenters;

use App\Components\AfterRegistration\CompleteAccountControl;
use App\Components\AfterRegistration\ICompleteAccountControlFactory;
use App\Model\Entity\Role;
use App\Model\Entity\User;

/**
 * Complete account presenter
 */
class CompleteAccountPresenter extends BasePresenter
{

	/** @var ICompleteAccountControlFactory @inject */
	public $iCompleteAccountControlFactory;

	protected function startup()
	{
		parent::startup();
		if (!$this->user->isInRole(Role::ROLE_SIGNED) ||
				($this->user->isInRole(Role::ROLE_SIGNED) && count($this->user->roles) !== 1)) {
			$this->flashMessage('Your registration is already complete.', 'info');
			$this->redirect('Dashboard:');
		}
	}

	/**
	 * @secured
	 * @resource('registration')
	 * @privilege('default')
	 */
	public function actionDefault()
	{
		
	}

	// <editor-fold defaultstate="collapsed" desc="components">

	/** @return CompleteAccountControl */
	protected function createComponentCompleteAccount()
	{
		$user = $this->em->getDao(User::getClassName())->find($this->user->id);
		
		$control = $this->iCompleteAccountControlFactory->create();
		$control->setUser($user);
		return $control;
	}

	// </editor-fold>
}
