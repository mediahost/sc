<?php

namespace App\Components\Sign;

/* Nette */

use Nette\Application\UI\Form,
	Nette\Security\Identity,
	Nette\Utils;

/* Application */
use App\Components,
	App\Model\Storage,
	App\Model\Storage\RegistrationStorage,
	App\Model\Facade,
	App\Model\Entity;

/* Facebook */
use Kdyby\Facebook\Facebook,
	Kdyby\Facebook\Dialog\LoginDialog,
	Kdyby\Facebook\FacebookApiException;

/* Twitter */
use Netrium\Addons\Twitter\Authenticator as Twitter,
	Netrium\Addons\Twitter\AuthenticationException as TwitterException;

/**
 * AuthControl provides login or registration via AOuth and aplication.
 */
class AuthControl extends Components\BaseControl
{

	/** @var RegistrationStorage @inject */
	public $storage;

	/** @var Facade\RegistrationFacade @inject */
	public $registrationFacade;

	/** @var Facade\UserFacade @inject */
	public $userFacade;

	/** @var Facade\AuthFacade @inject */
	public $authFacade;

	/** @var \Nette\Mail\IMailer @inject */
	public $mailer;

	/** @var Facebook @inject */
	public $facebook;

	/** @var Twitter @inject */
	public $twitter;

	/** @var Storage\MessageStorage @inject */
	public $messages;

	/** @var \Kdyby\Doctrine\EntityDao */
	private $authDao;

	/** @var bool Force registration without required data. */
	private $force = FALSE;

	/**
	 * Registration form.
	 */
	public function renderRegistration()
	{
		$template = $this->template;
		$template->storage = $this->storage;
		$template->setFile(__DIR__ . '/registration.latte');
		$template->render();
	}

	/**
	 * Icons provides login.
	 */
	public function renderIcons()
	{
		$template = $this->template;
		$template->setFile(__DIR__ . '/icons.latte');
		$template->render();
	}

	/**
	 * Activation and deactivation.
	 */
	public function renderConnect()
	{
		$template = $this->template;

		$sources = [
			RegistrationStorage::SOURCE_APP => [
				'name' => 'SourceCode',
				'status' => 'deactivated',
				'action' => 'activate',
				'plink' => ':Admin:UserSettings:settings#set-password'
			],
			RegistrationStorage::SOURCE_FACEBOOK => [
				'name' => 'Facebook',
				'status' => 'deactivated',
				'action' => 'activate',
				'link' => 'facebook-open!'
			],
			RegistrationStorage::SOURCE_TWITTER => [
				'name' => 'Twitter',
				'status' => 'deactivated',
				'action' => 'activate',
				'link' => 'twitter!'
			]
		];

		$user = $this->userFacade->find($this->presenter->user->id);
		$auths = $this->authFacade->findByUser($user);
		
		$count = count($auths);
		$lastAuth = $auths[0]->source;
		
		$template->auths = $auths;
		
		foreach ($auths as $auth) {
			switch ($auth->source) {
				case RegistrationStorage::SOURCE_APP:
					$sources[RegistrationStorage::SOURCE_APP]['status'] = 'active';
					$sources[RegistrationStorage::SOURCE_APP]['action'] = 'deactivate';
					unset($sources[RegistrationStorage::SOURCE_APP]['plink']);
					$sources[RegistrationStorage::SOURCE_APP]['link'] = 'deactivate!';
					$sources[RegistrationStorage::SOURCE_APP]['arg'] = $auth->id;
					break;
				case RegistrationStorage::SOURCE_FACEBOOK:
					$sources[RegistrationStorage::SOURCE_FACEBOOK]['status'] = 'active';
					$sources[RegistrationStorage::SOURCE_FACEBOOK]['action'] = 'deactivate';
					$sources[RegistrationStorage::SOURCE_FACEBOOK]['link'] = 'deactivate!';
					$sources[RegistrationStorage::SOURCE_FACEBOOK]['arg'] = $auth->id;
					break;
				case RegistrationStorage::SOURCE_TWITTER:
					$sources[RegistrationStorage::SOURCE_TWITTER]['status'] = 'active';
					$sources[RegistrationStorage::SOURCE_TWITTER]['action'] = 'deactivate';
					$sources[RegistrationStorage::SOURCE_TWITTER]['link'] = 'deactivate!';
					$sources[RegistrationStorage::SOURCE_TWITTER]['arg'] = $auth->id;
					break;
				default:
					break;
			}
		}
		
		if ($count < 2) {
			$sources[$lastAuth]['action'] = NULL;
		}
		
		$template->sources = $sources;

		$template->setFile(__DIR__ . '/connect.latte');
		$template->render();
	}
	
