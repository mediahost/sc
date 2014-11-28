<?php

namespace App\Components\Auth;

use App\Components\BaseControl;
use App\Forms\Form;
use App\Forms\Renderers\MetronicFormRenderer;
use App\Model\Entity\User;
use App\Model\Facade\UserFacade;
use App\TaggedString;
use Kdyby\Doctrine\EntityDao;
use Kdyby\Doctrine\EntityManager;
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
		$form = new Form;
		$form->setRenderer(new MetronicFormRenderer());
		$form->setTranslator($this->translator);

		// TODO: do it without $this->presenter; do by method setUser(\Nette\Security)
		$user = $this->presenter->user->identity;
		$form->addText('mail', 'E-mail')
				->setEmptyValue($user->mail)
				->setDisabled();

		$helpText = new TaggedString('At least <%number%> characters long.', ['number' => $this->settings->passwordsPolicy->length]);
		$helpText->setTranslator($this->translator);
		$form->addPassword('newPassword', 'New password', NULL, 255)
				->setAttribute('placeholder', 'Password')
				->setRequired('Please enter your password')
				->addRule(Form::MIN_LENGTH, 'Password must be at least %d characters long.', $this->settings->passwordsPolicy->length)
				->setOption('description', (string) $helpText);

		$form->addPassword('passwordAgain', 'Re-type Your Password', NULL, 255)
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
		// TODO: do it without $this->presenter; do by method setUser(\Nette\Security)
		$user = $this->userFacade->findByMail($this->presenter->user->identity->mail);
		$user->password = $values->newPassword;
		$this->userDao->save($user);

		// TODO: do it without $this->presenter; do it with event
		$this->presenter->flashMessage('Password has been successfuly set!', 'success');
		$this->presenter->redirect(':App:Profile:settings#connect-manager');
	}

	public function injectEntityManager(EntityManager $em)
	{
		$this->userDao = $em->getDao(User::getClassName());
	}

	public function renderLogin()
	{
		$this->setTemplateFile('login');
		parent::render();
	}

}

interface ISetPasswordControlFactory
{

	/** @return SetPasswordControl */
	function create();
}
