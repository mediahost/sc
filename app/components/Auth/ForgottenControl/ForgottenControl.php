<?php

namespace App\Components\Auth;

use App\Components\BaseControl;
use App\Forms\Form;
use App\Forms\Renderers\MetronicFormRenderer;
use App\Mail\Messages\IForgottenMessageFactory;
use App\Model\Entity\User;
use App\Model\Facade\UserFacade;
use Nette\Utils\ArrayHash;

class ForgottenControl extends BaseControl
{
	
	/** @var array */
	public $onSuccess = [];
	
	/** @var array */
	public $onMissingUser = [];

	/** @var UserFacade @inject */
	public $userFacade;

	/** @var IForgottenMessageFactory @inject */
	public $forgottenMessage;

	/** @return Form */
	protected function createComponentForm()
	{
		$form = new Form;
		$form->setTranslator($this->translator);
		$form->setRenderer(new MetronicFormRenderer());

		$form->addText('mail', 'E-mail', NULL, 255)
				->setRequired('Please enter your e-mail')
				->setAttribute('placeholder', 'E-mail')
				->addRule(Form::EMAIL, 'Fill right e-mail format');

		$form->addSubmit('send', 'Send');

		$form->onSuccess[] = $this->formSucceeded;
		return $form;
	}

	public function formSucceeded(Form $form, ArrayHash $values)
	{
		$user = $this->userFacade->findByMail($values->mail);

		if (!$user) {
			$this->onMissingUser($values->mail);
		} else {
			$this->userFacade->setRecovery($user);
			$this->em->getDao(User::getClassName())->save($user);

			// Send e-mail with recovery link
			$message = $this->forgottenMessage->create();
			$message->addParameter('link', $this->link('//:Front:Sign:recovery', $user->recoveryToken));
			$message->addTo($user->mail);
			$message->send();

			$this->onSuccess($user->mail);
		}
	}

	public function renderLogin()
	{
		$this->setTemplateFile('login');
		parent::render();
	}

}

interface IForgottenControlFactory
{

	/** @return ForgottenControl */
	function create();
}
