<?php

namespace App\Components\Profile;

use App\Components\BaseControl;
use App\Forms\Renderers\MetronicFormRenderer;
use App\Model\Storage\SettingsStorage;
use Nette\Application\UI\Form;
use Nette\Security\AuthenticationException;
use Nette\Utils\ArrayHash;

class SignInControl extends BaseControl
{
	
	public $onSuccess = [];
	
	/** @var SettingsStorage @inject */
	public $settings;

	/** @return Form */
	protected function createComponentForm()
	{	
		$form = new Form();
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
		if ($values->remember) {
			$this->presenter->getUser()->setExpiration($this->settings->expiration->remember , FALSE);
		} else {
			$this->presenter->getUser()->setExpiration($this->settings->expiration->notRemember, TRUE);
		}
		


		try {
			$this->presenter->user->login($values->mail, $values->password);
			$this->onSuccess();
		} catch (AuthenticationException $e) {
			$form->addError($this->translator->translate('Incorrect login or password!'));
		}
	}

}

interface ISignInControlFactory
{

	/** @return SignInControl */
	function create();
}
