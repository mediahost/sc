<?php

Namespace App\Components\Sign;

use App\Components\Control,
	Nette\Application\UI\Form,
	App\Model\Facade\UserFacade,
	Nette\Mail\IMailer,
	App\Model\Storage\MessageStorage as Messages,
	Nette;

/**
 * Forgotten control
 * @author Martin Šifra <me@martinsifra.cz>
 */
class ForgottenControl extends Control
{
	
	/** @var UserFacade */
	private $userFacade;
	
	/** @var IMailer */
	private $mailer;
	
	/** @var Messages */
	private $messages;
	
	public function __construct(\GettextTranslator\Gettext $translator, UserFacade $userFacade, IMailer $mailer, Messages $messages)
	{
		parent::__construct($translator);
		$this->userFacade = $userFacade;
		$this->mailer = $mailer;
		$this->messages = $messages;
	}

	public function render()
	{
		$template = $this->getTemplate();
		$template->setFile(__DIR__ . '/render.latte');
		$template->render();
	}

	/**
	 * Form factory.
	 * @return Nette\Application\UI\Form
	 */
	protected function createComponentForgottenForm()
	{
		$form = new Form();
		$form->setRenderer(new \App\Forms\Renderers\MetronicFormRenderer());

		$form->addText('email', 'E-mail')
				->setRequired('Please enter your e-mail')
				->setAttribute('placeholder', 'E-mail')
				->addRule(function(\Nette\Forms\Controls\TextInput $item) { // ToDo: Tohle by šlo eliminovat na jeden dotaz v success metodě
					return $this->userFacade->findByEmail($item->value) !== NULL;
				}, 'We not register any user with this e-mail address!');

		$form->addSubmit('send', 'Send request');

		$form->onSuccess[] = $this->forgottenFormSucceeded;
		return $form;
	}

	public function forgottenFormSucceeded(Form $form, $values)
	{
		$user = $this->userFacade->findByEmail($values->email);
		$this->userFacade->forgotten($user);
		
		// Odeslat e-mail
		$message = $this->messages->getRegistrationMail($this->createTemplate(), [
			'token' => $user->recovery
		]);

		$message->addTo($user->email);
		$this->mailer->send($message);
	}

}

interface IForgottenControlFactory
{

	/** @return ForgottenControl */
	function create();
}
