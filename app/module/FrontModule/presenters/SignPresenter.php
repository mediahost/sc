<?php

namespace App\FrontModule\Presenters;

use Kdyby\Doctrine\EntityManager,
	Kdyby\Doctrine\EntityDao,
	App\Model\Facade\UserFacade;

/**
 * Sign in/out presenters.
 */
class SignPresenter extends BasePresenter
{

	/** @var EntityManager @inject */
	public $em;

	/** @var EntityDao */
	private $userDao;

	/** @var UserFacade @inject */
	public $userFacade;

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

//		$this->user->logout();
		// Logged user redirect away
		if ($this->user->isLoggedIn()) {
//			$this->flashMessage('You have been already signed in.', 'warning'); // ToDo: Delete, 'cos showing after redirection throught this presenter, maybe.
			$this->redirect(':Admin:Dashboard:');
		}
	}

	/**
	 * Default is SIGN IN
	 */
	public function actionDefault()
	{
		$this->redirect('in');
	}

	/**
	 * Sign IN
	 */
	public function actionIn()
	{
		
	}
	
	/**
	 * Sign OUT
	 */
	public function actionOut()
	{
		$this->user->logout();
		$this->redirect(':Front:Sign:in');
	}

	/**
	 * Lost Password
	 */
	public function actionLostPassword()
	{
		
	}

	/**
	 * @param string $token
	 * @throws Nette\Application\BadRequestException
	 */
	public function actionRecovery($token)
	{
		if ($token !== NULL) {
			$auth = $this->authFacade->findByRecovery($token);

			if ($auth !== NULL) {
				$this['recovery']->setAuth($auth);
			} else {
				$this->flashMessage('Token to recovery your password is no longer active. Please request new.', 'info');
				$this->redirect('forgotten');
			}
		} else {
			throw new \Nette\Application\BadRequestException;
		}
	}

	/**
	 *
	 */
	public function actionRegister($source = NULL)
	{

		if (!$this->registration->isSource($source)) {
			$this->redirect('in');
		} else {
			if ($source === NULL) {
				$this->registration->wipe();
			}
		}

		// Check if is user in registration process
//		$this->checkInProcess();

		$this->template->bool = $this->registration->isOAuth();
	}

	/**
	 *
	 */
	public function actionVerify($code)
	{
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

	private function checkInProcess()
	{
		if (!$this->registration->isOAuth()) {
			$this->redirect(':Front:Sign:in');
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
