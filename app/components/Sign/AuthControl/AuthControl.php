<?php

namespace App\Components\Sign;

/** Nette */
use Nette\Application\UI,
	Nette\Security\Identity,
	Nette\Utils;
		
/** Application */
use App\Components,
	App\Model\Storage,
	App\Model\Facade,
	App\Model\Entity;

/** Facebook */
use Kdyby\Facebook\Facebook,
	Kdyby\Facebook\Dialog\LoginDialog,
	Kdyby\Facebook\FacebookApiException;

/** Twitter */
use Netrium\Addons\Twitter\Authenticator as Twitter,
	Netrium\Addons\Twitter\AuthenticationException as TwitterException;


/**
 * AuthControl provides login or registration via AOuth and aplication.
 *
 * @author Martin Šifra <me@martinsifra.cz>
 */
class AuthControl extends Components\BaseControl
{

	/** @var Storage\RegistrationStorage @inject */
	public $storage;

	/** @var Facade\RegistrationFacade @inject */
	public $facade;

	/** @var \Nette\Mail\IMailer @inject */
	public $mailer;

	/** @var Facebook @inject */
	public $facebook;

	/** @var Twitter @inject */
	public $twitter;

	/** @var Storage\MessageStorage @inject */
	public $messages;


	public function renderRegistration()
	{
		$template = $this->template;
		$template->storage = $this->storage;
		$template->setFile(__DIR__ . '/registration.latte');
		$template->render();
	}

	public function renderIcons()
	{
		$template = $this->template;
		$template->setFile(__DIR__ . '/icons.latte');
		$template->render();
	}

	/**
	 * Register form factory.
	 * @return UI\Form
	 */
	protected function createComponentRegisterForm()
	{

		$form = new UI\Form();
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
	 * Callback for process reg. form data.
	 * @param UI\Form $form
	 * @param Utils\ArrayHash $values
	 */
	public function registerFormSucceeded(UI\Form $form, $values)
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
					->setPassword($values->reg_password);

			$this->registerTemporarily($reg);
		}
	}

	/** 
	 * Component that process AOuth via Facebook.
	 * @return LoginDialog
	 */
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
	 * Handle processing Twitter OAuth.
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
	 * @param Entity\User $user
	 * @throws \Nette\Application\AbortException
	 */
	private function login(Entity\User $user)
	{
		$this->presenter->user->login(new Identity($user->id, $user->getRolesPairs(), $user->toArray()));
		$this->presenter->flashMessage('You have been successfully logged in!', 'success');
		$this->presenter->redirect(':Admin:Dashboard:');
	}

	/**
	 * Provide temorary registration.
	 * @param Entity\Registration $registration
	 * @throws \Nette\Application\AbortException
	 */
	private function registerTemporarily(Entity\Registration $registration)
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
	 * @return Entity\User
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
