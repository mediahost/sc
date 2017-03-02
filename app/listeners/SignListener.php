<?php

namespace App\Listeners;

use App\Components\Auth\Facebook;
use App\Components\Auth\Linkedin;
use App\Components\Auth\Twitter;
use App\Extensions\Settings\SettingsStorage;
use App\Mail\Messages\ICreateRegistrationMessageFactory;
use App\Mail\Messages\IVerificationMessageFactory;
use App\Model\Entity\Role;
use App\Model\Entity\User;
use App\Model\Facade\RoleFacade;
use App\Model\Facade\UserFacade;
use App\Model\Storage\SignUpStorage;
use Kdyby\Doctrine\EntityManager;
use Kdyby\Events\Subscriber;
use Kdyby\Translation\Translator;
use Nette\Application\UI\Control;
use Nette\Object;

class SignListener extends Object implements Subscriber
{

	const REDIRECT_AFTER_LOGIN = ':App:Dashboard:';
	const REDIRECT_AFTER_REGISTER = ':App:CompleteAccount:';
	const REDIRECT_SIGN_IN_PAGE = ':Front:Sign:in';
	const REDIRECT_SIGN_UP_PAGE = ':Front:Sign:up';
	const REDIRECT_SIGN_UP_REQUIRED = ':Front:Sign:upRequired';

	// <editor-fold desc="variables">

	/** @var SignUpStorage @inject */
	public $session;

	/** @var EntityManager @inject */
	public $em;

	/** @var Translator @inject */
	public $translator;

	/** @var UserFacade @inject */
	public $userFacade;

	/** @var RoleFacade @inject */
	public $roleFacade;

	/** @var ICreateRegistrationMessageFactory @inject */
	public $createRegistrationMessage;

	/** @var IVerificationMessageFactory @inject */
	public $verificationMessage;

	/** @var SettingsStorage @inject */
	public $settingsStorage;

	/** @var bool */
	private $rememberMe = FALSE;

	/** @var string */
	private $redirectUrl;

	/** @var int */
	private $jobApplyId;

	// </editor-fold>

	public function getSubscribedEvents()
	{
		return array(
			'App\Components\Auth\Facebook::onSuccess' => 'onStartup',
			'App\Components\Auth\Linkedin::onSuccess' => 'onStartup',
			'App\Components\Auth\Twitter::onSuccess' => 'onStartup',
			'App\Components\Auth\SignUp::onSuccess' => 'onStartup',
			'App\Components\Auth\Required::onSuccess' => 'onRequiredSuccess',
			'App\Components\Auth\SignIn::onSuccess' => 'onSuccess',
			'App\Components\Auth\Recovery::onSuccess' => 'onRecovery',
			'App\FrontModule\Presenters\SignPresenter::onVerify' => 'onCreate',
			'App\FrontModule\Presenters\SignPresenter::onAccess' => 'onStartup',
		);
	}

	/**
	 * Naslouchá společně všem OAuth metodám a registračnímu formuláři
	 * Pokud uživatel existuje (má ID), pak jej přihlásíme
	 * Pokud neexistuje, pak pokračuje v registraci
	 * @param Control $control
	 * @param User $user
	 * @param bool $rememberMe
	 * @param string $redirectUrl
	 * @param int $jobApplyId
	 */
	public function onStartup(Control $control, User $user, $rememberMe = FALSE, $redirectUrl = NULL, $jobApplyId = NULL)
	{
		$this->rememberMe = $rememberMe;
		$this->redirectUrl = $redirectUrl;
		$this->jobApplyId = $jobApplyId;

		if ($control instanceof Facebook || $control instanceof Linkedin || $control instanceof Twitter) {
			$this->userFacade->importSocialData($user);
		}
		if ($user->id) {
			$this->onSuccess($control, $user);
		} else {
			$this->session->user = $user;
			$this->checkRequire($control, $user);
		}
	}

	/**
	 * Ověřuje, zda jsou vyplněny všechny nutné položky k registraci
	 * @param Control $control
	 * @param User $user
	 */
	public function checkRequire(Control $control, User $user)
	{
		if (!$user->mail) {
			$control->presenter->redirect(self::REDIRECT_SIGN_UP_REQUIRED);
		} else {
			$this->onRequiredSuccess($control, $user);
		}
	}

	/**
	 * Zde jsou již vyplněna všechna data pro registraci
	 * @param Control $control
	 * @param User $user
	 */
	public function onRequiredSuccess(Control $control, User $user)
	{
		$existedUser = $this->userFacade->findByMail($user->mail);
		if (!$existedUser) {
			$this->verify($control, $user);
		} else {
			$this->join($control, $user, $existedUser);
		}
	}

