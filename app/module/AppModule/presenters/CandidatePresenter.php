<?php

namespace App\AppModule\Presenters;

use App\Components\Candidate\IProfileFactory;
use App\Components\Candidate\Profile;
use App\Model\Entity\Candidate;

class CandidatePresenter extends BasePresenter
{
	// <editor-fold desc="inject">

	/** @var IProfileFactory @inject */
	public $iProfileFactory;

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

	/** @return Profile */
	public function createComponentProfileForm()
	{
		$control = $this->iProfileFactory->create();
		$control->setPerson($this->candidate);
		$control->onAfterSave = function (Person $saved) {
			$message = $this->translator->translate('Profile \'%person%\' was successfully saved.', ['person' => (string)$saved]);
			$this->flashMessage($message, 'success');
			$this->redirect('this');
		};
		return $control;
	}

	// </editor-fold>
}
