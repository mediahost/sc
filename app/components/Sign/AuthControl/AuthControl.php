<?php

namespace App\components\Sign;

use App\Components\Control,
	Nette\Application\UI\Form,
	GettextTranslator\Gettext as Translator,
	App\Model\Storage\RegistrationStorage as Storage,
	App\Model\Facade\RegistrationFacade as Facade,
	Nette\Security\Identity,
	Nette\Mail\IMailer,
	App\Model\Storage\MessageStorage as Messages;
use Kdyby\Facebook\Facebook,
	Kdyby\Facebook\Dialog\LoginDialog,
	Kdyby\Facebook\FacebookApiException;
use Netrium\Addons\Twitter\Authenticator as Twitter,
	Netrium\Addons\Twitter\AuthenticationException as TwitterException;
use App\Model\Entity,
	App\Model\Entity\Auth,
	App\Model\Entity\User,
	App\Model\Entity\Registration;

/**
 * AuthControl provides login or registration via AOuth
 *
 * @author Martin Šifra <me@martinsifra.cz>
 */
class AuthControl extends Control
{

	/** @var Storage */
	private $storage;

	/** @var Facade */
	private $facade;

	/** @var IMailer */
	private $mailer;

	/** @var Facebook */
	private $facebook;

	/** @var Twitter */
	private $twitter;

	/** @var Messages */
	private $messages;

	public function __construct(Translator $translator, Facade $facade, Storage $storage, IMailer $mailer, Messages $messages, Facebook $facebook, Twitter $twitter)
	{
		parent::__construct($translator);
		$this->storage = $storage;
		$this->facade = $facade;
		$this->mailer = $mailer;
		$this->messages = $messages;
		$this->facebook = $facebook;
		$this->twitter = $twitter;
	}

	public function renderRegistration()
	{
		$template = $this->getTemplate();
		$template->storage = $this->storage;
		$template->setFile(__DIR__ . '/registration.latte');
		$template->render();
	}

	public function renderIcons()
	{
		$template = $this->getTemplate();
		$template->setFile(__DIR__ . '/icons.latte');
		$template->render();
	}

	/**
	 * Register form factory.
	 * @return Nette\Application\UI\Form
	 */
	protected function createComponentRegisterForm()
	{

		$form = new Form();
		$form->setRenderer(new \App\Forms\Renderers\MetronicFormRenderer());

		if ($this->storage->isRequired('name')) {
			$form->addText('reg_name', 'Name')
					->setAttribute('placeholder', 'Full name');
		}

		if ($this->storage->isRequired('email')) {
			$form->addText('reg_email', 'E-mail')
					->setAttribute('placeholder', 'E-mail')
					->setRequired('Please enter your e-mail')
					->addRule(Form::EMAIL, 'Fill right e-mail format')
					->addRule(function(\Nette\Forms\Controls\TextInput $item) { // Tohle pouze v případě registrace přes aplikaci
						return TRUE; //$this->users->isUnique($item->value);
					}, 'This e-mail is used yet!');
		}

		if (!$this->storage->isOAuth()) {
			$form->addPassword('reg_password', 'Password')
					->setRequired('Please enter your password')
					->setAttribute('placeholder', 'Password');

			$form->addPassword('reg_password_verify', 'Re-type Your Password')
					->setAttribute('placeholder', 'Re-type Your Password')
					->addConditionOn($form['reg_password_verify'], Form::FILLED)
					->addRule(Form::EQUAL, 'Passwords must be equal.', $form['reg_password']);
		}

		$form->setDefaults($this->storage->defaults);
		$form->addSubmit('register', 'Register');
		$form->addSubmit('cancel', 'Back')
						->setValidationScope(FALSE)
				->onClick = $this->registerFormCancel;

		$form->onSuccess[] = $this->registerFormSucceeded;
		return $form;
	}

	public function registerFormCancel(\Nette\Forms\Controls\SubmitButton $button)
	{
		$this->presenter->redirect("Sign:in");
	}

