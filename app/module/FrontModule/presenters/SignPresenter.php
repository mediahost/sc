<?php

namespace App\FrontModule\Presenters;

use App\Components\Auth\AdditionalControl;
use App\Components\Auth\FacebookControl;
use App\Components\Auth\ForgottenControl;
use App\Components\Auth\IAdditionalControlFactory;
use App\Components\Auth\IFacebookControlFactory;
use App\Components\Auth\IForgottenControlFactory;
use App\Components\Auth\IRecoveryControlFactory;
use App\Components\Auth\IRequiredControlFactory;
use App\Components\Auth\ISignInControlFactory;
use App\Components\Auth\ISignUpControlFactory;
use App\Components\Auth\ISummaryControlFactory;
use App\Components\Auth\ITwitterControlFactory;
use App\Components\Auth\RecoveryControl;
use App\Components\Auth\RequiredControl;
use App\Components\Auth\SignInControl;
use App\Components\Auth\SignUpControl;
use App\Components\Auth\SummaryControl;
use App\Components\Auth\TwitterControl;
use App\Model\Entity\Facebook;
use App\Model\Entity\Role;
use App\Model\Entity\Twitter;
use App\Model\Entity\User;
use App\Model\Entity\UserSettings;
use App\Model\Facade\RoleFacade;
use App\Model\Facade\UserFacade;
use App\Model\Storage\SignUpStorage;

class SignPresenter extends BasePresenter
{

	const ROLE_CANDIDATE = 'candidate';
	const ROLE_COMPANY = 'company';
	const ROLE_DEFAULT = self::ROLE_CANDIDATE;
	const REDIRECT_AFTER_LOG = ':App:Dashboard:';
	const REDIRECT_NOT_LOGGED = ':Front:Sign:in';
	const REDIRECT_IS_LOGGED = ':App:Dashboard:';
	const STEP1 = 'required';
	const STEP2 = 'additional';
	const STEP3 = 'summary';

	// <editor-fold defaultstate="expanded" desc="events">

	/** @var array */
	public $onVerify = [];

	// </editor-fold>
	// <editor-fold defaultstate="collapsed" desc="Injects">

	/** @var IAdditionalControlFactory @inject */
	public $iAdditionalControlFactory;

	/** @var IFacebookControlFactory @inject */
	public $iFacebookControlFactory;

	/** @var IForgottenControlFactory @inject */
	public $iForgottenControlFactory;

	/** @var IRecoveryControlFactory @inject */
	public $iRecoveryControlFactory;

	/** @var IRequiredControlFactory @inject */
	public $iRequiredControlFactory;

	/** @var ISignInControlFactory @inject */
	public $iSignInControlFactory;

	/** @var ISignUpControlFactory @inject */
	public $iSignUpControlFactory;

	/** @var ISummaryControlFactory @inject */
	public $iSummaryControlFactory;

	/** @var ITwitterControlFactory @inject */
	public $iTwitterControlFactory;

	/** @var SignUpStorage @inject */
	public $session;

	/** @var UserFacade @inject */
	public $userFacade;
	
	/** @var RoleFacade @inject */
	public $roleFacade;

	// </editor-fold>

	protected function startup()
	{
		parent::startup();
//		$this->isLoggedIn();
	}

	protected function beforeRender()
	{
		parent::beforeRender();
		$this->template->roleCandidate = self::ROLE_CANDIDATE;
		$this->template->roleCompany = self::ROLE_COMPANY;
	}

	/**
	 * Get valid role name; If isn't valid then return default role
	 * @param type $role
	 * @param type $defaultRole
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

	// <editor-fold defaultstate="expanded" desc="Actions & renders">

	/** @param string $role */
	public function actionIn($role = self::ROLE_DEFAULT)
	{
		$this->session->role = $this->getValidRole($role);
		$this['signIn']->onSuccess[] = function () {
			$this->restoreRequest($this->presenter->backlink);
			$this->redirect(self::REDIRECT_AFTER_LOG);
		};
	}
	
	public function renderIn()
	{
		$this->template->role = $this->session->role;
	}

	/** @param string $role */
	public function actionRegister($role = NULL)
	{
		$this->redirect('up', ['role' => $this->getValidRole($role)]);
	}

	/**
	 * @param string $role
	 * @param string $step
	 */
	public function actionUp($role = NULL, $step = NULL)
	{
		$allowedSteps = [self::STEP1, self::STEP2, self::STEP3];
		if ($step !== NULL && in_array($step, $allowedSteps)) {
			$this->setView('step' . ucfirst($step));
		} else {
			$this->session->role = $this->getValidRole($role);
		}
		// This cannot be in renderUp() because setting other view
		$this->template->user = $this->session->user;
		$this->template->company = $this->session->company;
		$this->template->role = $this->session->role;
	}

	/** @param string $token */
	public function actionVerify($token)
	{
		$signUp = $this->userFacade->findByVerificationToken($token);

		if ($signUp) {
			$user = new User();
			$user->setMail($signUp->mail)
					->setHash($signUp->hash)
					->setName($signUp->mail)
					->addRole($signUp->role);

			if ($signUp->facebookId) {
				$user->facebook = (new Facebook)
						->setId($signUp->facebookId)
						->setAccessToken($signUp->facebookAccessToken);
			}

			if ($signUp->twitterId) {
				$user->twitter = (new Twitter)
						->setId($signUp->twitterId)
						->setAccessToken($signUp->twitterAccessToken);
			}
			
			$user->settings = new UserSettings();
			
			$role = $this->roleFacade->findByName(Role::ROLE_SIGNED);
			$user->addRole($role);
			
			$this->userFacade->verify($user, $token);
			$this->session->verification = TRUE;
			$this->presenter->redirect(':Front:Sign:up', [
				'step' => 'additional'
			]);
		} else {
			$this->presenter->flashMessage('Verification token is incorrect.', 'warning');
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
	 * @param type $redirect
	 * @return bool
	 */
	private function isLoggedIn($redirect = TRUE)
	{
		$isLogged = $this->user->isLoggedIn();
		if ($isLogged && $redirect) {
			$this->redirect(self::REDIRECT_IS_LOGGED);
		}
		return $isLogged;
	}

	// <editor-fold defaultstate="collapsed" desc="controls">

	/** @return AdditionalControl */
	protected function createComponentAdditional()
	{
		return $this->iAdditionalControlFactory->create();
	}

	/** @return FacebookControl */
	protected function createComponentFacebook()
	{
		return $this->iFacebookControlFactory->create();
	}

	/** @return ForgottenControl */
	protected function createComponentForgotten()
	{
		return $this->iForgottenControlFactory->create();
	}

	/** @return RecoveryControl */
	protected function createComponentRecovery()
	{
		return $this->iRecoveryControlFactory->create();
	}

	/** @return RequiredControl */
	protected function createComponentRequired()
	{
		return $this->iRequiredControlFactory->create();
	}

	/** @return SignInControl */
	protected function createComponentSignIn()
	{
		return $this->iSignInControlFactory->create();
	}

	/** @return SignUpControl */
	protected function createComponentSignUp()
	{
		return $this->iSignUpControlFactory->create();
	}

	/** @return SummaryControl */
	protected function createComponentSummary()
	{
		return $this->iSummaryControlFactory->create();
	}

	/** @return TwitterControl */
	protected function createComponentTwitter()
	{
		return $this->iTwitterControlFactory->create();
	}

	// </editor-fold>
}
