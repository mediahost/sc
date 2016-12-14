<?php

namespace App\Components\Auth;

use App\Components\BaseControl;
use App\Forms\Form;
use App\Forms\Renderers\MetronicFormRenderer;
use App\Model\Entity\User;
use App\Model\Facade\RoleFacade;
use App\Model\Facade\UserFacade;
use App\Model\Storage\SignUpStorage;
use Nette\Forms\IControl;
use Nette\Utils\ArrayHash;

class SignUp extends BaseControl
{
	/** @var bool */
	private $registerCandidate = TRUE;

	// <editor-fold desc="events">

	/** @var array */
	public $onSuccess = [];

	// </editor-fold>
	// <editor-fold desc="injects">

	/** @var IFacebookFactory @inject */
	public $iFacebookFactory;

	/** @var ITwitterFactory @inject */
	public $iTwitterFactory;

	/** @var ILinkedinFactory @inject */
	public $iLinkedinFactory;

	/** @var UserFacade @inject */
	public $userFacade;

	/** @var RoleFacade @inject */
	public $roleFacade;

	/** @var SignUpStorage @inject */
	public $session;

	// </editor-fold>

	protected function createComponentForm()
	{
		$form = new Form();
		$form->setRenderer(new MetronicFormRenderer());
		$form->setTranslator($this->translator);

		$form->addText('firstname', 'First name')
			->setRequired('Please enter your first name.')
			->setAttribute('placeholder', 'First name');
		$form->addText('surname', 'Surname')
			->setRequired('Please enter your surname.')
			->setAttribute('placeholder', 'Surname');

		$form->addText('mail', 'E-mail')
			->setRequired('Please enter your e-mail.')
			->setAttribute('placeholder', 'E-mail')
			->addRule(Form::EMAIL, 'E-mail has not valid format.')
			->setOption('description', 'for example: example@domain.com');

		$helpText = $this->translator->translate('At least %count% characters long.', $this->settings->passwords->length);
		$form->addPassword('password', 'Password')
			->setAttribute('placeholder', 'Password')
			->setRequired('Please enter your password')
			->addRule(Form::MIN_LENGTH, 'Password must be at least %d characters long.', $this->settings->passwords->length)
			->setOption('description', $helpText);

		$form->addPassword('passwordVerify', 'Re-type Your Password')
			->setAttribute('placeholder', 'Re-type Your Password')
			->setRequired('Please enter your password')
			->addRule(Form::EQUAL, 'Passwords must be equal.', $form['password']);

		$form->addSubmit('continue', 'Continue');

		$form->onValidate[] = $this->formValidate;
		$form->onSuccess[] = $this->formSucceeded;
		return $form;
	}

	public function setRegisterCandidate($value = TRUE)
	{
		$this->registerCandidate = $value;
	}

	public function setRegisterCompany($value = TRUE)
	{
		$this->registerCandidate = !$value;
	}

	public function formSucceeded(Form $form, ArrayHash $values)
	{
		$user = new User($values->mail, FALSE);
		$user->setPassword($values->password);
		$user->person->firstname = $values->firstname;
		$user->person->surname = $values->surname;

		if (isset($values->cvFile) && $values->cvFile->isOk()) {
			$user->person->candidate->cvFile = $values->cvFile;
		}

		$this->onSuccess($this, $user);
	}

	public function formValidate(Form $form)
	{
		$values = $form->getValues();
		if (!$this->userFacade->isUnique($values['mail'])) {
			$form->addError($this->translator->translate('E-mail \'%mail%\' is already registered.', ['mail' => $values['mail']]));
		}
	}

	public function renderLogin()
	{
		$this->setTemplateFile('login');
		$this->template->registerCandidate = $this->registerCandidate;
		parent::render();
	}

	public function renderSocial()
	{
		$this->setTemplateFile('social');
		parent::render();
	}

	// <editor-fold desc="controls">

	/** @return Facebook */
	protected function createComponentFacebook()
	{
		return $this->iFacebookFactory->create();
	}

	/** @return Twitter */
	protected function createComponentTwitter()
	{
		return $this->iTwitterFactory->create();
	}

	/** @return Linkedin */
	protected function createComponentLinkedin()
	{
		return $this->iLinkedinFactory->create();
	}

	// </editor-fold>
}

interface ISignUpFactory
{

	/** @return SignUp */
	function create();
}
