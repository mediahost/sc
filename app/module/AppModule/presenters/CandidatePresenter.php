<?php

namespace App\AppModule\Presenters;

use App\Components\Candidate\IProfileControlFactory;
use App\Components\Candidate\ProfileControl;
use App\Model\Entity\Candidate;
use App\TaggedString;

class CandidatePresenter extends BasePresenter
{
	// <editor-fold defaultstate="collapsed" desc="inject">

	/** @var IProfileControlFactory @inject */
	public $iProfileControlFactory;

	// </editor-fold>
	// <editor-fold defaultstate="collapsed" desc="variables">

	/** @var Candidate */
	private $candidate;

	// </editor-fold>

	protected function startup()
	{
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

	// <editor-fold defaultstate="collapsed" desc="forms">

	/** @return ProfileControl */
	public function createComponentProfileForm()
	{
		$control = $this->iProfileControlFactory->create();
		$control->setCandidate($this->candidate);
		$control->onAfterSave = function (Candidate $saved) {
			$message = new TaggedString('Candidate \'%s\' was successfully saved.', (string) $saved);
			$this->flashMessage($message, 'success');
			$this->redirect('this');
		};
		return $control;
	}

	// </editor-fold>
}
