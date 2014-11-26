<?php

namespace App\Listeners;

use App\Mail\Messages\CreateRegistrationMessage;
use App\Mail\Messages\VerificationMessage;
use App\Model\Entity\Role;
use App\Model\Entity\User;
use App\Model\Facade\RoleFacade;
use App\Model\Facade\UserFacade;
use App\Model\Storage\SettingsStorage;
use App\Model\Storage\SignUpStorage;
use App\TaggedString;
use Kdyby\Doctrine\EntityManager;
use Kdyby\Events\Subscriber;
use Nette\Application\Application;
use Nette\Application\UI\Control;
use Nette\Application\UI\Presenter;
use Nette\Latte\Engine;
use Nette\Mail\IMailer;
use Nette\Object;
use Nette\Security\Identity;

class SignListener extends Object implements Subscriber
{

	const REDIRECT_AFTER_LOGIN = ':App:Dashboard:';
	const REDIRECT_SIGNIN_PAGE = ':Front:Sign:in';
	const REDIRECT_SIGN_UP_REQUIRED = ':Front:Sign:upRequired';

	// <editor-fold defaultstate="collapsed" desc="variables">

	/** @var SignUpStorage @inject */
	public $session;

	/** @var EntityManager @inject */
	public $em;

	/** @var UserFacade @inject */
	public $userFacade;

	/** @var RoleFacade @inject */
	public $roleFacade;

	/** @var IMailer @inject */
	public $mailer;

	/** @var Application @inject */
	public $application;

	/** @var SettingsStorage @inject */
	public $settings;

	// </editor-fold>

	public function __construct(Application $application)
	{
		$this->application = $application->presenter;
	}

	public function getSubscribedEvents()
	{
		return array(
			'App\Components\Auth\FacebookControl::onSuccess' => 'onStartup',
			'App\Components\Auth\SignUpControl::onSuccess' => 'onStartup',
			'App\Components\Auth\TwitterControl::onSuccess' => 'onStartup',
			'App\Components\Auth\RequiredControl::onSuccess' => 'onRequiredSuccess',
			'App\Components\Auth\SignInControl::onSuccess' => 'onSuccess',
			'App\FrontModule\Presenters\SignPresenter::onVerify' => 'onCreate',
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
		\Tracy\Debugger::barDump($rememberMe, 'onSuccess');
		if ($user->id) {
			$this->onSuccess($control->presenter, $user, $rememberMe);
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
			$message = new TaggedString('<%mail%> is already registered.', ['mail' => $user->mail]);
			$control->presenter->flashMessage($message);
			$control->presenter->redirect(self::REDIRECT_SIGNIN_PAGE);
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
		if ($this->session->isVerified()) { // verifikovaná metoda
			$signedRole = $this->roleFacade->findByName(Role::ROLE_SIGNED);
			$user->addRole($signedRole);
			$savedUser = $this->em->getDao(User::getClassName())->save($user);
			$this->onCreate($control->presenter, $savedUser);
		} else {
			$registration = $this->userFacade->createRegistration($user);
			$this->session->remove();

			// Send verification e-mail
			$latte = new Engine;
			$params = ['link' => $this->application->presenter->link('//:Front:Sign:verify', $registration->verificationToken)];
			$message = new VerificationMessage();
			$message->addTo($user->mail)
					->setHtmlBody($latte->renderToString($message->getPath(), $params));

			$this->mailer->send($message);

			$control->presenter->flashMessage('We have sent you a verification e-mail. Please check your inbox!', 'success');
			$control->presenter->redirect(self::REDIRECT_SIGNIN_PAGE);
		}
	}

	/**
	 * After create account
	 * @param Presenter $presenter
	 * @param User $user
	 */
	public function onCreate(Presenter $presenter, User $user)
	{
		$latte = new Engine;
		$message = new CreateRegistrationMessage();
		$message->addTo($user->mail)
				->setHtmlBody($latte->renderToString($message->getPath()));

		$this->mailer->send($message);

		$presenter->flashMessage('Your account has been seccessfully created.', 'success');
		$this->onSuccess($presenter, $user);
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
			$presenter->user->setExpiration($this->settings->expiration->remember, FALSE);
		} else {
			$presenter->user->setExpiration($this->settings->expiration->notRemember, TRUE);
		}

		$presenter->user->login(new Identity($user->id, $user->getRolesPairs(), $user->toArray()));
		$presenter->flashMessage('You are logged in.', 'success');

		$presenter->restoreRequest($presenter->backlink);
		$presenter->redirect(self::REDIRECT_AFTER_LOGIN);
	}

}
