<?php

namespace App\Components\Profile;

use App\Components\BaseControl;
use App\Forms\Renderers\MetronicFormRenderer;
use App\Model\Entity\User;
use App\Model\Facade\UserFacade;
use App\Model\Storage\SignUpStorage;
use Nette\Application\UI\Form;
use Nette\Forms\Controls\TextInput;
use Nette\Utils\ArrayHash;

class SignUpControl extends BaseControl
{

	public $onSuccess = [];

	/** @var UserFacade @inject */
	public $userFacade;
	
	/** @var SignUpStorage @inject */
	public $session;

	/** @return Form */
	protected function createComponentForm()
	{
		$form = new Form();
		$form->setRenderer(new MetronicFormRenderer());
		$form->setTranslator($this->translator);

		$form->addText('mail', 'E-mail:')
				->setAttribute('placeholder', 'E-mail')
				->setRequired('Please enter your e-mail.')
				->addRule(Form::EMAIL, 'E-mail has not valid format.')
				->addRule(function (TextInput $item) {
					return $this->userFacade->isUnique($item->value);
				}, 'This e-mail is registered yet!');

		$form->addPassword('password', 'Password:')
				->setAttribute('placeholder', 'Password')
				->setRequired('Please enter your password.');

		$form->addPassword('passwordVerify', 'Re-type Your Password:')
				->setAttribute('placeholder', 'Re-type Your Password')
				->addConditionOn($form['password'], Form::FILLED)
					->addRule(Form::EQUAL, 'Passwords must be equal.', $form['password']);

		$form->addSubmit('continue', 'Continue');

		$form->onSuccess[] = $this->formSucceeded;
		return $form;
	}

	/**
	 * @param Form $form
	 * @param ArrayHash $values
	 */
	public function formSucceeded(Form $form, ArrayHash $values)
	{
		$user = new User();
		$user->setMail($values->mail)
				->setPassword($values->password);

		$this->session->verification = FALSE;

		$this->onSuccess($this, $user);
	}

}

interface ISignUpControlFactory
{

	/** @return SignUpControl */
	function create();
}
