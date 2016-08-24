<?php

namespace App\Components\Auth;

use App\Components\BaseControl;
use App\Forms\Form;
use App\Forms\Renderers\MetronicFormRenderer;
use App\Model\Entity\User;
use App\Model\Facade\RoleFacade;
use App\Model\Facade\UserFacade;
use App\Model\Storage\SignUpStorage;
use App\TaggedString;
use Nette\Forms\IControl;
use Nette\Utils\ArrayHash;

class SignUpControl extends BaseControl
{
	// <editor-fold desc="events">

	/** @var array */
	public $onSuccess = [];

	// </editor-fold>
	// <editor-fold desc="injects">

	/** @var IFacebookControlFactory @inject */
	public $iFacebookControlFactory;

	/** @var ITwitterControlFactory @inject */
	public $iTwitterControlFactory;

	/** @var ILinkedinControlFactory @inject */
	public $iLinkedinControlFactory;

	/** @var UserFacade @inject */
	public $userFacade;

	/** @var RoleFacade @inject */
	public $roleFacade;

	/** @var SignUpStorage @inject */
	public $session;

	// </editor-fold>

	/** @return Form */
	protected function createComponentForm()
	{
		$form = new Form;
		$form->setRenderer(new MetronicFormRenderer());
		$form->setTranslator($this->translator);

		$form->addServerValidatedText('mail', 'E-mail')
				->setRequired('Please enter your e-mail.')
				->setAttribute('placeholder', 'E-mail')
				->addRule(Form::EMAIL, 'E-mail has not valid format.')
				->addServerRule([$this, 'validateMail'], $this->translator->translate('%s is already registered.'))
				->setOption('description', 'for example: example@domain.com');

		$helpText = new TaggedString('At least %d characters long.', $this->settings->passwords->length);
		$helpText->setTranslator($this->translator);
		$form->addPassword('password', 'Password')
				->setAttribute('placeholder', 'Password')
				->setRequired('Please enter your password')
				->addRule(Form::MIN_LENGTH, 'Password must be at least %d characters long.', $this->settings->passwords->length)
				->setOption('description', (string) $helpText);

		$form->addPassword('passwordVerify', 'Re-type Your Password')
				->setAttribute('placeholder', 'Re-type Your Password')
				->setRequired('Please enter your password')
				->addRule(Form::EQUAL, 'Passwords must be equal.', $form['password']);

		$form->addSubmit('continue', 'Continue');

		$form->onSuccess[] = $this->formSucceeded;
		return $form;
	}

	public function validateMail(IControl $control, $arg = NULL)
	{
		return $this->userFacade->isUnique($control->getValue());
	}

	/**
	 * @param Form $form
	 * @param ArrayHash $values
	 */
	public function formSucceeded(Form $form, ArrayHash $values)
	{
		$user = new User($values->mail, FALSE);
		$user->setPassword($values->password);

		$this->onSuccess($this, $user);
	}

	public function renderLogin()
	{
		$this->setTemplateFile('login');
		parent::render();
	}

	public function renderSocial()
	{
		$this->setTemplateFile('social');
		parent::render();
	}

	// <editor-fold desc="controls">

	/** @return FacebookControl */
	protected function createComponentFacebook()
	{
		return $this->iFacebookControlFactory->create();
	}

	/** @return TwitterControl */
	protected function createComponentTwitter()
	{
		return $this->iTwitterControlFactory->create();
	}

	/** @return LinkedinControl */
	protected function createComponentLinkedin()
	{
		return $this->iLinkedinControlFactory->create();
	}

	// </editor-fold>
}

interface ISignUpControlFactory
{

	/** @return SignUpControl */
	function create();
}
