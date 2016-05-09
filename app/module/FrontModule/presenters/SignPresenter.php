<?php

namespace App\FrontModule\Presenters;

use App\Components\Auth;
use App\Mail\Messages\IForgottenMessageFactory;
use App\Model\Entity\Role;
use App\Model\Entity\User;
use App\Model\Facade;
use App\Model\Storage;
use App\TaggedString;
use Tracy\Debugger;

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

	// </editor-fold>
	// <editor-fold desc="Injects">

	/** @var Auth\IFacebookControlFactory @inject */
	public $iFacebookControlFactory;

	/** @var Auth\IForgottenControlFactory @inject */
	public $iForgottenControlFactory;

	/** @var Auth\IRecoveryControlFactory @inject */
	public $iRecoveryControlFactory;

	/** @var Auth\IRequiredControlFactory @inject */
	public $iRequiredControlFactory;

	/** @var Auth\ISignInControlFactory @inject */
	public $iSignInControlFactory;

	/** @var Auth\ISignUpControlFactory @inject */
	public $iSignUpControlFactory;

	/** @var Auth\ITwitterControlFactory @inject */
	public $iTwitterControlFactory;

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
		$this->setLayout('supr');
		parent::beforeRender();
		$this->template->roleCandidate = self::ROLE_CANDIDATE;
		$this->template->roleCompany = self::ROLE_COMPANY;
	}

	/**
	 * Get valid role name; If isn't valid then return default role
	 * @param type $role
	 * @return type
	 */
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
		$this->session->role = $this->getValidRole($role);

		$this->template->role = $this->session->role;
	}

	public function renderUpRequired()
	{
		$this->template->role = $this->session->role;
	}

	/** @param string $token */
	public function actionVerify($token)
	{
		$user = $this->userFacade->findByVerificationToken($token);
		if ($user) {
			$user->verificated = TRUE;
			$userRepo = $this->em->getRepository(User::getClassName());
			$userRepo->save($user);
			$this->flashMessage('Your e-mail has been seccessfully verified!', 'success');
			$this->onVerify($this, $user);
		} else {
			$this->flashMessage('Verification token is incorrect.', 'warning');
			$this->redirect('in');
		}
	}

	/** @param string $token */
	public function actionRecovery($token)
	{
		$this['recovery']->setToken($token);
	}

	// </editor-fold>

	/**
	 * Redirect logged to certain destination.
	 * @param bool $redirect
	 * @return bool
	 */
	private function isLoggedIn($redirect = TRUE)
	{
		$isLogged = $this->user->isLoggedIn();
		if ($this->action !== 'verify' && $isLogged && $redirect) {
			$this->redirect(self::REDIRECT_IS_LOGGED);
		}
		return $isLogged;
	}

	// <editor-fold desc="controls">

	/** @return Auth\ForgottenControl */
	protected function createComponentForgotten()
	{
		$control = $this->iForgottenControlFactory->create();
		$control->onSuccess[] = function (User $user) {

			// Send e-mail with recovery link
			$message = $this->forgottenMessage->create();
			$message->addParameter('link', $this->link('//:Front:Sign:recovery', $user->recoveryToken));
			$message->addTo($user->mail);
			$message->send();

			$this->flashMessage('Recovery link has been sent to your mail.');
			$this->redirect(':Front:Sign:in');
		};
		$control->onMissingUser[] = function ($mail) {
			$message = new TaggedString('We do not register any user with mail \'%s\'.', $mail);
			$this->flashMessage($message, 'warning');
			$this->redirect(':Front:Sign:lostPassword');
		};
		return $control;
	}

	/** @return Auth\RecoveryControl */
	protected function createComponentRecovery()
	{
		$control = $this->iRecoveryControlFactory->create();
		$control->onFailToken[] = function () {
			$this->flashMessage('Token to recovery your password is no longer active. Please request new one.', 'info');
			$this->redirect(':Front:Sign:lostPassword');
		};
		return $control;
	}

	/** @return Auth\RequiredControl */
	protected function createComponentRequired()
	{
		return $this->iRequiredControlFactory->create();
	}

	/** @return Auth\SignInControl */
	protected function createComponentSignIn()
	{
		return $this->iSignInControlFactory->create();
	}

	/** @return Auth\SignUpControl */
	protected function createComponentSignUp()
	{
		return $this->iSignUpControlFactory->create();
	}

	// </editor-fold>
}
