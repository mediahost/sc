<?php

namespace App\Components\Sign;

use Nette\Application\UI\Control,
	Nette\Application\UI\Form,
	Nette,
	App\Model\Entity;

/**
 *
 * @author Martin Šifra <me@martinsifra.cz>
 */
class RegisterControl extends Control
{

	/** @var \Kdyby\Doctrine\EntityManager */
	private $em;

	/** @var \App\Model\Storage\RegistrationStorage */
	private $registration;

	/** @var \App\Model\Facade\Users */
	private $users;

	/** @var \Kdyby\Doctrine\EntityDao */
	private $registrationDao;

	/** @var Nette\Mail\IMailer */
	private $mailer;

	/** @var \App\Model\Storage\MessageStorage @inject */
	private $messages;
	
	
	public function __construct(\Kdyby\Doctrine\EntityManager $em, \App\Model\Storage\RegistrationStorage $reg, \App\Model\Facade\Users $users, Nette\Mail\IMailer $mailer, \App\Model\Storage\MessageStorage $messages)
	{
		$this->em = $em;
		$this->registration = $reg;
		$this->users = $users;

		$this->registrationDao = $this->em->getDao(Entity\Registration::getClassName());
		$this->mailer = $mailer;
		$this->messages = $messages;
	}

	public function render()
	{
		$template = $this->template;
		$template->oauth = $this->registration->isOauth();
		$template->birthdate = $this->registration->isRequired('birthdate');
		$template->email = $this->registration->isRequired('email');
		$template->setFile(__DIR__ . '/render.latte');
		$template->render();
	}

	/**
	 * Sign in form factory.
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
				$this->presenter->user->login(new \Nette\Security\Identity($existing->id, $existing->getRolesPairs(), $existing->toArray()));
				$this->presenter->flashMessage('You have been successfully registered and logged in!', 'success');
				$this->presenter->redirect(':Admin:Dashboard:');
			} else {
				// Neověřený e-mail
				$this->registration->user->email = $values->email;
				$registration = $this->registration->toRegistration();
			}
			
		} else {
			// Registrace přes formulář
			$registration = new Entity\Registration();
			$registration->email = $values->email;
			$registration->key = $values->email;
			$registration->source = 'app';
			$registration->hash = \Nette\Security\Passwords::hash($values->password);
		}
		
		
		if (!isset($this->registration->user->email)) {
			// Ověření e-mailu
			$registration->verification_code = Nette\Utils\Strings::random(32);
			$this->em->persist($registration);
			$this->em->flush();
			
			// Odeslat e-mail
			$message = new Nette\Mail\Message();
			$template = $this->createTemplate()->setFile($this->messages->getTemplate('registration'));
			$template->code = $registration->verification_code;
			
			$message->setFrom('noreply@sc.com')
					->addTo($registration->email)
					->setHtmlBody($template);
			$this->mailer->send($message);
			
			$this->presenter->flashMessage('Verification code has been sent to your e-mail. Please check your inbox!', 'success');
			$this->presenter->redirect(':Front:Sign:in');
		}
	}
}

interface IRegisterControlFactory
{
	/** @return RegisterControl */
	function create();
}
