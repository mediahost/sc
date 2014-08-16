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


    protected function startup() {
        parent::startup();

        // Logged user redirect away
        if ($this->user->isLoggedIn()) {
			$this->flashMessage('You have been already signed in.', 'warning'); // ToDo: Delete, 'cos showing after redirection throught this presenter, maybe.
            $this->redirect(':Admin:Dashboard:');
        }
    }

    public function actionIn()
    {
		$this->registration->wipe();
//		\Tracy\Debugger::barDump($this->registrationStorage->user);
    }

	public function actionRegister()
	{
		// Check if is user in registration process
//		$this->checkInProcess();
		
		dump($this->registration->section);
		dump($this->registration->session->hasSection('registration'));
		
		
		$this->template->bool = $this->registration->isOauth();
//
//		$this->template->newUser = $this->registration->getUser();
//		$this->template->data = $this->registration->data;
//		$this->template->defaults = $this->registration->defaults;
	}

	public function actionMerge()
	{
		// Check if is user in merging process
//		$this->checkInProcess();
		
	}

	private function checkInProcess()
	{
		if (!$this->registration->isOauth()) {
			$this->redirect(':Front:Sign:in');
		}
			
	}

// <editor-fold defaultstate="collapsed" desc="Components">

	/** @return \App\Components\SignInControl */
    protected function createComponentSignIn() {
        return $this->iSignInControlFactory->create();
    }

	/** @return \App\Components\Sign\RegisterControl */
	protected function createComponentRegister() {
        return $this->iRegisterControlFactory->create();
    }

	/** @return \App\Components\Sign\MergeControl */
	protected function createComponentMerge() {
        return $this->iMergeControlFactory->create();
    }
	
// </editor-fold>
}
