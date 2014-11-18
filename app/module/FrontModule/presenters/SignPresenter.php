<?php

namespace App\FrontModule\Presenters;

use App\Components\Auth;
use App\Model\Entity;
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
	const STEP1 = 'required';
	const STEP2 = 'additional';
	const STEP3 = 'summary';

	// <editor-fold defaultstate="expanded" desc="events">

	/** @var array */
	public $onVerify = [];

	// </editor-fold>
	// <editor-fold defaultstate="collapsed" desc="Injects">

	/** @var Auth\IAdditionalControlFactory @inject */
	public $iAdditionalControlFactory;

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

	/** @var Auth\ISummaryControlFactory @inject */
	public $iSummaryControlFactory;

	/** @var Auth\ITwitterControlFactory @inject */
	public $iTwitterControlFactory;

	/** @var Storage\SignUpStorage @inject */
	public $session;

	/** @var Facade\UserFacade @inject */
	public $userFacade;
	
	/** @var Facade\RoleFacade @inject */
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
			$user = new Entity\User();
			$user->setMail($signUp->mail)
					->setHash($signUp->hash)
					->setName($signUp->mail)
					->addRole($signUp->role);

			if ($signUp->facebookId) {
				$user->facebook = (new Entity\Facebook)
						->setId($signUp->facebookId)
						->setAccessToken($signUp->facebookAccessToken);
			}

			if ($signUp->twitterId) {
				$user->twitter = (new Entity\Twitter)
						->setId($signUp->twitterId)
						->setAccessToken($signUp->twitterAccessToken);
			}
			
			$user->settings = new Entity\UserSettings();
			
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

	/** @return Auth\AdditionalControl */
	protected function createComponentAdditional()
	{
		return $this->iAdditionalControlFactory->create();
	}

	/** @return Auth\FacebookControl */
	protected function createComponentFacebook()
	{
		return $this->iFacebookControlFactory->create();
	}

	/** @return Auth\ForgottenControl */
	protected function createComponentForgotten()
	{
		return $this->iForgottenControlFactory->create();
	}

	/** @return Auth\RecoveryControl */
	protected function createComponentRecovery()
	{
		return $this->iRecoveryControlFactory->create();
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

	/** @return Auth\SummaryControl */
	protected function createComponentSummary()
	{
		return $this->iSummaryControlFactory->create();
	}

	/** @return Auth\TwitterControl */
	protected function createComponentTwitter()
	{
		return $this->iTwitterControlFactory->create();
	}

	// </editor-fold>
}
