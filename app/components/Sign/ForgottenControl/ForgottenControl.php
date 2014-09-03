<?php

Namespace App\Components\Sign;

/** Nette */
use Nette\Application\UI;

/** Application */
use App\Components,
	App\Model\Facade;


/**
 * Forgotten control.
 * @author Martin Šifra <me@martinsifra.cz>
 */
class ForgottenControl extends Components\BaseControl
{

	/** @var Facade\UserFacade @inject */
	public $userFacade;
	
	/** @var Facade\AuthFacade @inject */
	public $authFacade;

	/** @var \Nette\Mail\IMailer @inject */
	public $mailer;

	/** @var \App\Model\Storage\MessageStorage @inject */
	public $messages;


	/**
	 * Form factory.
	 * @return UI\Form
	 */
	protected function createComponentForgottenForm()
	{
		$form = new UI\Form();
		$form->setTranslator($this->translator);
		$form->setRenderer(new \App\Forms\Renderers\MetronicFormRenderer());

		$form->addText('email', 'E-mail')
				->setRequired('Please enter your e-mail')
				->setAttribute('placeholder', 'E-mail')
				->addRule(UI\Form::EMAIL, 'Fill right e-mail format');

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

	public function forgottenFormSucceeded(UI\Form $form, $values)
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
