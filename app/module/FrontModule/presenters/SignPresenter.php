<?php

namespace App\FrontModule\Presenters;

/** Nette */
use Nette\Security\Identity;
/** Application */
use App\Components\Sign,
	App\Model\Storage,
	App\Model\Storage\RegistrationStorage,
	App\Model\Facade;

/**
 * Sign in, sign out and registration presenters.
 */
class SignPresenter extends BasePresenter
{

	// <editor-fold defaultstate="collapsed" desc="constants & variables">
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

	/** @var Facade\RoleFacade @inject */
	public $roleFacade;

	/** @var \App\Components\Profile\ISignControlFactory @inject */
	public $iSignControlFactory;
	
	// </editor-fold>
	// <editor-fold defaultstate="collapsed" desc="actions">
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
	public function actionRegistration($role = \App\Model\Entity\Role::ROLE_CANDIDATE, $source = RegistrationStorage::SOURCE_APP)
	{
		$this->isLoggedIn();

		if (!$this->registrationStorage->isSource($source) || !($role = $this->roleFacade->isRegistratable($role))) {
			$this->redirect('in');
		} else {
			if ($source === RegistrationStorage::SOURCE_APP) {
				$this->registrationStorage->wipe();
			}

			$this['auth']->setRole($role);
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
			$this->presenter->redirect(':App:Dashboard:');
		} else {
			$this->presenter->flashMessage('Verification code is incorrect.', 'warning');
			$this->redirect('in');
		}
	}

	// </editor-fold>
	// <editor-fold defaultstate="collapsed" desc="private functions">

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

	// </editor-fold>
	// <editor-fold defaultstate="collapsed" desc="components">

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
	
	/** @return \App\Components\Profile\SignControl */
	protected function createComponentSign()
	{
		return $this->iSignControlFactory->create();
	}

	// </editor-fold>
}
