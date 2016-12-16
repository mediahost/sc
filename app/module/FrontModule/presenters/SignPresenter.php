<?php

namespace App\FrontModule\Presenters;

use App\Components\Auth;
use App\Mail\Messages\IForgottenMessageFactory;
use App\Model\Entity\User;
use App\Model\Facade;
use App\Model\Storage;

class SignPresenter extends BasePresenter
{

	const ROLE_CANDIDATE = 'candidate';
	const ROLE_COMPANY = 'company';
	const ROLE_DEFAULT = self::ROLE_CANDIDATE;
	const REDIRECT_AFTER_LOG = ':App:Dashboard:';
	const REDIRECT_NOT_LOGGED = ':Front:Sign:in';
	const REDIRECT_IS_LOGGED = ':App:Dashboard:';

	// <editor-fold desc="events">

	/** @var array */
	public $onVerify = [];

	/** @var array */
	public $onAccess = [];

	// </editor-fold>
	// <editor-fold desc="Injects">

	/** @var Auth\IFacebookFactory @inject */
	public $iFacebookFactory;

	/** @var Auth\IForgottenFactory @inject */
	public $iForgottenFactory;

	/** @var Auth\IRecoveryFactory @inject */
	public $iRecoveryFactory;

	/** @var Auth\IRequiredFactory @inject */
	public $iRequiredFactory;

	/** @var Auth\ISignInFactory @inject */
	public $iSignInFactory;

	/** @var Auth\ISignUpFactory @inject */
	public $iSignUpFactory;

	/** @var Auth\ITwitterFactory @inject */
	public $iTwitterFactory;

	/** @var Storage\SignUpStorage @inject */
	public $session;

	/** @var Facade\UserFacade @inject */
	public $userFacade;

	/** @var Facade\RoleFacade @inject */
	public $roleFacade;

	/** @var IForgottenMessageFactory @inject */
	public $forgottenMessage;

	// </editor-fold>

	protected function startup()
	{
		$this->isLoggedIn();
		parent::startup();
	}

	protected function beforeRender()
	{
		$this->setLayout('suprEasy');
		parent::beforeRender();
		$this->template->roleCandidate = self::ROLE_CANDIDATE;
		$this->template->roleCompany = self::ROLE_COMPANY;
	}

	/** Get valid role name; If isn't valid then return default role */
	private function getValidRole($role)
	{
		switch ($role) {
			case self::ROLE_COMPANY:
			case self::ROLE_CANDIDATE:
				break;
			default:
				$role = self::ROLE_DEFAULT;
				break;
		}
		return $role;
	}

	// <editor-fold desc="Actions & renders">

	public function actionIn($role = self::ROLE_DEFAULT)
	{
		$this->session->wipe();
		$this->session->role = $this->getValidRole($role);

		$this->template->role = $this->session->role;
	}

	public function actionUp($role = self::ROLE_DEFAULT)
	{
		$this->session->wipe();

		if ($role === self::ROLE_CANDIDATE) {
			$this->session->role = $this->getValidRole($role);
			$this['signUp']->setRegisterCandidate();
		} else if ($role === self::ROLE_COMPANY) {
			$this->redirect('this', ['role' => self::ROLE_CANDIDATE]);
		}

		$this->template->role = $this->session->role;
	}

	public function renderUpRequired()
	{
		$this->template->role = $this->session->role;
	}

	public function actionVerify($token)
	{
		$user = $this->userFacade->findByVerificationToken($token);
		if ($user) {
			$user->verificated = TRUE;
			$userRepo = $this->em->getRepository(User::getClassName());
			$userRepo->save($user);
			$message = $this->translator->translate('Your e-mail has been seccessfully verified!');
			$this->flashMessage($message, 'success');
			$this->onVerify($this, $user);
		} else {
			$message = $this->translator->translate('Verification token is incorrect.');
			$this->flashMessage($message, 'warning');
			$this->redirect('in');
		}
	}

	public function actionRecovery($token)
	{
		$this['recovery']->setToken($token);
	}

	public function actionAccess($token)
	{
		$user = $this->userFacade->findByAccessToken($token);
		if ($user) {
			$this->onAccess($this, $user);
		} else {
			$message = $this->translator->translate('Access token is incorrect.');
			$this->flashMessage($message, 'warning');
			$this->redirect('in');
		}
	}

	// </editor-fold>

	/** Redirect logged to certain destination.*/
	private function isLoggedIn($redirect = TRUE)
	{
		$isLogged = $this->user->isLoggedIn();
		$allowedActions = [
			'verify',
			'access',
		];
		if (!in_array($this->action, $allowedActions) && $isLogged && $redirect) {
			$this->redirect(self::REDIRECT_IS_LOGGED);
		}
		return $isLogged;
	}

	// <editor-fold desc="controls">

	/** @return Auth\Forgotten */
	protected function createComponentForgotten()
	{
		$control = $this->iForgottenFactory->create();
		$control->onSuccess[] = function (User $user) {

			// Send e-mail with recovery link
			$message = $this->forgottenMessage->create();
			$message->addParameter('link', $this->link('//:Front:Sign:recovery', $user->recoveryToken));
			$message->addTo($user->mail);
			$message->send();

			$message = $this->translator->translate('Recovery link has been sent to your mail.');
			$this->flashMessage($message);
			$this->redirect(':Front:Sign:in');
		};
		$control->onMissingUser[] = function ($mail) {
			$message = $this->translator->translate('We do not register any user with mail \'%mail%\'.', ['mail' => $mail]);
			$this->flashMessage($message, 'warning');
			$this->redirect(':Front:Sign:lostPassword');
		};
		return $control;
	}

	/** @return Auth\Recovery */
	protected function createComponentRecovery()
	{
		$control = $this->iRecoveryFactory->create();
		$control->onFailToken[] = function () {
			$message = 'Token to recovery your password is no longer active. Please request new one.';
			$this->flashMessage($message, 'info');
			$this->redirect(':Front:Sign:lostPassword');
		};
		return $control;
	}

	/** @return Auth\Required */
	protected function createComponentRequired()
	{
		return $this->iRequiredFactory->create();
	}

	/** @return Auth\SignIn */
	protected function createComponentSignIn()
	{
		return $this->iSignInFactory->create();
	}

	/** @return Auth\SignUp */
	protected function createComponentSignUp()
	{
		return $this->iSignUpFactory->create();
	}

	// </editor-fold>
}