	/**
	 * Activation and deactivation.
	 */
	public function renderSetPassword()
	{
		$template = $this->template;
		$template->setFile(__DIR__ . '/setPassword.latte');
		$template->render();
	}

	/**
	 * @return Form
	 */
	protected function createComponentRegisterForm()
	{
		$form = new Form();
		$form->setTranslator($this->translator);
		$form->setRenderer(new \App\Forms\Renderers\MetronicFormRenderer());

		if ($this->storage->isRequired('name')) {
			$form->addText('reg_name', 'Name')
					->setAttribute('placeholder', 'Full name');
		}

		if ($this->storage->isRequired('mail')) {
			$form->addText('reg_mail', 'E-mail')
					->setAttribute('placeholder', 'E-mail')
					->setRequired('Please enter your e-mail')
					->addRule(Form::EMAIL, 'Fill right e-mail format')
					->addRule(function(\Nette\Forms\Controls\TextInput $item) {
						if (!$this->storage->isOAuth()) { // Check just for application login
							return $this->authFacade->isUnique($item->value, RegistrationStorage::SOURCE_APP);
						}
						return TRUE;
					}, 'This e-mail is registered yet!');
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
		$this->presenter->redirect(':Front:Sign:in');
	}

	/**
	 * Callback for process reg. form data.
	 * @param Form $form
	 * @param Utils\ArrayHash $values
	 */
	public function registerFormSucceeded(Form $form, $values)
	{
		// Namapování hodnot z formuláře
		if ($this->storage->isRequired('name')) {
			$this->storage->user->name = $values->reg_name;
		}

		if ($this->storage->isRequired('mail')) {
			$this->storage->user->mail = $values->reg_mail;
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
			$reg->setKey($values->reg_mail)
					->setSource(RegistrationStorage::SOURCE_APP)
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
				$source = RegistrationStorage::SOURCE_FACEBOOK;

				$this->process($source, $fb->getUser(), $data, $fb->getAccessToken());
			} catch (FacebookApiException $e) {
				\Tracy\Debugger::log($e->getMessage(), 'facebook');

				$this->presenter->flashMessage('We are sorry, facebook authentication failed hard.');
			}
		};

		return $dialog;
	}
	
	/**
	 * @return Form
	 */
	protected function createComponentSetPasswordForm()
	{
		$form = new Form();
		$form->setTranslator($this->translator);
		$form->setRenderer(new \App\Forms\Renderers\MetronicFormRenderer());
		
		$form->addText('reg_mail', 'E-mail')
				->setEmptyValue($this->presenter->user->getIdentity()->mail)
				->setDisabled();

		$form->addPassword('reg_password', 'Password')
				->setRequired('Please enter your password')
				->setAttribute('placeholder', 'Password');

		$form->addPassword('reg_password_verify', 'Re-type Your Password')
				->setAttribute('placeholder', 'Re-type Your Password')
				->addConditionOn($form['reg_password'], Form::FILLED)
				->addRule(Form::EQUAL, 'Passwords must be equal.', $form['reg_password']);

		$form->addSubmit('save', 'Save');

		$form->onSuccess[] = $this->setPasswordFormSucceeded;
		return $form;
	}

