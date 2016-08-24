<?php

namespace App\FrontModule\Presenters;

use App\Components\Candidate\IAddressControlFactory;
use App\Components\Candidate\IPhotoControlFactory;
use App\Components\Candidate\IProfileControlFactory;
use App\Components\Candidate\ProfileControl;
use App\Model\Entity\Candidate;

class CandidatePresenter extends BasePresenter
{
	// <editor-fold desc="inject">

	/** @var IProfileControlFactory @inject */
	public $iProfileControlFactory;

	/** @var IAddressControlFactory @inject */
	public $iAddressControlFactory;

	/** @var IPhotoControlFactory @inject */
	public $iPhotoControlFactory;

	// </editor-fold>
	// <editor-fold desc="variables">

	/** @var Candidate */
	private $candidate;

	// </editor-fold>

	protected function startup()
	{
		parent::startup();
		$this->candidate = $this->user->identity->candidate;
	}

	protected function beforeRender()
	{
		$this->template->candidate = $this->candidate;
		parent::beforeRender();
	}

	/**
	 * @secured
	 * @resource('candidate')
	 * @privilege('default')
	 */
	public function actionDefault()
	{

	}

	/**
	 * @secured
	 * @resource('candidate')
	 * @privilege('address')
	 */
	public function actionAddress()
	{

	}

	/**
	 * @secured
	 * @resource('candidate')
	 * @privilege('photo')
	 */
	public function actionPhoto()
	{

	}

	// <editor-fold desc="forms">

	/** @return ProfileControl */
	public function createComponentProfileForm()
	{
		$control = $this->iProfileControlFactory->create();
		$control->setCandidate($this->candidate);
		$control->onAfterSave = function (Candidate $saved) {
			$message = $this->translator->translate('Personal info for \'%candidate%\' was successfully saved.', ['candidate' => (string)$saved]);
			$this->flashMessage($message, 'success');
			$this->redirect('this');
		};
		return $control;
	}

	/** @return ProfileControl */
	public function createComponentAddressForm()
	{
		$control = $this->iAddressControlFactory->create();
		$control->setCandidate($this->candidate);
		$control->onAfterSave = function (Candidate $saved) {
			$message = $this->translator->translate('Address for \'%candidate%\' was successfully saved.', ['candidate' => (string)$saved]);
			$this->flashMessage($message, 'success');
			$this->redirect('this');
		};
		return $control;
	}

	/** @return ProfileControl */
	public function createComponentPhotoForm()
	{
		$control = $this->iPhotoControlFactory->create();
		$control->setCandidate($this->candidate);
		$control->onAfterSave = function (Candidate $saved) {
			$message = $this->translator->translate('Photo for \'%candidate%\' was successfully saved.', ['candidate' => (string)$saved]);
			$this->flashMessage($message, 'success');
			$this->redirect('this');
		};
		return $control;
	}

	// </editor-fold>
}
