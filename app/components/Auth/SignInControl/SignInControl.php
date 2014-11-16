<?php

namespace App\Components\Auth;

use App\Components\BaseControl;
use App\Forms\Form;
use App\Forms\Renderers\MetronicFormRenderer;
use App\Model\Storage\SettingsStorage;
use Nette\Security\AuthenticationException;
use Nette\Utils\ArrayHash;

class SignInControl extends BaseControl
{

	/** @var array */
	public $onSuccess = [];

	/** @var SettingsStorage @inject */
	public $settings;

	/** @return Form */
	protected function createComponentForm()
	{
		$form = new Form;
		$form->setRenderer(new MetronicFormRenderer());
		$form->setTranslator($this->translator);

		$form->addText('mail', 'E-mail:')
				->setAttribute('placeholder', 'E-mail')
				->setRequired('Please enter your e-mail');

		$form->addPassword('password', 'Password:')
				->setAttribute('placeholder', 'Password')
				->setRequired('Please enter your password.');

		$form->addCheckbox('remember', 'Remember me')
						->getLabelPrototype()->class = "checkbox";

		$form->addSubmit('signIn', 'Sign in');

		$form->onSuccess[] = $this->formSucceeded;
		return $form;
	}

	/**
	 * @param Form $form
	 * @param ArrayHash $values
	 */
	public function formSucceeded(Form $form, ArrayHash $values)
	{
		// TODO: do it without $this->presenter; do by method setUser(\Nette\Security)
		$user = $this->presenter->getUser();
		if ($values->remember) {
			$user->setExpiration($this->settings->expiration->remember, FALSE);
		} else {
			$user->setExpiration($this->settings->expiration->notRemember, TRUE);
		}

		try {
			$user->login($values->mail, $values->password);
			$this->onSuccess();
		} catch (AuthenticationException $e) {
			$form->addError('Incorrect login or password!');
		}
	}

	public function renderLogin()
	{
		$this->setTemplateFile('login');
		parent::render();
	}

}

interface ISignInControlFactory
{

	/** @return SignInControl */
	function create();
}
