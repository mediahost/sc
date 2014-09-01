<?php

Namespace App\Components\Sign;

use App\Components\Control,
	Nette\Application\UI\Form,
	App\Model\Facade\AuthFacade,
	Nette\Mail\IMailer,
	App\Model\Storage\MessageStorage as Messages,
	Nette;

/**
 * Recovery control
 * @author Martin Šifra <me@martinsifra.cz>
 */
class RecoveryControl extends Control
{
	
	/** @var AuthFacade */
	private $authFacade;
	
	/** @var IMailer */
	private $mailer;
	
	/** @var Messages */
	private $messages;
	
	/** @var \App\Model\Entity\User */
	private $auth;
	
	public function __construct(\GettextTranslator\Gettext $translator, AuthFacade $authFacade, IMailer $mailer, Messages $messages)
	{
		parent::__construct($translator);
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
	protected function createComponentRecoveryForm()
	{
		$form = new Form();
		$form->setRenderer(new \App\Forms\Renderers\MetronicFormRenderer());

		$form->addPassword('password', 'Password')
				->setAttribute('placeholder', 'Password')
				->setRequired('Please enter your password');

		$form->addPassword('password_verify', 'Re-type Your Password')
				->setAttribute('placeholder', 'Re-type Your Password')
				->addConditionOn($form['password_verify'], Form::FILLED)
				->addRule(Form::EQUAL, 'Passwords must be equal.', $form['password']);

		$form->addSubmit('recovery', 'Set new password');
		$form->addSubmit('cancel', 'Back')
				->setValidationScope(FALSE)
				->onClick[] = $this->recoveryFormCancel;
		
		$form->onSuccess[] = $this->recoveryFormSucceeded;
		return $form;
	}

	public function recoveryFormCancel(\Nette\Forms\Controls\SubmitButton $button)
	{
		$this->presenter->redirect("Sign:in");
	}

	public function recoveryFormSucceeded(Form $form, $values)
	{
		$user = $this->authFacade->recovery($this->auth, $values->password);
		
		$this->presenter->user->login(new Nette\Security\Identity($user->id, $user->getRolesPairs(), $user->toArray()));
		$this->presenter->flashMessage('Your password has been successfully changed!', 'success');
		$this->presenter->redirect(':Admin:Dashboard:');
	}
	
	public function setAuth($auth)
	{
		$this->auth = $auth;
	}

}

interface IRecoveryControlFactory
{

	/** @return RecoveryControl */
	function create();
}