	/**
	 * Pro vefikovanou metodu přímo vytvoří uživatele
	 * Jinak vytvoří registraci
	 * @param Control $control
	 * @param User $user
	 */
	private function verify(Control $control, User $user)
	{
		$userRepo = $this->em->getRepository(User::getClassName());
		$role = $this->roleFacade->findByName(Role::CANDIDATE);
		$user->addRole($role);

		$this->session->remove();

		if ($user->verificated) {
			$savedUser = $userRepo->save($user);
			$this->onCreate($control->presenter, $savedUser);
		} else {
			$this->userFacade->setVerification($user);
			$userRepo->save($user);

			// Send verification e-mail
			$message = $this->verificationMessage->create();
			$message->addParameter('link', $control->presenter->link('//:Front:Sign:verify', $user->verificationToken));
			$message->addTo($user->mail);
			$message->send();

			$messageText = $this->translator->translate('We have sent you a verification e-mail. Please check your inbox!');
			$control->presenter->user->login($user);
			$control->presenter->flashMessage($messageText, 'success');
			$control->presenter->redirect(self::REDIRECT_AFTER_REGISTER);
		}
	}

	/**
	 * Pro verifikované účty provede připojení
	 * @param Control $control
	 * @param User $user
	 * @param User $existedUser
	 */
	private function join(Control $control, User $user, User $existedUser)
	{
		if ($existedUser->verificated) {
			if ($control instanceof Facebook) {
				$existedUser->facebook = $user->facebook;
			} else if ($control instanceof Linkedin) {
				$existedUser->linkedin = $user->linkedin;
			} else if ($control instanceof Twitter) {
				$existedUser->twitter = $user->twitter;
			} else {
				$message = $this->translator->translate('This method is not suppoorted.');
				$control->presenter->flashMessage($message);
				$control->presenter->redirect(self::REDIRECT_SIGN_IN_PAGE);
			}

			$message = $this->translator->translate('Your account was joined with existed account');
			$control->presenter->flashMessage($message, 'success');

			$this->userFacade->importSocialData($existedUser);
			$this->onSuccess($control, $existedUser);
		} else {
			$userRepo = $this->em->getRepository(User::getClassName());
			$this->userFacade->setVerification($existedUser);
			$userRepo->save($existedUser);

			// Send verification e-mail
			$message = $this->verificationMessage->create();
			$message->addParameter('link', $control->presenter->link('//:Front:Sign:verify', $existedUser->verificationToken));
			$message->addTo($existedUser->mail);
			$message->send();

			$message = 'We cannot join your account automatically while you not verify your account. We send you mail with verification link.';
			$message = $this->translator->translate($message);
			$control->presenter->flashMessage($message);
			$control->presenter->redirect(self::REDIRECT_SIGN_IN_PAGE);
		}
	}

	/**
	 * After recovery password
	 * @param Control $control
	 * @param User $user
	 */
	public function onRecovery(Control $control, User $user)
	{
		$message = $this->translator->translate('We have sent you a verification e-mail. Please check your inbox!');
		$control->presenter->flashMessage($message, 'success');
		$this->onSuccess($control, $user);
	}

	/**
	 * After create account
	 * @param Control $control
	 * @param User $user
	 */
	public function onCreate(Control $control, User $user)
	{
		$message = $this->createRegistrationMessage->create();
		$message->addTo($user->mail);
		$message->send();

		$messageText = $this->translator->translate('Your account has been seccessfully created.');
		$control->presenter->flashMessage($messageText, 'success');
		$this->onSuccess($control, $user);
	}

	/**
	 * Only login and redirect to app
	 * @param Control $control
	 * @param User $user
	 * @param bool $rememberMe
	 * @param string|NULL $redirectUrl
	 * @param int|NULL $jobApplyId
	 */
	public function onSuccess(Control $control, User $user)
	{
		$this->session->remove();

		if ($this->rememberMe) {
			$control->presenter->user->setExpiration($this->settingsStorage->expiration->remember, FALSE);
		} else {
			$control->presenter->user->setExpiration($this->settingsStorage->expiration->notRemember, TRUE);
		}

		$control->presenter->user->login($user);
		$message = $this->translator->translate('You are logged in.');
		$control->presenter->flashMessage($message, 'success');

		// redirections
		if ($this->jobApplyId) {
			$control->presenter->redirect(":App:Job:view", [
				'id' => $this->jobApplyId,
				'jobId' => $this->jobApplyId,
				'redirectUrl' => $this->redirectUrl,
				'do' => 'apply',
			]);
		}
		if ($this->redirectUrl) {
			$control->presenter->redirectUrl($this->redirectUrl);
		}
		$control->presenter->restoreRequest($control->presenter->backlink, FALSE);
		$control->presenter->redirect(self::REDIRECT_AFTER_LOGIN);
	}

}
