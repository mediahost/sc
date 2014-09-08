<?php

namespace App\FrontModule\Presenters;

/** Nette */
use Nette\Security\Identity;

/** Application */
use App\Components\Sign,
	App\Model\Storage,
	App\Model\Facade;


/**
 * Sign in, sign out and registration presenters.
 */
class SignPresenter extends BasePresenter
{

	/** @var Sign\ISignInControlFactory @inject */
	public $iSignInControlFactory;

	/** @var Sign\IForgottenControlFactory @inject */
	public $iForgottenControlFactory;

	/** @var Sign\IRecoveryControlFactory @inject */
	public $iRecoveryControlFactory;

	/** @var Sign\IAuthControlFactory @inject */
	public $iAuthControlFactory;

	/** @var Storage\RegistrationStorage @inject */
	public $registrationStorage;

	/** @var Facade\RegistrationFacade @inject */
	public $registrationFacade;

	/** @var Facade\AuthFacade @inject */
	public $authFacade;
	

	/**
	 * Default is SIGN IN.
	 */
	public function actionDefault()
	{
		$this->redirect('in');
	}

	/**
	 * Sign IN.
	 */
	public function actionIn()
	{
		$this->isLoggedIn();
	}
	
	/**
	 * Sign OUT.
	 */
	public function actionOut()
	{
		$this->user->logout();
		$this->redirect(':Front:Sign:in');
	}

	/**
	 * Lost Password.
	 */
	public function actionLostPassword()
	{
		$this->isLoggedIn();
	}

	/**
	 * Recovery password.
	 * @param string $token
	 */
	public function actionRecovery($token)
	{
		$this->isLoggedIn();
		
		$message = 'Token to recovery your password is no longer active. Please request new one.';
		
		if ($token !== NULL) {
			$auth = $this->authFacade->findByRecoveryToken($token);

			if ($auth !== NULL) {
				$this['recovery']->setAuth($auth);
			} else {
				$this->flashMessage($message, 'info');
				$this->redirect('lostPassword');
			}
		} else {
			$this->flashMessage($message, 'info');
			$this->redirect('lostPassword');
		}
	}

	/**
	 * Registration.
	 * @param string $source
	 */
	public function actionRegistration($source = NULL)
	{
		$this->isLoggedIn();

		if (!$this->registrationStorage->isSource($source)) {
			$this->redirect('in');
		} else {
			if ($source === NULL) {
				$this->registrationStorage->wipe();
			}
		}
	}

	/**
	 * User account verification.
	 * @param string $token
	 */
	public function actionVerify($token)
	{
		$this->isLoggedIn();
		
		$user = $this->registrationFacade->verify($token);
		
		if ($user) {
			$this->presenter->user->login(new Identity($user->id, $user->getRolesPairs(), $user->toArray()));
			$this->presenter->flashMessage('You have been successfully logged in!', 'success');
			$this->presenter->redirect(':Admin:Dashboard:');
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
			$this->redirect(':Admin:Dashboard:');
		}
		return $isLogged;
	}

// <editor-fold defaultstate="collapsed" desc="Components">

	/** @return Sign\SignInControl */
	protected function createComponentSignIn()
	{
		return $this->iSignInControlFactory->create();
	}

	/** @return Sign\AuthControl */
	protected function createComponentAuth()
	{
		return $this->iAuthControlFactory->create();
	}

	/** @return Sign\ForgottenControl */
	protected function createComponentForgotten()
	{
		return $this->iForgottenControlFactory->create();
	}

	/** @return Sign\RecoveryControl */
	protected function createComponentRecovery()
	{
		return $this->iRecoveryControlFactory->create();
	}

// </editor-fold>
}
