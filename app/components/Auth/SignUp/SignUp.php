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

		$form->addServerValidatedText('mail', 'E-mail')
			->setRequired('Please enter your e-mail.')
			->setAttribute('placeholder', 'E-mail')
			->addRule(Form::EMAIL, 'E-mail has not valid format.')
			->addServerRule([$this, 'validateMail'], $this->translator->translate('%s is already registered.'))
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

		if ($this->registerCandidate) {
			$acceptedFiles = [
				'application/pdf',
				'application/msword',
				'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
			];
			$form->addUpload('cvFile', 'Your CV')
				->addRule(Form::MIME_TYPE, 'File must be PDF or DOC', implode(',', $acceptedFiles))
				->setRequired('Please enter file with %label');
		}

		$form->addSubmit('continue', 'Continue');

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

	public function validateMail(IControl $control, $arg = NULL)
	{
		return $this->userFacade->isUnique($control->getValue());
	}

	public function formSucceeded(Form $form, ArrayHash $values)
	{
		$user = new User($values->mail, FALSE);
		$user->setPassword($values->password);

		if (isset($values->cvFile) && $values->cvFile->isOk()) {
			$user->person->candidate->cvFile = $values->cvFile;
		}

		$this->onSuccess($this, $user);
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
