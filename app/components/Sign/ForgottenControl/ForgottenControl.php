<?php

Namespace App\Components\Sign;

use App\Components\Control,
	Nette\Application\UI\Form,
	App\Model\Facade\UserFacade,
	App\Model\Facade\AuthFacade,
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
	
	/** @var AuthFacade */
	private $authFacade;

	/** @var IMailer */
	private $mailer;

	/** @var Messages */
	private $messages;

	public function __construct(\GettextTranslator\Gettext $translator, UserFacade $userFacade, AuthFacade $authFacade, IMailer $mailer, Messages $messages)
	{
		parent::__construct($translator);
		$this->userFacade = $userFacade;
		$this->authFacade = $authFacade;
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
				->addRule(Form::EMAIL, 'Fill right e-mail format');

		$form->addSubmit('send', 'Send');
		$form->addSubmit('cancel', 'Back')
						->setValidationScope(FALSE)
				->onClick[] = $this->forgottenFormCancel;

		$form->onSuccess[] = $this->forgottenFormSucceeded;
		return $form;
	}

	public function forgottenFormCancel(\Nette\Forms\Controls\SubmitButton $button)
	{
		$this->presenter->redirect("Sign:in");
	}

	public function forgottenFormSucceeded(Form $form, $values)
	{
		if ($form['send']->isSubmittedBy()) {
			$auth = $this->authFacade->findByEmail($values->email);

			if (!$auth) {
				$form['email']->addError('We do not register any user with this e-mail address!');
			} else {		
				$user = $auth->user;
				$this->userFacade->forgotten($user);

				// Odeslat e-mail
				$message = $this->messages->getForgottenMail($this->createTemplate(), [
					'token' => $user->recovery
				]);

				$message->addTo($user->email);
				$this->mailer->send($message);

				$this->presenter->flashMessage('Recovery link has been send to your mail.');
				$this->presenter->redirect("Sign:in");
			}
		}
	}

}

interface IForgottenControlFactory
{

	/** @return ForgottenControl */
	function create();
}
