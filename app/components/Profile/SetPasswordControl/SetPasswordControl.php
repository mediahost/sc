<?php

namespace App\Components\Profile;

use App\Components\BaseControl;
use App\Forms\Renderers\MetronicFormRenderer;
use App\Model\Entity\User;
use App\Model\Facade\UserFacade;
use Kdyby\Doctrine\EntityDao;
use Kdyby\Doctrine\EntityManager;
use Nette\Application\UI\Form;
use Nette\Utils\ArrayHash;

class SetPasswordControl extends BaseControl
{

	/** @var UserFacade @inject */
	public $userFacade;
	
	/** @var EntityDao */
	private $userDao;

	/** @return Form */
	protected function createComponentForm()
	{
		$form = new Form();
		$form->setRenderer(new MetronicFormRenderer());
		$form->setTranslator($this->translator);

		$form->addText('mail', 'E-mail')
				->setEmptyValue($this->presenter->user->getIdentity()->mail)
				->setDisabled();

		$form->addPassword('newPassword', 'New password:', NULL, 255)
				->setAttribute('placeholder', 'Password')
				->setRequired('Please enter your password')
				->addRule(Form::MIN_LENGTH, 'Password must be at least %d characters long.', 8);

		$form->addPassword('passwordAgain', 'Re-type Your Password:', NULL, 255)
				->setAttribute('placeholder', 'Re-type Your Password')
				->addConditionOn($form['newPassword'], Form::FILLED)
				->addRule(Form::EQUAL, 'Passwords must be equal.', $form['newPassword']);

		$form->addSubmit('save', 'Save');
		$form->onSuccess[] = $this->formSucceeded;

		return $form;
	}

	/**
	 * @param Form $form
	 * @param ArrayHash $values
	 */
	public function formSucceeded(Form $form, ArrayHash $values)
	{

		$user = $this->userFacade->findByMail($this->presenter->user->identity->mail);
		$user->password = $values->newPassword;
		$this->userDao->save($user);

		$this->presenter->flashMessage('Password has been successfuly set!', 'success');
		$this->presenter->redirect(':App:Profile:settings#connect-manager');
	}

	public function injectEntityManager(EntityManager $em)
	{
		$this->userDao = $em->getDao(User::getClassName());
	}

}

interface ISetPasswordControlFactory
{

/** @return SetPasswordControl */
function create();
}