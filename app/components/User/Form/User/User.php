<?php

namespace App\Components\User;

use App\Components\BaseControl;
use App\Forms\Form;
use App\Forms\Renderers\MetronicHorizontalFormRenderer;
use App\Model\Entity;
use App\Model\Facade\CommunicationFacade;
use App\Model\Facade\CompanyFacade;
use App\Model\Facade\RoleFacade;
use App\Model\Facade\UserFacade;
use Exception;
use Kdyby\Doctrine\DuplicateEntryException;
use Nette\Utils\ArrayHash;
use Nette\Utils\Html;

class User extends BaseControl
{

	/** @var \Nette\Security\User @inject */
	public $identity;

	/** @var CompanyFacade @inject */
	public $companyFacade;

	/** @var RoleFacade @inject */
	public $roleFacade;

	/** @var UserFacade @inject */
	public $userFacade;

	/** @var CommunicationFacade @inject */
	public $communicationFacade;

	// <editor-fold desc="events">

	/** @var array */
	public $onAfterSave = [];

	// </editor-fold>
	// <editor-fold desc="variables">

	/** @var Entity\User */
	private $user;

	/** @var Entity\Company */
	private $company;

	/** @var string */
	private $companyRole = Entity\CompanyRole::JOBBER;

	/** @var array */
	private $loadedCompanies = [];

	// </editor-fold>

	protected function createComponentForm()
	{
		$this->checkEntityExistsBeforeRender();

		$form = new Form();
		$form->setTranslator($this->translator);
		$form->setRenderer(new MetronicHorizontalFormRenderer(2, 10));

		$form->addGroup();
		$form->addText('mail', 'E-mail')
			->addRule(Form::EMAIL, 'Fill right format')
			->addRule(Form::FILLED, 'Mail must be filled');

		if ($this->user->isNew()) {
			$form->addCheckSwitch('createRegistration', 'Create registration')
				->setDefaultValue(FALSE)
				->addCondition($form::EQUAL, TRUE)
				->toggle('password');
		}

		$password = $form->addText('password', 'Password')
			->setOption('id', 'password');
		if ($this->user->isNew()) {
			$helpText = $this->translator->translate('At least %count% characters long.', $this->settings->passwords->length);
			$password
				->addConditionOn($form['createRegistration'], $form::EQUAL, TRUE)
				->addRule(Form::FILLED, 'Password must be filled')
				->addRule(Form::MIN_LENGTH, 'Password must be at least %d characters long.', $this->settings->passwords->length);
			$password->setOption('description', $helpText);
		}

		if ($this->identity->isAllowed('companies', 'edit') && $this->user->isCompany()) {
			$compayRepo = $this->em->getRepository(Entity\Company::getClassName());
			$companies = $compayRepo->findPairs('name');
			$form->addMultiSelect('companyAccess', 'Access for companies', $companies);
		}

		if ($this->identity->isAllowed('persons', 'edit')) {
			if ($this->user->isCandidate()) {
				$form->addGroup('Candidate info');
			} else {
				$form->addGroup('Person info');
			}

			$form->addSelect('title', 'Title', Entity\Person::getTitleList())
				->getControlPrototype()->class[] = 'input-small';

			$form->addText('firstname', 'First Name(s)', NULL, 100)
				->setRequired('Please enter your First Name(s).');

			$surname = $form->addText('surname', 'Surname(s)', NULL, 100);
			if ($this->user->isCandidate()) {
				$surname->setRequired('Please enter your Surname(s).');
			}

			$form->addSelect2('country', 'Country', Entity\Address::getCountriesList())
				->setPrompt('Not disclosed');

			$form->addRadioList('gender', 'Gender', Entity\Person::getGenderList())
				->setDefaultValue('x');

			$form->addText('phone', 'Mobile number');

			if ($this->identity->isAllowed('candidates', 'edit') && $this->user->isCandidate() && $this->user->isNew()) {
				$acceptedFiles = [
					'application/pdf',
					'application/msword',
					'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
				];
				$form->addUpload('cvFile', 'Upload New CV')
					->addConditionOn($form['createRegistration'], Form::EQUAL, TRUE)
					->addRule(Form::MIME_TYPE, 'File must be PDF or DOC', implode(',', $acceptedFiles));
			}

			if ($this->user->isAdmin()) {
				$form->addCheckSwitch('isDealer', 'Dealer')
					->addCondition(Form::EQUAL, TRUE)
					->toggle('smtp-container');
			}
		}

		if ($this->user->isAdmin()) {
			$fieldsetHide = Html::el('div', [
				'id' => 'smtp-container',
			]);

			$form->addGroup('SMTP account')
				->setOption('container', $fieldsetHide);

			$form->addText('smtpHost', 'Host');
			$form->addText('smtpUsername', 'Username');
			$form->addText('smtpPassword', 'Password');
			$form->addCheckSwitch('smtpSsl', 'SSL');
		}

		$form->setDefaults($this->getDefaults());

		$form->addSubmit('save', 'Save');
		if ($this->identity->isAllowed('candidates', 'edit') && $this->user->isCandidate()) {
			$form->addSubmit('saveAndItSkills', 'Save & Go to IT skills');
			$form->addSubmit('saveAndCandidate', 'Save & Go to candidate');
		}

		$form->onValidate[] = $this->formValidate;
		$form->onSuccess[] = $this->formSucceeded;
		return $form;
	}

	public function formValidate(Form $form)
	{
		$values = $form->getValues();
		if (!($this->user->isNew() ?
			$this->userFacade->isUnique($values['mail']) :
			$this->userFacade->isUnique($values['mail'], $this->user->mail))
		) {
			$message = $this->translator->translate('E-mail \'%mail%\' is already registered.', ['mail' => $values['mail']]);
			$form['mail']->addError($message);
		}
	}

