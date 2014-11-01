<?php

namespace App\FrontModule\Presenters;

use App\Components\Profile;
use App\Model\Entity\Facebook;
use App\Model\Entity\Twitter;
use App\Model\Entity\User;
use App\Model\Entity\UserSettings;
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

	// </editor-fold>

	protected function startup()
	{
		parent::startup();
		$this->isLoggedIn();
	}

	protected function beforeRender()
	{
		parent::beforeRender();
		$this->template->roleCandidate = self::ROLE_CANDIDATE;
		$this->template->roleCompany = self::ROLE_COMPANY;
	}

	/**
	 * Validate role parameter and redirect when not allowed
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
	public function actionIn()
	{
		$this['signIn']->onSuccess[] = function () {
			$this->restoreRequest($this->presenter->backlink);
			$this->redirect(self::REDIRECT_AFTER_LOG);
		};
	}
	
	/** @param string $role */
	public function renderIn($role = self::ROLE_DEFAULT)
	{
		$this->template->role = $this->getValidRole($role);
	}

	/** @param string $role */
	public function actionUp($role = NULL, $step = NULL)
	{
		$allowedSteps = [self::STEP1, self::STEP2, self::STEP3];
		if ($step !== NULL && in_array($step, $allowedSteps)) {
			$this->setView('step' . ucfirst($step));
		} else {
			$this->session->role = $this->getValidRole($role);
		}
		
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

			$this->userFacade->verify($user, $token);
			$this->onVerify($this, $user);
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
			$this->redirect(self::REDIRECT_NOT_LOGGED);
		}
		return $isLogged;
	}

	// <editor-fold defaultstate="collapsed" desc="controls">

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

	/** @return Profile\ForgottenControl */
	protected function createComponentForgotten()
	{
		return $this->iForgottenControlFactory->create();
	}

	/** @return Profile\RecoveryControl */
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

	// </editor-fold>
}
