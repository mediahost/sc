<?php

namespace App\Components\Profile;

use App\Components\BaseControl;
use App\Forms\Renderers\MetronicFormRenderer;
use App\Model\Entity\User;
use App\Model\Facade\UserFacade;
use Nette\Application\UI\Form;
use Nette\Security\Identity;
use Nette\Utils\ArrayHash;

class RecoveryControl extends BaseControl
{

	/** @var UserFacade @inject */
	public $userFacade;

	/** @var User */
	private $user;

	/** @return Form */
	protected function createComponentForm()
	{
		$form = new Form();
		$form->setRenderer(new MetronicFormRenderer());
		$form->setTranslator($this->translator);

		$form->addPassword('newPassword', 'New password:', NULL, 255)
				->setAttribute('placeholder', 'Password')
				->setRequired('Please enter your password')
				->addRule(Form::MIN_LENGTH, 'Password must be at least %d characters long.', self::MIN_PASSWORD_CHARACTERS);

		$form->addPassword('passwordAgain', 'Re-type Your Password:', NULL, 255)
				->setAttribute('placeholder', 'Re-type Your Password')
				->addConditionOn($form['newPassword'], Form::FILLED)
					->addRule(Form::EQUAL, 'Passwords must be equal.', $form['newPassword']);

		$form->addSubmit('recovery', 'Set new password');
		$form->onSuccess[] = $this->formSucceeded;

		return $form;
	}

	/**
	 * @param Form $form
	 * @param ArrayHash $values
	 */
	public function formSucceeded(Form $form, ArrayHash $values)
	{
		$user = $this->userFacade->recoveryPassword($this->user, $values->newPassword);

		$this->presenter->user->login(new Identity($user->id, $user->getRolesPairs(), $user->toArray()));
		$this->presenter->flashMessage('Your password has been successfully changed!', 'success');
		$this->presenter->redirect(':App:Dashboard:');
	}

	/**
	 * @param type $token
	 * @return void
	 */
	public function setToken($token)
	{
		if (!$this->user = $this->userFacade->findByRecoveryToken($token)) {
			$this->presenter->flashMessage('Token to recovery your password is no longer active. Please request new one.', 'info');
			$this->presenter->redirect(':Front:Sign:lostPassword');
		}
	}

}

interface IRecoveryControlFactory
{

	/** @return RecoveryControl */
	function create();
}
