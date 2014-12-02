<?php

namespace App\AppModule\Presenters;

use App\Components\AfterRegistration\CompleteAccountControl;
use App\Components\AfterRegistration\ICompleteAccountControlFactory;
use App\Model\Entity\Candidate;
use App\Model\Entity\Company;
use App\Model\Entity\Role;

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
		if (!$this->user->isInRole(Role::SIGNED) ||
				($this->user->isInRole(Role::SIGNED) && count($this->user->roles) !== 1)) {
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
		$control = $this->iCompleteAccountControlFactory->create();
		$control->setUserId($this->user->id);
		$control->onCreateCandidate[] = function (CompleteAccountControl $control, Candidate $candidate) {
			$this->flashMessage('Your candidate account is complete. Enjoy your ride!', 'success');
			$this->redirect(':App:Candidate:');
		};
		$control->onCreateCompany[] = function (CompleteAccountControl $control, Company $company) {
			$this->flashMessage('Your company account is complete. Enjoy your ride!', 'success');
			$this->redirect(':App:Company:');
		};
		return $control;
	}

	// </editor-fold>
}
