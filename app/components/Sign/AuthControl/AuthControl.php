<?php

namespace App\components\Sign;

use	Nette\Application\UI\Control,
	Nette\Application\UI\Form,
	App\Model\Storage\RegistrationStorage as Storage,
	App\Model\Facade\Registration as Facade,
	Nette\Security\Identity,
    Nette\Mail\IMailer,
	App\Model\Storage\MessageStorage as Messages;

use Kdyby\Facebook\Facebook,
	Kdyby\Facebook\Dialog\LoginDialog,
	Kdyby\Facebook\FacebookApiException;

use Netrium\Addons\Twitter\Authenticator as Twitter,
	Netrium\Addons\Twitter\AuthenticationException as TwitterException;

use App\Model\Entity\Auth,
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


	public function __construct(Facade $facade, Storage $storage, IMailer $mailer, Messages $messages, Facebook $facebook, Twitter $twitter)
	{
		parent::__construct();
		$this->storage = $storage;
		$this->facade = $facade;
		$this->mailer = $mailer;
		$this->messages = $messages;
		$this->facebook = $facebook;
		$this->twitter = $twitter;
	}

	/**
	 * Register form factory.
	 * @return Nette\Application\UI\Form
	 */
	protected function createComponentRegisterForm()
	{
		$form = new Form();
		$form->getElementPrototype()->addAttributes(['autocomplete' => 'off']);
		$form->setRenderer(new \App\Forms\Renderers\MetronicFormRenderer());

		if ($this->registration->isRequired('birthdate')) {
			$form->addText('name', 'Name')
					->setRequired('Please enter your username')
					->setAttribute('placeholder', 'Full name');
		}

		if ($this->registration->isRequired('birthdate')) {
			$form->addText('birthdate', 'Birthdate')
					->setRequired('Please enter your username')
					->setAttribute('placeholder', 'Birthdate');
		}

		if ($this->registration->isRequired('email')) {
			$form->addText('email', 'E-mail')
					->setRequired('Please enter your e-mail')
					->setAttribute('placeholder', 'E-mail')
					->setAttribute('autocomplete', 'off')
					->addRule(function(Nette\Forms\Controls\TextInput $item) {
						return $this->users->isUnique($item->value);
					}, 'This e-mail is used yet!');
		}

		if ($this->registration->isOauth()) {
			$form->setDefaults($this->registration->defaults);
		}

		if (!$this->registration->isOauth()) {
			$form->addPassword('password', 'Password')
					->setRequired('Please enter your password')
					->setAttribute('placeholder', 'Password');
		}

		$form->addSubmit('register', 'Register');

		// call method signInFormSucceeded() on success
		$form->onSuccess[] = $this->registerFormSucceeded;
		return $form;
	}

	/**
	 *
	 * @param \Nette\Application\UI\Form $form
	 * @param type $values
	 */
	public function registerFormSucceeded(Form $form, $values)
	{
		// Namapování hodnot z formuláře
		if ($this->registration->isOauth()) {
			// Registrace přes OAuth
			if ($this->registration->user->email !== NULL) {
				// Ověřený e-mail
				$user = $this->registration->user;
				$auth = $this->registration->auth;
				$user->addAuth($auth);
				$user->clearRoles();
				$this->users->addRole($user, ['signed']);
				$this->em->persist($user);
				$this->em->flush();

				$existing = $this->users->findByEmail($this->registration->user->email);

				// Přihlásit
				$this->login($existing);
			} else {
				// Neověřený e-mail
				$this->registration->user->email = $values->email;
				$registration = $this->registration->toRegistration();
				// Ověření mailu
			}

		} else {
			// Registrace přes formulář
			$registration = new Entity\Registration();
			$registration->email = $values->email;
			$registration->key = $values->email;
			$registration->source = 'app';
			$registration->hash = \Nette\Security\Passwords::hash($values->password);
			// Ověření mailu
		}


		if (!isset($this->registration->user->email)) {
			// Ověření e-mailu
			$this->verify($registration);
		}
	}

	/** @return LoginDialog */
	protected function createComponentFacebook()
	{
		$dialog = $this->facebook->createDialog('login');

		/** @var LoginDialog $dialog */
		$dialog->onResponse[] = function (LoginDialog $dialog) {
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
		try {
			$data = $this->twitter->tryAuthenticate();
			$source = 'twitter';

			$this->process($source, $data['user']['id'], $data, $data['accessToken']['key']);

		} catch (TwitterException $e) {
			\Tracy\Debugger::log($e->getMessage(), 'twitter');

			throw new NS\AuthenticationException('Twitter authentication did not approve', self::NOT_APPROVED, $e);
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
		if (!$user = $this->facade->findByKey($source, $id)) { // Pořád nevím jestli vracet User nebo Auth
//			Tady by měla proběhnout registrace nebo mergování, pokud nemám
//			hodnoty, tak bych měl odsud přesměrovat na registraci.
//			$user = $this->register();

			$this->storage->store($source, $data);

		}


		// Update tokenu
		$this->facade->updateAccessToken($source, $id, $token);

		// Login
		$this->login($user);
	}

	/**
	 *
	 * @param string $source
	 * @param string $data
	 */
	private function register($source, $data)
	{
		// Registration or merge
		$this->storage->store($source, $data);

		if ($this->storage->checkRequired()) { // Mám všechny povinné údaje pro registraci?
			if (($user = $this->facade->findByEmail($this->storage->data->email))) { // E-mail nemusím vždy dostat!
				// Merge
				$this->facade->merge($user, $auth);
			} else {
				// Register
				$user = $this->facade->merge($this->storage->user, $this->storage->auth);
			}

			$this->login($user);
		} else {
			$this->presenter->redirect('Sign:Register');
		}
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
	private function verify(Registration $registration)
	{
		// Ověření e-mailu
		$registration = $this->facade->temporarilyRegister($registration);

		// Odeslat e-mail
		$message = new Nette\Mail\Message();
		// Do messages předat $template a tam si s tím pohrát.
		$template = $this->createTemplate()->setFile($this->messages->getTemplate('registration')); // Tohle bude ve storage, ta poté vrátí $message
		$template->code = $registration->verification_code;

		$message->setFrom('noreply@sc.com')
				->addTo($registration->email)
				->setHtmlBody($template); // Tohle buda taky v té storage
		$this->mailer->send($message);

		$this->presenter->flashMessage('We have sent you a verification e-mail. Please check your inbox!', 'success');
		$this->presenter->redirect(':Front:Sign:in');
	}
}


interface IAuthControlFactory
{
	/** @return AuthControl */
	function create();
}