	/**
	 *
	 * @param \Nette\Application\UI\Form $form
	 * @param type $values
	 */
	public function registerFormSucceeded(Form $form, $values)
	{
		// Namapování hodnot z formuláře
		if ($this->storage->isRequired('name')) {
			$this->storage->user->name = $values->reg_name;
		}

		if ($this->storage->isRequired('email')) {
			$this->storage->user->email = $values->reg_email;
		}

		// Data processing
		if ($this->storage->isOAuth()) {
			// Registrace veia OAuth
			if ($this->storage->isVerified()) {
				// Verified e-mail
				$user = $this->mergeOrRegister();
				$this->login($user);
			} else {
				$this->registerTemporarily($this->storage->toRegistration());
			}
		} else {
			// Registration via aplication form
			$reg = $this->storage->toRegistration();
			$reg->setKey($values->reg_email)
					->setSource('app')
					->setHash(\Nette\Security\Passwords::hash($values->reg_password));

			$this->registerTemporarily($reg);
		}
	}

	/** @return LoginDialog */
	protected function createComponentFacebook()
	{
		$dialog = $this->facebook->createDialog('login');

		/** @var LoginDialog $dialog */
		$dialog->onResponse[] = function (LoginDialog $dialog) {
			$this->storage->wipe();

			$fb = $dialog->getFacebook();

			if (!$fb->getUser()) {
				$this->presenter->flashMessage('We are sorry, facebook authentication failed.');
				return;
			}

			try {
				$data = $fb->api('/me');
				$source = 'facebook';

				$this->process($source, $fb->getUser(), $data, $fb->getAccessToken());
			} catch (FacebookApiException $e) {
				\Tracy\Debugger::log($e->getMessage(), 'facebook');

				$this->presenter->flashMessage('We are sorry, facebook authentication failed hard.');
			}
		};

		return $dialog;
	}

	/**
	 *
	 * @throws NS\AuthenticationException
	 */
	public function handleTwitter()
	{
		$this->storage->wipe();

		try {
			$data = $this->twitter->tryAuthenticate();
			$source = 'twitter';

			$this->process($source, $data['user']->id, $data['user'], $data['accessToken']['key']);
		} catch (TwitterException $e) {
			\Tracy\Debugger::log($e->getMessage(), 'twitter');

			throw new \Nette\Security\AuthenticationException('Twitter authentication did not approve');
		}
	}

	/**
	 * Provides login, registration or merge.
	 * @param string $source Source type
	 * @param string $id User external identification
	 * @param string $data Raw data from external source
	 * @param string $token OAuth access token
	 */
	private function process($source, $id, $data, $token = NULL)
	{
		if (!$auth = $this->facade->findByKey($source, $id)) {

			$this->storage->store($source, $data, $token);

			if ($this->storage->isRequired()) {
				$this->presenter->redirect('Sign:Register', $source);
			} else {
				$user = $this->mergeOrRegister();
			}

			$this->storage->wipe();
		} else {
			$user = $auth->user;
			$this->facade->updateAccessToken($auth, $token);
		}

		// Login
		$this->login($user);
	}

	/**
	 * Login user and redirect.
	 * @param User $user
	 * @throws \Nette\Application\AbortException
	 */
	private function login(User $user)
	{
		$this->presenter->user->login(new Identity($user->id, $user->getRolesPairs(), $user->toArray()));
		$this->presenter->flashMessage('You have been successfully logged in!', 'success');
		$this->presenter->redirect(':Admin:Dashboard:');
	}

	/**
	 *
	 * @param Registration $registration
	 * @throws \Nette\Application\AbortException
	 */
	private function registerTemporarily(Registration $registration)
	{
		// Ověření e-mailu
		$registration = $this->facade->registerTemporarily($registration);

		// Odeslat e-mail
		$message = $this->messages->getRegistrationMail($this->createTemplate(), [
			'code' => $registration->verification_token
		]);

		$message->addTo($registration->email);
		$this->mailer->send($message);

		$this->presenter->flashMessage('We have sent you a verification e-mail. Please check your inbox!', 'success');
		$this->presenter->redirect(':Front:Sign:in');
	}

	/**
	 * Choose registration or merging facade methods.
	 * @return User
	 */
	private function mergeOrRegister()
	{
		if (($user = $this->facade->findByEmail($this->storage->user->email))) {
			return $this->facade->merge($user, $this->storage->auth);
		} else {
			return $this->facade->register($this->storage->user, $this->storage->auth);
		}
	}

}

interface IAuthControlFactory
{

	/** @return AuthControl */
	function create();
}
