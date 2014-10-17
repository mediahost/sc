<?php

Namespace App\Components\Sign;

use App\Components\Control,
	Nette\Application\UI\Form,
	Nette;

/**
 * Sign in form control
 * @author Martin Šifra <me@martinsifra.cz>
 */
class SignInControl extends \App\Components\BaseControl
{
	// <editor-fold defaultstate="collapsed" desc="constants & variables">
	// </editor-fold>

	/**
	 * Sign in form factory.
	 * @return Nette\Application\UI\Form
	 */
	protected function createComponentSignInForm()
	{
		$form = new Form();
		$form->setTranslator($this->translator);
		$form->setRenderer(new \App\Forms\Renderers\MetronicFormRenderer());

		$form->addText('username', 'E-mail')
				->setRequired('Please enter your e-mail')
				->setAttribute('placeholder', 'E-mail');

		$form->addPassword('password', 'Password')
				->setRequired('Please enter your password')
				->setAttribute('placeholder', 'Password');

		$form->addCheckbox('remember', 'Remember me')
						->getLabelPrototype()->class = "checkbox";

		$form->addSubmit('_submit', 'Login');

		$form->onSuccess[] = $this->signInFormSucceeded;
		return $form;
	}

	public function signInFormSucceeded(Form $form, $values)
	{
		if ($values->remember) {
			$this->presenter->getUser()->setExpiration('14 days', FALSE);
		} else {
			$this->presenter->getUser()->setExpiration('20 minutes', TRUE);
		}

		try {
			$this->presenter->user->login($values->username, $values->password);
			$this->presenter->restoreRequest($this->presenter->backlink);
			$this->presenter->redirect(':Dashboard:Home:');
		} catch (Nette\Security\AuthenticationException $e) {
			$form->addError('Incorrect login or password!');
		}
	}

	// <editor-fold defaultstate="collapsed" desc="setters">
	// </editor-fold>
	// <editor-fold defaultstate="collapsed" desc="getters">
	// </editor-fold>
	// <editor-fold defaultstate="collapsed" desc="renderers">
	// </editor-fold>
	// <editor-fold defaultstate="collapsed" desc="handlers">
	// </editor-fold>
}

interface ISignInControlFactory
{

	/** @return SignInControl */
	function create();
}