	/**
	 * @param Form $form
	 * @param Utils\ArrayHash $values
	 */
	public function setPasswordFormSucceeded(Form $form, $values)
	{
		$mail = $this->presenter->user->getIdentity()->mail;

		$auth = new Entity\Auth();
		$auth->source = RegistrationStorage::SOURCE_APP;
		$auth->key = $mail;
		$auth->user = $this->userFacade->findByMail($mail);
		$auth->password = $values->reg_password;
		$this->authDao->save($auth);
		
		$this->presenter->flashMessage('Password has been successfuly set!', 'success');
		$this->presenter->redirect(':Admin:UserSettings:settings#connect-manager');
	}

	/**
	 * Handle processing Twitter OAuth.
	 * @throws \Nette\Security\AuthenticationException
	 */
	public function handleTwitter()
	{
		$this->storage->wipe();

		try {
			$data = $this->twitter->tryAuthenticate();
			$source = RegistrationStorage::SOURCE_TWITTER;

			$this->process($source, $data['user']->id, $data['user'], $data['accessToken']['key']);
		} catch (TwitterException $e) {
			\Tracy\Debugger::log($e->getMessage(), 'twitter');

			throw new \Nette\Security\AuthenticationException('Twitter authentication did not approve.');
		}
	}

	public function handleDeactivate($id)
	{
		$auth = $this->authDao->findOneBy(['id' => $id]);
		

		if ($auth) {
			if (count($this->authFacade->findByUser($auth->user)) > 1) {
				$this->authDao->delete($auth);
				$this->presenter->flashMessage('Connection has been deactivated.');
			} else {
				$this->presenter->flashMessage('Last login method is not possible deactivate.');
			}
		}

		if ($this->presenter->isAjax()) {
			$this->redrawControl();
		} else {
			$this->redirect('this#connect-manager');
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
		if (!$auth = $this->authFacade->findByKey($source, $id)) {

			$this->storage->store($source, $data, $token);
			
			if ($this->force === TRUE) {
				$this->storage->user->mail = $this->presenter->user->getIdentity()->mail;
				
				if ($source === RegistrationStorage::SOURCE_APP) {
					$this->presenter->redirect(':Admin:UserSetings:settings#set-password');
				} else {
					$user = $this->mergeOrRegister();
				}
				
				$this->presenter->redirect(':Admin:UserSettings:settings#connect-manager');
			} else {
				if ($this->storage->isRequired()) {
					$this->presenter->redirect(':Front:Sign:Registration', $source);
				} else {
					$user = $this->mergeOrRegister();
				}
			}

			$this->storage->wipe();
		} else {
			if ($this->force === TRUE) {
				$this->presenter->flashMessage('This account is connected to another user!', 'warning');
				$this->presenter->redirect(':Admin:UserSettings:settings#connect-manager');
			} else {
				$user = $auth->user;
				$this->authFacade->updateAccessToken($auth, $token);
			}
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
		$registration = $this->registrationFacade->registerTemporarily($registration);

		// Odeslat e-mail
		$message = $this->messages->getRegistrationMail($this->createTemplate(), [
			'code' => $registration->verificationToken
		]);

		$message->addTo($registration->mail);
		$this->mailer->send($message);

		$this->presenter->flashMessage('We have sent you a verification e-mail. Please check your inbox!', 'success');
		$this->presenter->redirect(':Front:Sign:in');
	}

	/**
	 * Choose registration or merging facade methods.
	 * @return Entity\User
	 */
	private function mergeOrRegister() // ToDo: Tohle celé může být ve facade
	{
		if (($user = $this->userFacade->findByMail($this->storage->user->mail))) {
			return $this->registrationFacade->merge($user, $this->storage->auth);
		} else {
			return $this->registrationFacade->register($this->storage->user, $this->storage->auth);
		}
	}

	/**
	 * @param bool $force
	 */
	public function setForce($force = TRUE)
	{
		$this->force = $force;
	}

	public function injectEntityManager(\Kdyby\Doctrine\EntityManager $em)
	{
		$this->authDao = $em->getDao(Entity\Auth::getClassName());
	}

}

interface IAuthControlFactory
{

	/** @return AuthControl */
	function create();
}
