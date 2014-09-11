<?php

Namespace App\Components\Sign;

/** Nette */
use Nette\Application\UI\Form,
	Nette\Security\Identity;

/** Application */
use App\Components,
	App\Model\Facade,
	App\Model\Entity;

/**
 * Recovery control.
 * @author Martin Šifra <me@martinsifra.cz>
 */
class RecoveryControl extends Components\BaseControl
{
	
	/** @var Facade\AuthFacade @inject */
	public $authFacade;
	
	/** @var \App\Model\Entity\Auth */
	private $auth;


	/**
	 * Form factory.
	 * @return Form
	 */
	protected function createComponentRecoveryForm()
	{
		$form = new Form();
		$form->setTranslator($this->translator);
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
		$auth = $this->authFacade->recoveryPassword($this->auth, $values->password);
		$user = $auth->user;
		
		$this->presenter->user->login(new Identity($user->id, $user->getRolesPairs(), $user->toArray()));
		$this->presenter->flashMessage('Your password has been successfully changed!', 'success');
		$this->presenter->redirect(':Admin:Dashboard:');
	}
	
	public function setAuth(Entity\Auth $auth)
	{
		$this->auth = $auth;
	}
}

interface IRecoveryControlFactory
{

	/** @return RecoveryControl */
	function create();
}
