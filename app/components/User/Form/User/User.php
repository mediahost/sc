<?php

namespace App\Components\User;

use App\Components\BaseControl;
use App\Forms\Form;
use App\Forms\Renderers\Bootstrap3FormRenderer;
use App\Model\Entity;
use App\Model\Facade\CompanyFacade;
use App\Model\Facade\RoleFacade;
use App\Model\Facade\UserFacade;
use Exception;
use Kdyby\Doctrine\DuplicateEntryException;
use Nette\Forms\IControl;
use Nette\Utils\ArrayHash;
use Tracy\Debugger;

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

	// <editor-fold desc="events">

	/** @var array */
	public $onAfterSave = [];

	// </editor-fold>
	// <editor-fold desc="variables">

	/** @var Entity\User */
	private $user;

	/** @var Entity\Company */
	private $company;

	/** @var array */
	private $disabledRoles = [];

	/** @var bool */
	private $disableChangeRoles = FALSE;

	/** @var array */
	private $roles;

	/** @var array */
	private $loadedCompanies = [];

	// </editor-fold>

	protected function createComponentForm()
	{
		$this->checkEntityExistsBeforeRender();

		$form = new Form();
		$form->setTranslator($this->translator);
		$form->setRenderer(new Bootstrap3FormRenderer());

		$mail = $form->addText('mail', 'E-mail')
			->addRule(Form::EMAIL, 'Fill right format')
			->addRule(Form::FILLED, 'Mail must be filled');
		if (!$this->user->isNew()) {
			$mail->setDisabled();
		}

		$password = $form->addText('password', 'Password');
		if ($this->user->isNew()) {
			$helpText = $this->translator->translate('At least %count% characters long.', $this->settings->passwords->length);
			$password->addRule(Form::FILLED, 'Password must be filled')
				->addRule(Form::MIN_LENGTH, 'Password must be at least %d characters long.', $this->settings->passwords->length)
				->setOption('description', $helpText);
		}

		if ($this->user->isNew()) {
			$role = $form->addMultiSelect('roles', 'Roles', $this->getAllRoles())
				->setRequired('Select any role');
			if ($this->disabledRoles) {
				$role->setDisabled();
				$role->setOmitted(FALSE);
			}

			$form->setDefaults($this->getDefaults());
			$defaultRole = $this->roleFacade->findByName(Entity\Role::CANDIDATE);
			$firstRole = $this->roleFacade->findByName(current($this->getAllRoles()));
			if ($defaultRole && array_key_exists($defaultRole->id, $this->getAllRoles())) {
				$role->setDefaultValue($defaultRole->id);
				if ($role->isDisabled()) {
					$role->setValue($defaultRole->id);
				}
			} else if ($firstRole && array_key_exists($firstRole->id, $this->getAllRoles())) {
				$role->setDefaultValue($firstRole->id);
				if ($role->isDisabled()) {
					$role->setValue($firstRole->id);
				}
			}
		}

		if (!$this->company) {
			$compayRepo = $this->em->getRepository(Entity\Company::getClassName());
			$companies = $compayRepo->findPairs('name');
			$form->addMultiSelect('companyAdmin', 'Admin for companies', $companies);
		}

		if (!$this->user->isNew()) {
			$form->setDefaults($this->getDefaults());
		}
		$form->addSubmit('save', 'Save');

		$form->onSuccess[] = $this->formSucceeded;
		return $form;
	}

	public function validateMail(IControl $control, $arg = NULL)
	{
		return $this->userFacade->isUnique($control->getValue());
	}

	public function formSucceeded(Form $form, $values)
	{
		$this->load($values);
		try {
			$this->save();
			$this->onAfterSave($this->user);
		} catch (DuplicateEntryException $exc) {
			$message = $this->translator->translate('\'%mail%\' is already registred', ['mail' => $values->mail]);
			$form['mail']->addError($message);
		}
	}

	private function load(ArrayHash $values)
	{
		if (isset($values->mail)) {
			$this->user->mail = $values->mail;
		}
		if ($values->password !== NULL && $values->password !== "") {
			$this->user->setPassword($values->password);
		}

		if (isset($values->roles)) {
			$this->user->clearRoles();
			foreach ($values->roles as $id) {
				$roleDao = $this->em->getDao(Entity\Role::getClassName());
				$item = $roleDao->find($id);
				if ($item) {
					$this->user->addRole($item);
				}
			}
		}

		if (isset($values->companyAdmin)) {
			$this->loadedCompanies[Entity\CompanyRole::ADMIN] = $values->companyAdmin;
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

		$companies = [];
		if ($this->company) {
			$companies[$this->company->id] = $this->company;
		} else if(isset($this->loadedCompanies[Entity\CompanyRole::ADMIN])) {
			foreach ($this->loadedCompanies[Entity\CompanyRole::ADMIN] as $companyId) {
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
				$this->companyFacade->createPermission($company, $this->user);
			}
		}

		return $this;
	}

	protected function getDefaults()
	{
		$adminRole = $this->companyFacade->findRoleByName(Entity\CompanyRole::ADMIN);
		$values = [
			'mail' => $this->user->mail,
			'roles' => $this->user->getRolesKeys(),
			'companyAdmin' => $adminRole ? array_keys($this->user->getCompanies($adminRole)) : [],
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

	public function setDisabledRoles(array $roles, $disableChange = FALSE)
	{
		$this->disabledRoles = $roles;
		$this->disableChangeRoles = $disableChange;
		return $this;
	}

	private function getAllRoles()
	{
		if ($this->roles === NULL) {
			$this->roles = $this->userFacade->findLowerRoles($this->identity->roles, TRUE);
			$this->roles = array_diff($this->roles, $this->disabledRoles);
		}
		return $this->roles;
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
