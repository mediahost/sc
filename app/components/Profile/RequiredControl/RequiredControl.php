<?php

namespace App\Components\Profile;

use App\Components\BaseControl;
use App\Forms\Renderers\MetronicFormRenderer;
use App\Model\Facade\UserFacade;
use App\Model\Storage\SignUpStorage;
use Nette\Application\UI\Form;
use Nette\Forms\Controls\TextInput;
use Nette\Utils\ArrayHash;

class RequiredControl extends BaseControl
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

		$form->addText('mail', 'E-mail:')
				->setAttribute('placeholder', 'E-mail')
				->setRequired('Please enter your e-mail.')
				->addRule(Form::EMAIL, 'E-mail has not valid format.')
				->addRule(function (TextInput $item) {
					return $this->userFacade->isUnique($item->value);
				}, 'This e-mail is registered yet!');

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
		$this->session->user->mail = $values->mail;
		$this->session->verification = FALSE;
		$this->onSuccess($this, $this->session->user);
	}

}

interface IRequiredControlFactory
{

	/** @return RequiredControl */
	function create();
}