	public function formSucceeded(Form $form, $values)
	{
		$this->load($values);
		try {
			$this->save();
			$action = null;
			if (isset($form['saveAndCandidate']) && $form['saveAndCandidate']->submittedBy) {
				$action = 'goToCandidate';
			}
			if (isset($form['saveAndItSkills']) && $form['saveAndItSkills']->submittedBy) {
				$action = 'goToItSkills';
			}
			$this->onAfterSave($this->user, $action);
		} catch (DuplicateEntryException $exc) {
			$message = $this->translator->translate('E-mail \'%mail%\' is already registred', ['mail' => $values->mail]);
			$form['mail']->addError($message);
		}
	}

	private function load(ArrayHash $values)
	{
		if (isset($values->mail) && $values->mail != $this->user->mail) {
			$this->user->mail = $values->mail;
			$this->user->clearSocials();
		}

		if (!isset($values->createRegistration) || $values->createRegistration) {
			if ($values->password !== NULL && $values->password !== "") {
				$this->user->setPassword($values->password);
			}
		} else {
			$this->user->createdByAdmin = TRUE;
			$this->user->verificated = FALSE;
		}

		if (isset($values->companyAccess)) {
			$this->loadedCompanies[$this->companyRole] = $values->companyAccess;
		}

		if (isset($values->firstname)) {
			$this->user->person->firstname = $values->firstname;
		}
		if (isset($values->surname)) {
			$this->user->person->surname = $values->surname;
		}
		if (isset($values->country)) {
			if (!$this->user->person->address) {
				$this->user->person->address = new Entity\Address();
			}
			$this->user->person->address->country = $values->country;
		}
		if (isset($values->gender)) {
			$this->user->person->gender = $values->gender;
		}
		if (isset($values->phone)) {
			$this->user->person->phoneMobile = $values->phone;
		}
		if (isset($values->cvFile) && $values->cvFile->isOk()) {
			$this->user->person->candidate->cvFile = $values->cvFile;
		}
		if (isset($values->isDealer)) {
			$this->user->isDealer = $values->isDealer;

			if ($this->user->isDealer) {
				if (!$this->user->smtpAccount) {
					$this->user->smtpAccount = new Entity\SmtpAccount();
				}
				if (isset($values->smtpUsername)) {
					$this->user->smtpAccount->username = $values->smtpUsername;
				}
				if (isset($values->smtpHost)) {
					$this->user->smtpAccount->host = $values->smtpHost;
				}
				if (isset($values->smtpPassword)) {
					$this->user->smtpAccount->password = $values->smtpPassword;
				}
				if (isset($values->smtpSsl)) {
					$this->user->smtpAccount->secure = $values->smtpSsl;
				}
			} else {
				$this->user->smtpAccount = NULL;
			}
		}

		return $this;
	}

	private function save()
	{
		if ($this->user->isNew()) {
			$this->userFacade->setVerification($this->user);
		}
		$userRepo = $this->em->getRepository(Entity\User::getClassName());
		$userRepo->save($this->user);

		if (!$this->communicationFacade->findSender($this->user)) {
			$this->communicationFacade->createSender($this->user);
		}

		if ($this->user->isCompany()) {
			$companies = [];
			if ($this->company) {
				$companies[$this->company->id] = $this->company;
			} else if (isset($this->loadedCompanies[$this->companyRole])) {
				foreach ($this->loadedCompanies[$this->companyRole] as $companyId) {
					$companyRepo = $this->em->getRepository(Entity\Company::getClassName());
					$company = $companyRepo->find($companyId);
					if ($company) {
						$companies[$company->id] = $company;
					}
				}
			}
			foreach ($companies as $company) {
				$companyPermission = $this->companyFacade->findPermission($company, $this->user);
				if (!$companyPermission) {
					$this->companyFacade->createPermission($company, $this->user, $this->companyRole);
				}
				if (!$this->communicationFacade->findSender($this->user, $company)) {
					$this->communicationFacade->createSender($this->user, $company);
				}
			}
		}

		return $this;
	}

	protected function getDefaults()
	{
		$role = $this->companyFacade->findRoleByName($this->companyRole);
		$values = [
			'mail' => $this->user->mail,
			'companyAccess' => $role ? array_keys($this->user->getCompanies($role)) : [],
			'title' => $this->user->person->title,
			'firstname' => $this->user->person->firstname,
			'surname' => $this->user->person->surname,
			'country' => isset($this->user->person->address->country) ? $this->user->person->address->country : null,
			'gender' => $this->user->person->gender,
			'phone' => $this->user->person->phoneMobile,
			'isDealer' => $this->user->isDealer,
			'smtpHost' => $this->user->smtpAccount ? $this->user->smtpAccount->host : 'mail.source-code.com',
			'smtpUsername' => $this->user->smtpAccount ? $this->user->smtpAccount->username : $this->user->mail,
			'smtpPassword' => $this->user->smtpAccount ? $this->user->smtpAccount->password : NULL,
			'smtpSsl' => $this->user->smtpAccount ? $this->user->smtpAccount->secure : FALSE,
		];
		return $values;
	}

	private function checkEntityExistsBeforeRender()
	{
		if (!$this->user) {
			throw new UserControlException('Use setUser(\App\Model\Entity\User) before render');
		}
	}

	// <editor-fold desc="setters & getters">

	public function setUser(Entity\User $user)
	{
		$this->user = $user;
		return $this;
	}

	public function setCompany(Entity\Company $company)
	{
		$this->company = $company;
		return $this;
	}

	// </editor-fold>
}

class UserControlException extends Exception
{

}

interface IUserFactory
{

	/** @return User */
	function create();
}
