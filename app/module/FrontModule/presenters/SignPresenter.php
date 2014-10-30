<?php

namespace App\FrontModule\Presenters;

use App\Components\Profile;
use App\Model\Entity\User;
use App\Model\Facade\UserFacade;
use App\Model\Storage\SignUpStorage;

class SignPresenter extends BasePresenter
{
	
	public $onVerify = [];

	/** @var Profile\IAdditionalControlFactory @inject */
	public $iAdditionalControlFactory;

	/** @var Profile\IFacebookControlFactory @inject */
	public $iFacebookControlFactory;

	/** @var Profile\IForgottenControlFactory @inject */
	public $iForgottenControlFactory;

	/** @var Profile\IRecoveryControlFactory @inject */
	public $iRecoveryControlFactory;
	
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
		$this->isLoggedIn(); 
		
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
			$user = new User();
			$user->setMail($signUp->mail)
					->setHash($signUp->hash)
					->setName($signUp->mail)
					->addRole($signUp->role);
			
			if ($signUp->facebookId) {
				$user->facebook->setId($signUp->facebookId)
						->setAccessToken($signUp->facebookAccessToken);
			}
			
			if ($signUp->TwitterId) {
				$user->twitter->setId($signUp->twitterId)
						->setAccessToken($signUp->twitterAccessToken);
			}
			
			$this->onVerify($this, $user);
		} else {
			$this->presenter->flashMessage('Verification code is incorrect.', 'warning');
			$this->redirect('in');
		}
	}
	
	/** @param string $token */
	public function actionRecovery($token)
	{
		$this->isLoggedIn();

		$this['recovery']->setToken($token);
	}

	/**
	 * ToDo: Tohle chce automatiku!
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
	
	/** @return Profile\ForgottenControl*/
	protected function createComponentForgotten()
	{
		return $this->iForgottenControlFactory->create();
	}
	
	/** @return Profile\RecoveryControl*/
	protected function createComponentRecovery()
	{
		return $this->iRecoveryControlFactory->create();
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
