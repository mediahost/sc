<?php

namespace App\AppModule\Presenters;

use App\Components\Candidate\IProfileControlFactory;
use App\Components\Candidate\ProfileControl;
use App\Model\Entity\Candidate;

class CandidatePresenter extends BasePresenter
{
	// <editor-fold desc="inject">

	/** @var IProfileControlFactory @inject */
	public $iProfileControlFactory;

	// </editor-fold>
	// <editor-fold desc="variables">

	/** @var Candidate */
	private $candidate;

	// </editor-fold>

	protected function startup()
	{
		$this->redirect(':Front:Candidate:');
		parent::startup();
	}

	/**
	 * @secured
	 * @resource('candidate')
	 * @privilege('default')
	 */
	public function actionDefault()
	{
		$this->candidate = $this->user->identity->candidate;
	}

	public function renderDefault()
	{
		$this->template->candidate = $this->candidate;
	}

	// <editor-fold desc="forms">

	/** @return ProfileControl */
	public function createComponentProfileForm()
	{
		$control = $this->iProfileControlFactory->create();
		$control->setCandidate($this->candidate);
		$control->onAfterSave = function (Candidate $saved) {
			$message = $this->translator->translate('Candidate \'%candidate%\' was successfully saved.', ['candidate' => (string)$saved]);
			$this->flashMessage($message, 'success');
			$this->redirect('this');
		};
		return $control;
	}

	// </editor-fold>
}
