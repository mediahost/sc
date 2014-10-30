<?php

namespace App\FrontModule\Presenters;

use App\Components\Profile;
use App\Model\Facade\UserFacade;
use App\Model\Storage\SignUpStorage;
use Nette\Security\Identity;
use Tracy\Debugger;

class NewSignPresenter extends BasePresenter
{

	/** @var Profile\IAdditionalControlFactory @inject */
	public $iAdditionalControlFactory;

	/** @var Profile\IFacebookControlFactory @inject */
	public $iFacebookControlFactory;

	/** @var Profile\IRequiredControlFactory @inject */
	public $iRequiredControlFactory;

	/** @var Profile\ISignInControlFactory @inject */
	public $iSignInControlFactory;

	/** @var Profile\ISignUpControlFactory @inject */
	public $iSignUpControlFactory;

	/** @var Profile\ISummaryControlFactory @inject */
	public $iSummaryControlFactory;
	
	/** @var Profile\ITwitterControlFactory @inject */
	public $iTwitterControlFactory;
	
	/** @var SignUpStorage @inject */
	public $session;
	
	/** @var UserFacade @inject */
	public $userFacade;

	/** @param string $role */
	public function actionIn($role)
	{
		$this->isLoggedIn();
		$this->template->role = $role;

		$this['signIn']->onSuccess[] = function () {
			$this->restoreRequest($this->presenter->backlink);
			$this->redirect(':App:Dashboard:');
		};
	}

	/** @param string $role */
	public function actionUp($role = NULL, $step = NULL) // ToDo: Check ROLE validity!
	{
//		$this->session->wipe();
		$this->isLoggedIn(); // ToDo: Tohle chce automatiku!
		
		Debugger::barDump($this->session->role);
		
		if ($step !== NULL && in_array($step, ['required', 'additional', 'summary'])) {
			$this->setView('step' . ucfirst($step));
		} else {
			$this->session->role = $role;
		}
		
		$this->template->user = $this->session->user;
		$this->template->company = $this->session->company;
		$this->template->role = $this->session->role;
	}

	/** @param string $token */
	public function actionVerify($token)
	{
		$this->isLoggedIn();

		if ($signUp = $this->userFacade->findByVerificationToken($token)) {
			$user = new \App\Model\Entity\User();
			$this->onVerify();
		} else {
			$this->presenter->flashMessage('Verification code is incorrect.', 'warning');
			$this->redirect('in');
		}

	}

	/**
	 * Redirect logged to certain destination.
	 * @param type $redirect
	 * @return bool
	 */
	private function isLoggedIn($redirect = TRUE)
	{
		$isLogged = $this->user->isLoggedIn();
		if ($isLogged && $redirect) {
			$this->redirect(':App:Dashboard:');
		}
		return $isLogged;
	}

	/** @return Profile\AdditionalControl */
	protected function createComponentAdditional()
	{
		return $this->iAdditionalControlFactory->create();
	}
	
	/** @return Profile\FacebookControl */
	protected function createComponentFacebook()
	{
		return $this->iFacebookControlFactory->create();
	}

	/** @return Profile\RequiredControl */
	protected function createComponentRequired()
	{
		return $this->iRequiredControlFactory->create();
	}

	/** @return Profile\SignInControl */
	protected function createComponentSignIn()
	{
		return $this->iSignInControlFactory->create();
	}

	/** @return Profile\SignUpControl */
	protected function createComponentSignUp()
	{
		return $this->iSignUpControlFactory->create();
	}

	/** @return Profile\SummaryControl */
	protected function createComponentSummary()
	{
		return $this->iSummaryControlFactory->create();
	}
	
	/** @return Profile\TwitterControl */
	protected function createComponentTwitter()
	{
		return $this->iTwitterControlFactory->create();
	}

}
