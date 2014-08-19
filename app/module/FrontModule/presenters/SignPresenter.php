<?php

namespace App\FrontModule\Presenters;

/**
 * Sign in/out presenters.
 */
class SignPresenter extends BasePresenter
{

	/** @var \App\Components\ISignInControlFactory @inject */
	public $iSignInControlFactory;

	/** @var \App\Components\Sign\IRegisterControlFactory @inject */
	public $iRegisterControlFactory;

	/** @var \App\Components\Sign\IMergeControlFactory @inject */
	public $iMergeControlFactory;

	/** @var \App\Model\Storage\RegistrationStorage @inject */
	public $registration;

	/** @var \App\Model\Facade\Registration @inject */
	public $registrationFacade;

	
	protected function startup()
	{
		parent::startup();

		// Logged user redirect away
		if ($this->user->isLoggedIn()) {
			$this->flashMessage('You have been already signed in.', 'warning'); // ToDo: Delete, 'cos showing after redirection throught this presenter, maybe.
			$this->redirect(':Admin:Dashboard:');
		}
	}

	public function actionDefault()
	{
		$this->redirect("in");
	}

	public function actionIn()
	{
		$this->registration->wipe();
	}

	public function actionLostPassword()
	{
		$this->flashMessage("Not implemented yet", "warning");
		$this->redirect("in");
	}

	public function actionRegister()
	{
		// Check if is user in registration process
//		$this->checkInProcess();

		$this->template->bool = $this->registration->isOauth();
	}

	public function actionMerge()
	{
		// Check if is user in merging process
//		$this->checkInProcess();
	}

	public function actionVerify($code)
	{
		
	}

	private function checkInProcess()
	{
		if (!$this->registration->isOauth()) {
			$this->redirect(':Front:Sign:in');
		}
	}

// <editor-fold defaultstate="collapsed" desc="Components">

	/** @return \App\Components\SignInControl */
	protected function createComponentSignIn()
	{
		return $this->iSignInControlFactory->create();
	}

	/** @return \App\Components\Sign\RegisterControl */
	protected function createComponentRegister()
	{
		return $this->iRegisterControlFactory->create();
	}
	
// </editor-fold>
	
}
