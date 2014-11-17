<?php

namespace App\Components\Auth;

use App\Components\BaseControl;
use App\Forms\Form;
use App\Forms\Renderers\MetronicFormRenderer;
use App\Model\Entity\User;
use App\Model\Facade\UserFacade;
use App\Model\Storage\SignUpStorage;
use App\TaggedString;
use Nette\Utils\ArrayHash;

class SignUpControl extends BaseControl
{

	/** @var array */
	public $onSuccess = [];

	/** @var UserFacade @inject */
	public $userFacade;

	/** @var SignUpStorage @inject */
	public $session;

	/** @return Form */
	protected function createComponentForm()
	{
		$form = new Form;
		$form->setRenderer(new MetronicFormRenderer());
		$form->setTranslator($this->translator);

		$form->addText('mail', 'E-mail:')
				->setAttribute('placeholder', 'E-mail')
				->setRequired('Please enter your e-mail.')
				->addRule(Form::EMAIL, 'E-mail has not valid format.');

		$form->addPassword('password', 'Password:')
				->setAttribute('placeholder', 'Password')
				->setRequired('Please enter your password')
				->addRule(Form::MIN_LENGTH, 'Password must be at least %d characters long.', self::MIN_PASSWORD_CHARACTERS);

		$form->addPassword('passwordVerify', 'Re-type Your Password:')
				->setAttribute('placeholder', 'Re-type Your Password')
				->addConditionOn($form['password'], Form::FILLED)
				->addRule(Form::EQUAL, 'Passwords must be equal.', $form['password']);

		$form->addSubmit('continue', 'Continue');

		$form->onSubmit[] = $this->formSubmit;
		$form->onSuccess[] = $this->formSucceeded;
		return $form;
	}

	public function formSubmit(Form $form) // ToDo: Why this? On Success is good enought!
	{
		$values = $form->getValues();
		if (!$this->userFacade->isUnique($values->mail)) {
			$message = new TaggedString('<%mail%> is already registered.', ['mail' => $values->mail]);
			$message->setTranslator($this->translator);
			$form['mail']->addError((string) $message);
		}
	}

	/**
	 * @param Form $form
	 * @param ArrayHash $values
	 */
	public function formSucceeded(Form $form, ArrayHash $values)
	{
		$entity = $this->load($values);

		$this->session->verification = FALSE;

		$this->onSuccess($this, $entity);
	}

	/**
	 * Load Entity from Form
	 * @param type $values
	 * @return User
	 */
	private function load($values)
	{
		$entity = new User;
		$entity->setMail($values->mail)
				->setPassword($values->password);
		return $entity;
	}

	public function renderLogin()
	{
		$this->setTemplateFile('login');
		parent::render();
	}

}

interface ISignUpControlFactory
{

	/** @return SignUpControl */
	function create();
}
