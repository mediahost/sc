<?php

namespace App\FrontModule\Presenters;

use Kdyby\Doctrine\EntityManager,
	Kdyby\Doctrine\EntityDao;

/**
 * Sign in/out presenters.
 */
class SignPresenter extends BasePresenter
{

	/** @var EntityManager @inject */
	public $em;

	/** @var EntityDao */
	private $userDao;

	/** @var \App\Components\Sign\ISignInControlFactory @inject */
	public $iSignInControlFactory;

	/** @var \App\Components\Sign\IForgottenControlFactory @inject */
	public $iForgottenControlFactory;

	/** @var \App\Components\Sign\IRecoveryControlFactory @inject */
	public $iRecoveryControlFactory;

	/** @var \App\Components\Sign\IAuthControlFactory @inject */
	public $iAuthControlFactory;

	/** @var \App\Model\Storage\RegistrationStorage @inject */
	public $registration;

	/** @var \App\Model\Facade\RegistrationFacade @inject */
	public $registrationFacade;

	/** @var \App\Model\Facade\AuthFacade @inject */
	public $authFacade;

	protected function startup()
	{
		parent::startup();
		$this->userDao = $this->em->getDao(\App\Model\Entity\User::getClassName());
	}
	
	private function isLoggedIn($redirect = TRUE)
	{
		$isLogged = $this->user->isLoggedIn();
		if ($isLogged && $redirect) {
			$this->redirect(':Admin:Dashboard:');
		}
		return $isLogged;
	}

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
		
		$message = 'Token to recovery your password is no longer active. Please request new.';
		
		if ($token !== NULL) {
			$auth = $this->authFacade->findByValidToken($token);

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

		if (!$this->registration->isSource($source)) {
			$this->redirect('in');
		} else {
			if ($source === NULL) {
				$this->registration->wipe();
			}
		}
	}

	/**
	 * User account verification.
	 * @param string $code
	 */
	public function actionVerify($code)
	{
		$this->isLoggedIn();
		
		$user = $this->registrationFacade->verify($code);
		if ($user) {
			$this->presenter->user->login(new \Nette\Security\Identity($user->id, $user->getRolesPairs(), $user->toArray()));
			$this->presenter->flashMessage('You have been successfully logged in!', 'success');
			$this->presenter->redirect(':Admin:Dashboard:');
		} else {
			$this->presenter->flashMessage('Verification code is incorrect.', 'warning');
			$this->redirect('in');
		}
	}

// <editor-fold defaultstate="collapsed" desc="Components">

	/** @return \App\Components\SignInControl */
	protected function createComponentSignIn()
	{
		return $this->iSignInControlFactory->create();
	}

	/** @return \App\components\Sign\AuthControl */
	protected function createComponentAuth()
	{
		return $this->iAuthControlFactory->create();
	}

	/** @return \App\components\Sign\ForgottenControl */
	protected function createComponentForgotten()
	{
		return $this->iForgottenControlFactory->create();
	}

	/** @return \App\components\Sign\RecoveryControl */
	protected function createComponentRecovery()
	{
		return $this->iRecoveryControlFactory->create();
	}

// </editor-fold>
}
