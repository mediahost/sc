<?php

namespace App\Listeners;

use App\Components\Auth\Facebook;
use App\Components\Auth\Linkedin;
use App\Components\Auth\Twitter;
use App\Extensions\Settings\SettingsStorage;
use App\Mail\Messages\ICreateRegistrationMessageFactory;
use App\Mail\Messages\IVerificationMessageFactory;
use App\Model\Entity\User;
use App\Model\Facade\RoleFacade;
use App\Model\Facade\UserFacade;
use App\Model\Storage\SignUpStorage;
use Kdyby\Doctrine\EntityManager;
use Kdyby\Events\Subscriber;
use Kdyby\Translation\Translator;
use Nette\Application\Application;
use Nette\Application\UI\Control;
use Nette\Application\UI\Presenter;
use Nette\Object;
use Tracy\Debugger;

class SignListener extends Object implements Subscriber
{

	const REDIRECT_AFTER_LOGIN = ':App:Dashboard:';
	const REDIRECT_AFTER_REGISTER = ':App:CompleteAccount:';
	const REDIRECT_SIGN_IN_PAGE = ':Front:Sign:in';
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

	/** @var Application @inject */
	public $application;

	/** @var SettingsStorage @inject */
	public $settingsStorage;

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
	 */
	public function onStartup(Control $control, User $user, $rememberMe = FALSE)
	{
		if ($control instanceof Facebook || $control instanceof Linkedin || $control instanceof Twitter) {
			$this->userFacade->importSocialData($user);
		}
		if ($user->id) {
			$presenter = $this->application->presenter;
			if ($presenter->name == 'Front:Sign' && $presenter->action == 'up') {
				$this->onRegistered($control->presenter, $user);
			} else {
				$this->onSuccess($control->presenter, $user, $rememberMe);
			}
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
		// nepodporuje automatické joinování účtů (nebylo v aplikaci požadováno)
		if (!$existedUser) {
			$this->verify($control, $user);
		} else {
			$message = $this->translator->translate('%mail% is already registered.', ['mail' => $user->mail]);
			$control->presenter->flashMessage($message);
			$control->presenter->redirect(self::REDIRECT_SIGN_IN_PAGE, ['role' => $this->session->redirectRole]);
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
		$role = $this->roleFacade->findByName($this->session->getRole(TRUE));
		$user->addRole($role);

		$userRepo = $this->em->getRepository(User::getClassName());
		$this->userFacade->setVerification($user);
		$userRepo->save($user);

		$this->session->remove();

		// Send verification e-mail
		$message = $this->verificationMessage->create();
		$message->addParameter('link', $this->application->presenter->link('//:Front:Sign:verify', $user->verificationToken));
		$message->addTo($user->mail);
		$message->send();

		$messageText = $this->translator->translate('We have sent you a verification e-mail. Please check your inbox!');
		$control->presenter->user->login($user);
		$control->presenter->flashMessage($messageText, 'success');
		$control->presenter->redirect(self::REDIRECT_AFTER_REGISTER);
	}

	/**
	 * After recovery password
	 * @param Presenter $presenter
	 * @param User $user
	 */
	public function onRecovery(Presenter $presenter, User $user)
	{
		$message = $this->translator->translate('We have sent you a verification e-mail. Please check your inbox!');
		$presenter->flashMessage($message, 'success');
		$this->onSuccess($presenter, $user);
	}

	/**
	 * After create account
	 * @param Presenter $presenter
	 * @param User $user
	 */
	public function onCreate(Presenter $presenter, User $user)
	{
		$message = $this->createRegistrationMessage->create();
		$message->addTo($user->mail);
		$message->send();

		$messageText = $this->translator->translate('Your account has been seccessfully created.');
		$presenter->flashMessage($messageText, 'success');
		$this->onSuccess($presenter, $user);
	}

	/**
	 * After user is registered
	 * @param Presenter $presenter
	 * @param User $user
	 */
	public function onRegistered(Presenter $presenter, User $user)
	{
		$message = $this->translator->translate('User \'%mail%\' is already registered.', [
			'mail' => $user->mail,
		]);
		$presenter->flashMessage($message, 'info');
		$presenter->redirect(self::REDIRECT_SIGN_IN_PAGE);
	}

	/**
	 * Only login and redirect to app
	 * @param Presenter $presenter
	 * @param User $user
	 * @param bool $rememberMe
	 */
	public function onSuccess(Presenter $presenter, User $user, $rememberMe = FALSE)
	{
		$this->session->remove();

		if ($rememberMe) {
			$presenter->user->setExpiration($this->settingsStorage->expiration->remember, FALSE);
		} else {
			$presenter->user->setExpiration($this->settingsStorage->expiration->notRemember, TRUE);
		}

		$presenter->user->login($user);
		$message = $this->translator->translate('You are logged in.');
		$presenter->flashMessage($message, 'success');

		$presenter->restoreRequest($presenter->backlink);
		$presenter->redirect(self::REDIRECT_AFTER_LOGIN);
	}

}
