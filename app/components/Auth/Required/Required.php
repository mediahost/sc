<?php

namespace App\Components\Auth;

use App\Components\BaseControl;
use App\Forms\Form;
use App\Forms\Renderers\MetronicFormRenderer;
use App\Model\Facade\UserFacade;
use App\Model\Storage\SignUpStorage;
use Nette\Forms\IControl;
use Nette\Utils\ArrayHash;

class Required extends BaseControl
{

	public $onSuccess = [];

	/** @var SignUpStorage @inject */
	public $session;

	/** @var UserFacade @inject */
	public $userFacade;

	/** @return Form */
	protected function createComponentForm()
	{
		$form = new Form();
		$form->setRenderer(new MetronicFormRenderer());
		$form->setTranslator($this->translator);

		$form->addText('mail', 'E-mail')
			->setAttribute('placeholder', 'E-mail')
			->setRequired('Please enter your e-mail.')
			->addRule(Form::EMAIL, 'E-mail has not valid format.')
			->setOption('description', 'for example: example@domain.com');

		$form->addSubmit('continue', 'Continue');

		$form->onSuccess[] = $this->formSucceeded;
		return $form;
	}

	public function validateMail(IControl $control, $arg = NULL)
	{
		return $this->userFacade->isUnique($control->getValue());
	}

	public function formSucceeded(Form $form, ArrayHash $values)
	{
		if (!$this->userFacade->isUnique($values->mail)) {
			$message = $this->translator->translate('E-mail \'%mail\' is already registered.', ['mail' => $values->mail]);
			$form['mail']->addError($message);
		}
		if (!$form->hasErrors()) {
			$this->session->user->mail = $values->mail;
			$this->session->verification = FALSE;
			$this->onSuccess($this, $this->session->user);
		}
	}

	public function renderLogin()
	{
		$this->setTemplateFile('login');
		parent::render();
	}

}

interface IRequiredFactory
{

	/** @return Required */
	function create();
}
